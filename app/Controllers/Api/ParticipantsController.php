<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Config\UploadConfig;

class ParticipantsController extends BaseController
{
    protected $participantsModel;
    protected $bracketsModel;
    protected $tournamentsModel;
    protected $tournamentMembersModel;
    protected $votesModel;
    protected $groupsModel;
    protected $groupMembersModel;

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        $this->participantsModel = model('\App\Models\ParticipantModel');
        $this->bracketsModel = model('\App\Models\BracketModel');
        $this->tournamentsModel = model('\App\Models\TournamentModel');
        $this->tournamentMembersModel = model('\App\Models\TournamentMembersModel');
        $this->votesModel = model('\App\Models\VotesModel');
        $this->groupsModel = model('\App\Models\GroupsModel');
        $this->groupMembersModel = model('\App\Models\GroupMembersModel');
    }

    public function getParticipants() {
        // Check if it's an AJAX request
        if ($this->request->isAJAX()) {
            $participant_name = $this->request->getPost('participant'); // Get the posted data
            
            $participants = $this->getParticipantsList($participant_name);
            
            if ($participants) {
                $participants = array_values($participants);
                
                $keys = array_column($participants, 'tournaments_won');

                array_multisort($keys, SORT_DESC, $participants);
            }
            
            return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON($participants);
        }

        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                              ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }

    public function getAnalysis()
    {
        // Check if it's an AJAX request
        if ($this->request->isAJAX()) {
            $participants = $this->getParticipantsList();
            $type = 'tournaments_won';

            if ($this->request->getPost('type') == 'bracket') {
                $type = 'brackets_won';
            }

            if ($this->request->getPost('type') == 'score') {
                $type = 'accumulated_score';
            }

            if ($this->request->getPost('type') == 'votes') {
                $type = 'votes';
            }

            $keys = array_column($participants, $type);
            array_multisort($keys, SORT_DESC, $participants);
            
            $list = [];
            if ($participants) {
                $i = 0;
                foreach ($participants as $participant) {
                    if ($i > 4) {
                        break;
                    }

                    $list[] = $participant;
                    $i++;
                }
            }

            $tournaments_count = $this->tournamentsModel->where('status', TOURNAMENT_STATUS_COMPLETED)->countAllResults();
            
            return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['participants' => $list, 'tournaments_count' => $tournaments_count]);
        }

        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                              ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }

    private function getParticipantsList($participant_name = null)
    {
        $userSettingService = service('userSettings');

        if ($participant_name) {
            $participants = $this->participantsModel->like('name', $participant_name)->findAll();
        } else {
            $participants = $this->participantsModel->findAll();
        }
        
        if ($participants) {
            $newList = [];
            $registered_users = [];
            $groups = [];
            foreach ($participants as $participant) {
                $brackets = $this->bracketsModel->where(['winner' => $participant['id']])->findAll();
                $participant['brackets_won'] = ($brackets) ? count($brackets) : 0;

                $finalBrackets = $this->bracketsModel->where(['winner' => $participant['id'], 'final_match' => 1])->findAll();
                $participant['tournaments_won'] = ($finalBrackets) ? count($finalBrackets) : 0;

                $participant['won_tournaments'] = [];
                $won_tournament_names = [];
                if ($finalBrackets) {
                    foreach ($finalBrackets as $f_bracket) {
                        $won_tournament = $this->tournamentsModel->find($f_bracket['tournament_id']);
                        $participant['won_tournaments'][] = $won_tournament;
                        $won_tournament_names[] = strtolower($won_tournament['name']);
                    }
                }

                if ($this->request->getPost('won_tournament')) {
                    if (empty(array_filter($won_tournament_names, fn($str) => stripos($str, $this->request->getPost('won_tournament')) !== false))) {
                        continue;
                    }
                }

                $scores = $this->calculateScores($participant['id'], $brackets);
                $participant['top_score'] = $scores['top_score'];
                $participant['accumulated_score'] = round($scores['total_score'], 2);

                $votes = $this->votesModel->where('participant_id', $participant['id'])->findAll();
                $participant['votes'] = ($votes) ? count($votes) : 0;
                
                $participant['email'] = null;
                if (($participant['name'][0] == '@') && ($registered_user_id = $participant['registered_user_id'])) {
                    if (!$userSettingService->get('hide_email_participant', $participant['registered_user_id'])) {
                        $registered_user = auth()->getProvider()->findById($registered_user_id);
                        $participant['email'] = $registered_user ? $registered_user->email : null;
                    }
                }

                $tournamentIds = $this->tournamentMembersModel->where('participant_id', $participant['id'])->groupBy('tournament_id')->findColumn('tournament_id');
                if ($tournamentIds) {
                    if ($this->request->getPost('tournament')) {
                        $participant['tournaments_list'] = $this->tournamentsModel->whereIn('id', $tournamentIds)->like('name', $this->request->getPost('tournament'))->select(['id', 'name'])->findAll();
                    } else {
                        $participant['tournaments_list'] = $this->tournamentsModel->whereIn('id', $tournamentIds)->select(['id', 'name'])->findAll();
                    }
                } else {
                    $participant['tournaments_list'] = '';
                }

                if ($participant['is_group']) {
                    if (isset($groups[$participant['group_id']])) {
                        $groups[$participant['group_id']]['brackets_won'] += $participant['brackets_won'];
                        $groups[$participant['group_id']]['tournaments_won'] += $participant['tournaments_won'];
                        $groups[$participant['group_id']]['accumulated_score'] += round($participant['accumulated_score'], 2);
                        $groups[$participant['group_id']]['votes'] += $participant['votes'];
                        
                        if (count($participant['won_tournaments'])) {
                            $groups[$participant['group_id']]['won_tournaments'] = array_merge($groups[$participant['group_id']]['won_tournaments'], $participant['won_tournaments']);
                        }
                    } else {
                        $groups[$participant['group_id']] = $participant;
                    }

                    $groups[$participant['group_id']]['tournaments_list'] = $participant['tournaments_list'];
                }

                $newList[$participant['id']] = $participant;
            }

            // Plus the score of the group to the member partiicpant
            if (isset($groups) && $groups) {
                foreach ($groups as $group_id => $group) {
                    $group['members'] = '';
                    // Fetch the group members and plus the score and counts
                    $members = $this->groupMembersModel->where('group_members.group_id', $group_id)->groupBy('participants.id')->details()->findAll();
                    if ($members) {
                        foreach ($members as $index => $member) {
                            if (!$member['id']) {
                                continue;
                            }
                            
                            if (isset($newList[$member['id']])) {
                                $newList[$member['id']]['brackets_won'] += $group['brackets_won'];
                                $newList[$member['id']]['tournaments_won'] += $group['tournaments_won'];
                                $newList[$member['id']]['top_score'] += $group['top_score'];
                                $newList[$member['id']]['accumulated_score'] += $group['accumulated_score'];
                                $newList[$member['id']]['votes'] += $group['votes'];
                                // Merge participated tournaments list and Remove duplicate sub-arrays
                                if ($group['won_tournaments']) {
                                    $newList[$member['id']]['won_tournaments'] = array_merge($newList[$member['id']]['won_tournaments'], $group['won_tournaments']);
                                    $newList[$member['id']]['won_tournaments'] = array_map("unserialize", array_unique(array_map("serialize", $newList[$member['id']]['won_tournaments'])));
                                }
                                if ($group['tournaments_list']) {
                                    $newList[$member['id']]['tournaments_list'] = array_merge($newList[$member['id']]['tournaments_list'], $group['tournaments_list']);
                                    $newList[$member['id']]['tournaments_list'] = array_map("unserialize", array_unique(array_map("serialize", $newList[$member['id']]['tournaments_list'])));
                                }
                            }
                            
                            // make the member's name list for tooltip
                            $group['members'] .= $member['name'];
                            if ($index < count($members) - 1) {
                                $group['members'] .= ',';
                            }
                        }

                        $newList[$group['id']]['members'] = $group['members'];
                    }
                }
            }

            $participants = $newList;
        }

        return $participants;
    }

    public function addParticipant($names = null)
    {
        helper('db');
        helper('participant');

        if (!$names) {
            $names = $this->request->getPost('name');
        }

        $tournament_id = $this->request->getPost('tournament_id') ? $this->request->getPost('tournament_id') : 0;
        $hash = $this->request->getPost('hash');
        $user_id = auth()->user()? auth()->user()->id : 0;
        
        if (!$user_id || !$tournament_id) {
            disableForeignKeyCheck();
        }

        $inserted_count = 0;
        $notAllowedList = [];        
        if ($names) {
            $userProvider = auth()->getProvider();
            foreach ($names as $name) {
                if ($name) {
                    $participant = null;
                    
                    // Check if the participant is the registered user and already added as the participant
                    $registered_user_id = null;
                    if ($name[0] == '@') {
                        $trimName = trim($name, '@');
                        $user = $userProvider->where('username', $trimName)->first();
                        if ($user) {
                            $registered_user_id = $user->id;

                            $participant = $this->participantsModel->asObject()->where('registered_user_id', $user->id)->first();
                            // Check if the participant allows the invitation
                            if ($participant) {
                                if (!$available = checkAvailabilityAddToTournament($participant->registered_user_id)) {
                                    $notAllowedList[] = $participant->name;
                                    continue;
                                }
                            }
                        }
                    }
                    
                    // Add new participant if not existing
                    if (!$participant || !$participant->id) {
                        $participant = new \App\Entities\Participant([
                            'name' => $name,
                            'created_by' => $user_id,
                            'active' => 1,
                            'hash' => $hash
                        ]);

                        if ($registered_user_id) {
                            $participant->registered_user_id = $registered_user_id;
                        }

                        $this->participantsModel->insert($participant);
                        $participant->id = $this->participantsModel->getInsertID();
                    }

                    $this->tournamentMembersModel->insert([
                        'participant_id' => $participant->id,
                        'tournament_id' => $tournament_id,
                        'hash' => $hash,
                        'created_by' => $user_id
                    ]);
                    
                    $inserted_count++;
                }
            }
        }
        
        if (!$user_id) {
            enableForeignKeyCheck();
        }

        helper('participant_helper');            
        if ($tournament_id) {
            $list = getParticipantsAndReusedGroupsInTournament($tournament_id);
        } else {
            $list = getParticipantsAndReusedGroupsInTournament($tournament_id, $this->request->getPost('hash'));
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                ->setJSON(['result' => 'success', "participants"=> $list['participants'], 'notAllowedParticipants' => $notAllowedList, "reusedGroups"=> $list['reusedGroups'], 'count' => $inserted_count]);
    }

    public function updateParticipant($id)
    {
        $name = $this->request->getPost('name');
        $hash = $this->request->getPost('hash');
        $participant = $this->participantsModel->find($id);

        helper('participant');

        if (!$participant) {
            return $this->response->setJSON(['result' => 'error', "message"=> "Participant ID is invalid."]);
        }

        if ($name) {
            if ($tournament_id = $this->request->getPost('tournament_id')) {
                $meamber = $this->tournamentMembersModel->where(['tournament_id' => $tournament_id, 'participant_id' => $id])->find();
            } else {
                $meamber = $this->tournamentMembersModel->where(['tournament_id' => 0, 'participant_id' => $id, 'hash' => $hash])->find();
            }

            if (!$meamber) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                            ->setJSON(['result' => 'error', "message"=> "This participant didn't registered as a tournament member."]);
            }

            if ($name[0] == '@') {
                $name = trim($name, '@');
                $user = auth()->getProvider()->where('username', $name)->first();

                if (!$user) {
                    return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                            ->setJSON(['result' => 'error', "message"=> "The username @$name doesn't exist."]);
                }

                if (!checkAvailabilityAddToTournament($user->id)) {
                    return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                            ->setJSON(['result' => 'failed', "message"=> "This user \"@$user->username\" declined invitations to tournaments"]);
                }

                $member['participant_id'] = $user->id;
                $this->tournamentMembersModel->save($member);
            } else {
                $participant['name'] = $name;
                $participant['registered_user_id'] = null;                
                $this->participantsModel->update($id, $participant);
            }
        } else {
            $uploadConfig = new UploadConfig();
            
            $file = $this->request->getFile('image');
            if($file){
                $filepath = '';
                if (! $file->hasMoved()) {
                    $filepath = '/uploads/' . $file->store($uploadConfig->participantImagesUploadPath);
                    $participant['image'] = $filepath;
                }
            }
            
            if($this->request->getPost('action') == 'removeImage'){
                $participant['image'] = '';
            }

            $this->participantsModel->update($id, $participant);
        }
        
        helper('participant_helper');
        $list = getParticipantsAndReusedGroupsInTournament($this->request->getPost('tournament_id'), $this->request->getPost('hash'));
        
        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                            ->setJSON(['result' => 'success', "participants"=> $list['participants'], "reusedGroups"=> $list['reusedGroups']]);
    }

    public function deleteParticipant($id)
    {
        $tournament_id = $this->request->getPost('tournament_id');
        $hash = $this->request->getPost('hash');

        if ($tournament_id) {
            $this->tournamentMembersModel->where(['tournament_id' => $tournament_id, 'participant_id' => $id])->delete();
            $this->votesModel->where(['tournament_id'=> $tournament_id, 'participant_id' => $id])->delete();
        } else {
            $this->tournamentMembersModel->where(['hash' => $hash, 'participant_id' => $id])->delete();
        }

        if (!$this->tournamentMembersModel->where('participant_id', $id)->findAll()) {
            $this->participantsModel->where('id', $id)->delete();
            $this->votesModel->where(['participant_id' => $id])->delete();
        }

        helper('participant_helper');            
        if ($tournament_id) {
            $list = getParticipantsAndReusedGroupsInTournament($tournament_id);
        } else {
            $list = getParticipantsAndReusedGroupsInTournament($tournament_id, $this->request->getPost('hash'));
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                ->setJSON(['status' => 'success', "participants"=> $list['participants'],"reusedGroups"=> $list['reusedGroups']]);
    }
    
    public function deleteParticipants()
    {
        $user_id = auth()->user() ? auth()->user()->id : 0;
        $tournament_id = $this->request->getPost('tournament_id') ?? 0;
        $hash = $this->request->getPost('hash');
        
        if ($participant_ids = $this->request->getPost('p_ids')) {
            $this->tournamentMembersModel->whereIn('participant_id', $participant_ids);
            if ($tournament_id) {
                $this->tournamentMembersModel->where('tournament_id', $tournament_id)->delete();
            } else {
                $this->tournamentMembersModel->where(['tournament_id' => 0, 'hash' => $hash])->delete();
            }

            foreach ($participant_ids as $id) {
                if (!$pt = $this->tournamentMembersModel->where('participant_id', $id)->findAll()) {
                    $this->participantsModel->whereIn('id', $participant_ids)->delete();
                }
            }
        } else {
            return json_encode(array('result' => 'failed', 'msg' => 'There is not participant selected'));
        }

        helper('participant_helper');            
        if ($tournament_id) {
            $list = getParticipantsAndReusedGroupsInTournament($tournament_id);
        } else {
            $list = getParticipantsAndReusedGroupsInTournament($tournament_id, $this->request->getPost('hash'));
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                ->setJSON(['result' => 'success', "participants"=> $list['participants'], 'notAllowedParticipants' => $list['notAllowed'], "reusedGroups"=> $list['reusedGroups']]);
    }
    
    public function clearParticipants()
    {
        if ($tournament_id = $this->request->getGet('t_id')) {
            if ($tournament_id) {
                $members = $this->tournamentMembersModel->where('tournament_id', $tournament_id)->findAll();
            } else {
                $members = $this->tournamentMembersModel->where(['tournament_id' => 0, 'hash' => $this->request->getPost('hash')])->findAll();
            }

            // Delete group members and group if it's not associated to other tournaments
            $member_ids = array_map(function($item) {
                    return (int)$item['id'];
                }, $members);
            $groupMembers = $this->groupMembersModel->whereIn('tournament_member_id', $member_ids)->groupBy('group_id')->findAll();
            $this->groupMembersModel->whereIn('tournament_member_id', $member_ids)->delete();
            if ($groupMembers) {
                foreach ($groupMembers as $member) {
                    if ($this->groupMembersModel->where('group_id', $member['group_id'])->groupBy('tournament_id')->findAll()) {
                        continue;
                    } else {
                        $this->groupsModel->delete($member['group_id']);
                    }
                }
            }
            
            $this->tournamentMembersModel->whereIn('id', $member_ids)->delete();

            // Delete the participants if it'not associated to other tournaments
            $participant_ids = array_map(function($item) {
                    return (int)$item['participant_id'];
                }, $members);
            if ($participant_ids) {
                foreach ($participant_ids as $pt_id) {
                    if ($this->tournamentMembersModel->where('participant_id', $pt_id)->groupBy('tournament_id')->findAll()) {
                        continue;
                    } else {
                        $this->participantsModel->delete($pt_id);
                    }
                }
            }

        }
        
        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                ->setJSON(['result' => 'success']);
    }
    
    public function importParticipants()
    {
        $validationRule = [
            'file' => [
                'label' => 'CSV File',
                'rules' => [
                    'uploaded[file]',
                    'ext_in[file,csv]',
                ],
                'errors' => [
                    'uploaded' => 'Please upload a file.',
                    'ext_in' => 'The uploaded file must be a valid CSV.',
                ],
            ],
        ];
        
        if (!$this->validateData([], $validationRule)) {
            $data = ['errors' => $this->validator->getErrors()];
            
            return $this->response->setJSON($data);
        }

        $uploadConfig = new UploadConfig();

		$file = $this->request->getFile('file');
        $filepath = '';
        if (! $file->hasMoved()) {
            $filepath = WRITEPATH . 'uploads/' . $file->store($uploadConfig->csvUploadPath);
        }
        
        if (!file_exists($filepath)) {
            return $this->response->setJSON(['errors' => "Imported file was not saved correctly"]);
        }

		$arr_file 		= explode('.', $filepath);
		$extension 		= end($arr_file);
		if('csv' == $extension) {
			$reader 	= new \PhpOffice\PhpSpreadsheet\Reader\Csv();
		} else {
			$reader 	= new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
		}
		$spreadsheet 	= $reader->load($filepath);
		$sheet_data 	= $spreadsheet->getActiveSheet()->toArray();
        
		$data 			= [];
		foreach($sheet_data as $key => $val) {
			if($key != 0) {
                $data[] = $val[0];
			}
		}
        
        return $this->response->setJSON(['result' => 'success', 'names' => $data]);
    }

    public function calculateScores($participant_id, $brackets) {
        $totalScore = 0;
        $topScore = 0;
        $tournamentSettings = [];
        $scores_by_tournaments = [];

        if ($brackets) {
            foreach ($brackets as $bracket) {
                $bracket_score = 0;
                $increment_score = 0;
                $increment_score_type = 0;

                if (!isset($tournamentSettings[$bracket['tournament_id']])) {
                    $tournamentSettings[$bracket['tournament_id']] = $this->tournamentsModel->find($bracket['tournament_id']);
                }

                if ($tournamentSettings[$bracket['tournament_id']]['type'] == TOURNAMENT_TYPE_KNOCKOUT) {
                    if ($bracket['knockout_final']) {
                        continue;
                    }
                } else {
                    if ($bracket['final_match']) {
                        continue;
                    }
                }
                
                $bracket_score = ($tournamentSettings[$bracket['tournament_id']]['score_enabled']) ? $tournamentSettings[$bracket['tournament_id']]['score_bracket'] : 0;
                $increment_score = ($tournamentSettings[$bracket['tournament_id']]['increment_score_enabled']) ? $tournamentSettings[$bracket['tournament_id']]['increment_score'] : 0;
                $increment_score_type = $tournamentSettings[$bracket['tournament_id']]['increment_score_type'];

                if (!isset($scores_by_tournaments[$bracket['tournament_id']])) {
                    $scores_by_tournaments[$bracket['tournament_id']] = 0;
                }

                if ($increment_score_type == TOURNAMENT_SCORE_INCREMENT_PLUS) {
                    $scores_by_tournaments[$bracket['tournament_id']] += $bracket_score + $increment_score * ($bracket['roundNo'] - 1);
                }

                if ($increment_score_type == TOURNAMENT_SCORE_INCREMENT_MULTIPLY) {
                    if ($bracket['roundNo'] == 1) {
                        $scores_by_tournaments[$bracket['tournament_id']] = $bracket_score;
                    } else {
                        $scores_by_tournaments[$bracket['tournament_id']] += $scores_by_tournaments[$bracket['tournament_id']] * $increment_score;
                    }
                }
            }
        }

        $totalScore = array_sum($scores_by_tournaments);
        $topScore = ($scores_by_tournaments) ? max($scores_by_tournaments) : 0;

        return ['total_score' => $totalScore, 'top_score' => $topScore];
    }
    
    public function export(){
        $participants = $this->getParticipantsList();
        
        $filename = 'participants' . date('Ymd') . '.csv';

        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        $output = fopen('php://output', 'w');

        // Add the CSV column headers
        fputcsv($output, ['ID', 'Participant Name', 'Participant Type', 'Group Members', 'Brackets Won', 'Tournaments Won', 'Won Tournaments', 'Participated Tournaments', 'Accumulated Score', 'Votes']);

        // Fetch the data and write it to the CSV
        if ($participants) {
            foreach ($participants as $participant) {
                $participantType = $participant['is_group'] ? "Group" : "Individual Participant";
                $groupMembers = ($participant['is_group'] && isset($participant['members']) && $participant['members']) ? $participant['members'] : "N/A";
                $won_list = '';
                if ($participant['won_tournaments']) {
                    foreach ($participant['won_tournaments'] as $i => $won_t) {
                        $won_list .= $won_t['name'];

                        if ($i < count($participant['won_tournaments']) - 1) {
                            $won_list .= ', ';
                        }
                    }
                }

                $tournament_list = '';
                if ($participant['tournaments_list']) {
                    foreach ($participant['tournaments_list'] as $i => $tm) {
                        $tournament_list .= $tm['name'];

                        if ($i < count($participant['tournaments_list']) - 1) {
                            $tournament_list .= ', ';
                        }
                    }
                }

                fputcsv($output, [
                    $participant['id'],
                    $participant['name'],
                    $participantType,
                    $groupMembers,
                    $participant['brackets_won'],
                    $participant['tournaments_won'],
                    $won_list,
                    $tournament_list,
                    $participant['accumulated_score'],
                    $participant['votes']
                ]);
            }
        }

        fclose($output);
        exit;
    }
}