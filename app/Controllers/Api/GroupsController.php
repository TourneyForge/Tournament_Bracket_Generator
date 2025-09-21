<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Entities\Group;
use App\Entities\GroupeMember;
use App\Entities\GroupMember;
use App\Entities\Participant;
use App\Entities\TournamentMember;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class GroupsController extends BaseController
{
    protected $participantsModel;
    protected $tournamentMembersModel;
    protected $groupsModel;
    protected $groupMembersModel;
    
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);
        $this->participantsModel = model('\App\Models\ParticipantModel');
        $this->tournamentMembersModel = model('\App\Models\TournamentMembersModel');
        $this->groupsModel = model('\App\Models\GroupsModel');
        $this->groupMembersModel = model('\App\Models\GroupMembersModel');
    }

    public function getList()
    {
        // Check if it's an AJAX request
        if ($this->request->isAJAX()) {
            $groups = $this->groupsModel->findAll();

            return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['status' => 'success', 'groups' => $groups]);
        }

        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                              ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }

    public function save()
    {
        helper('db_helper');

        $user_id = auth()->user() ? auth()->user()->id :0;
        $tournament_id = $this->request->getPost('tournament_id') ?? 0;
        $hash = $this->request->getPost('hash');
        $previousMembers = [];
        $participants = $this->request->getPost('participants');

        // Check if it's an AJAX request
        if ($this->request->isAJAX()) {
            if (!$user_id || !$tournament_id) {
                disableForeignKeyCheck();
            }

            if (($group_id = $this->request->getPost('group_id')) && ($group_name = $this->request->getPost('group_name'))) {
                if ($group_name) {
                    $groupEntity = $this->groupsModel->asObject()->find($group_id);
                    $groupEntity->group_name = $group_name;
                    $groupEntity->image_path = $this->request->getPost('image_path');
                    $groupEntity->user_id = $user_id;

                    $this->groupsModel->save($groupEntity);
                }
            } elseif ($group_id) {
                $groupEntity = $this->groupsModel->asObject()->find($group_id);
                $participantsInGroups = $this->groupMembersModel->where(['group_members.tournament_id' => $tournament_id, 'group_members.group_id' => $group_id])->details()->groupBy('participant_id')->findAll();
                if ($participantsInGroups) {
                    log_message('debug', json_encode($participantsInGroups));
                    foreach ($participantsInGroups as $participant) {
                        // Check if the participants in the group are existing in this tournament
                        $isExisting = false;
                        if ($t_members = $this->tournamentMembersModel->where(['tournament_id' => $tournament_id, 'participant_id' => $participant['id']])->findAll()) {
                            log_message('debug', 't_members:' . json_encode($t_members));
                            if ($tournament_id) {
                                $isExisting = true;
                                continue;
                            }
                            log_message('debug', 't_id: ' . $tournament_id);
                            foreach ($t_members as $member) {
                                if ($member['hash'] == $hash) {
                                    $isExisting = true;
                                    continue;
                                }
                            }
                        }

                        if ($isExisting) {
                            continue;
                        }

                        // Add a previous participant as the member of the tournament
                        if ($participant['id'] && !in_array($participant['id'], $participants)) {
                            $memberEntity = new TournamentMember([
                                'participant_id' => $participant['id'],
                                'tournament_id' => $tournament_id,
                                'created_by' => $user_id,
                                'hash' => $hash
                            ]);

                            $previousMembers[] = $this->tournamentMembersModel->insert($memberEntity);
                        }
                    }
                }
            } elseif ($group_name = $this->request->getPost('group_name')) {
                $groupEntity = new Group();
                $groupEntity->group_name = $group_name;
                $groupEntity->image_path = $this->request->getPost('image_path');
                $groupEntity->user_id = $user_id;

                $group_id = $this->groupsModel->insert($groupEntity);
            }

            // Add a group as the participant
            if ($group_id) {
                $participant = $this->participantsModel->where('group_id', $group_id)->first();
                if (!$participant) {
                    $participantEntity = new Participant([
                        'name'  => $groupEntity->group_name,
                        'image' => $groupEntity->image_path,
                        'hash'  => $hash,
                        'is_group' => true,
                        'group_id' => $group_id,
                        'active' => 1,
                        'created_by'  => $user_id
                    ]);
                    $participant_id = $this->participantsModel->insert($participantEntity);
                } else {
                    $participant_id = $participant['id'];
                }

                if ($tournament_id) {
                    $member = $this->tournamentMembersModel->where(['tournament_id' => $tournament_id, 'participant_id' => $participant_id])->first();
                } else {
                    $member = $this->tournamentMembersModel->where(['tournament_id' => 0, 'hash' => $hash, 'participant_id' => $participant_id])->first();
                }
                if (!$member) {
                    $memberEntity = new TournamentMember([
                        'participant_id' => $participant_id,
                        'tournament_id' => $tournament_id,
                        'created_by' => $user_id,
                        'hash' => $hash
                    ]);
                    $this->tournamentMembersModel->insert($memberEntity);
                }
            }
            
            if ($group_id && $participants) {
                $participantsList = $previousMembers;
                foreach ($participants as $participant) {
                    $teamMember = ($tournament_id) ? $this->tournamentMembersModel->where(['tournament_id' => $tournament_id, 'participant_id' => $participant])->first() : $this->tournamentMembersModel->where(['tournament_id' => 0, 'participant_id' => $participant, 'hash' => $hash])->first();
                    if ($teamMember) {
                        $participantsList[] = $teamMember['id'];
                    }
                }

                if ($participantsList && is_array($participantsList)) {
                    foreach ($participantsList as $participant) {
                        $entity = new GroupMember();
                        $entity->tournament_id = $tournament_id;
                        $entity->tournament_member_id = $participant;
                        $entity->group_id = $group_id;

                        $this->groupMembersModel->save($entity);
                    }
                }
            }
            
            if (!$user_id || !$tournament_id) {
                enableForeignKeyCheck();
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

        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                              ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }

    public function reset()
    {
        $user_id = auth()->user() ? auth()->user()->id :0;
        $tournament_id = $this->request->getPost('tournament_id') ?? 0;
        $hash = $this->request->getPost('hash');

        if ($this->request->isAJAX()) {
            if ($list = $this->request->getPost('participants')) {
                if ($tournament_id) {
                    $members = $this->tournamentMembersModel->where('tournament_id', $tournament_id)->whereIn('participant_id', $list)->select('id')->findAll();
                } else {
                    $members = $this->tournamentMembersModel->where(['tournament_id' => 0, 'hash' => $hash])->whereIn('participant_id', $list)->select('id')->findAll();
                }

                if (isset($members) && $members) {
                    $members = array_map(function($item) {
                            return (int)$item['id'];
                        }, $members);

                    $this->groupMembersModel->where(['group_id'=> $this->request->getPost('group_id')])->whereIn('tournament_member_id', $members)->delete();
                } else {
                    return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['status' => 'error', 'message' => 'These participants are existing.']);
                }
            } else {
                return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['status' => 'error', 'message' => 'There is not the participants to remove.']);
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

        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                              ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }
    
    public function delete()
    {
        $user_id = auth()->user() ? auth()->user()->id :0;
        $tournament_id = $this->request->getPost('tournament_id') ?? 0;
        $hash = $this->request->getPost('hash');

        if ($this->request->isAJAX()) {
            if ($this->request->getPost('group_id')) {
                if (count($this->groupMembersModel->where('group_id', $this->request->getPost('group_id'))->groupBy('tournament_id')->findAll()) > 1) {
                    return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['status' => 'error', 'message' => 'This group is associated to other tournaments.']);
                }

                if ($tournament_id) {
                    $this->groupMembersModel->where(['group_id'=> $this->request->getPost('group_id'), 'tournament_id' => $tournament_id])->delete();
                } else {
                    $members = $this->tournamentMembersModel->where(['tournament_id' => 0, 'hash' => $hash])->findAll();
                    $members = array_map(function($item) {
                            return (int)$item['id'];
                        }, $members);
                    $this->groupMembersModel->where(['group_id'=> $this->request->getPost('group_id'), 'tournament_id' => 0])->whereIn('tournament_member_id', $members)->delete();
                }

                $this->groupsModel->where(['id'=> $this->request->getPost('group_id')])->delete();
            } else {
                return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['status' => 'error', 'message' => 'The group was not specified.']);
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

        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                              ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }
    
    public function removeParticipant()
    {
        $user_id = auth()->user() ? auth()->user()->id :0;
        $tournament_id = $this->request->getPost('tournament_id') ?? 0;
        $hash = $this->request->getPost('hash');
        
        if ($this->request->isAJAX()) {
            if ($participant_id = $this->request->getPost('participant_id')) {
                if ($tournament_id) {
                    $member = $this->tournamentMembersModel->where(['tournament_id' => $tournament_id, 'participant_id' => $participant_id])->first();
                } else {
                    $member = $this->tournamentMembersModel->where(['tournament_id' => $tournament_id, 'participant_id' => $participant_id, 'hash' => $hash])->first();
                }

                if ($member) {
                    $this->groupMembersModel->where(['group_id' => $this->request->getPost('group_id'), 'tournament_member_id' => $member['id']])->delete();
                }
            } else {
                return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                    ->setJSON(['status' => 'error', 'message' => 'Failed to remove the participant.']);
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

        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                              ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }
}