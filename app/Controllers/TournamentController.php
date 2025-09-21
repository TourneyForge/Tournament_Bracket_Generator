<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class TournamentController extends BaseController
{
    public function index()
    {
        $navActive = ($this->request->getGet('filter')) ? $this->request->getGet('filter') :'all';
        $searchString = $this->request->getGet('query');

        $table = view('tournament/list', ['navActive' => $navActive, 'searchString' => $searchString, 'shareType' => $this->request->getGet('type')]);

        $settingsBlock = view('tournament/tournament-settings', []);
        $audioSettingsBlock = view('tournament/audio-setting', []);

        $userModel = model('CodeIgniter\Shield\Models\UserModel');
        $users = $userModel->select(['id', 'username'])->findAll();

        if ($navActive == 'glr') {
            return view('gallery', ['searchString' => $searchString]);
        }

        return view('tournament/dashboard', ['table' => $table, 'audioSettingsBlock' => $audioSettingsBlock, 'settingsBlock' => $settingsBlock, 'users' => $users, 'navActive' => $navActive]);
    }

    public function create()
    {
        $userSettingModel = model('\App\Models\UserSettingModel');
        
        // Convert settings to key-value array
        $settingsArray = [];
        if( auth()->user() ){
            $userSettings = $userSettingModel->where('user_id', auth()->user()->id)->findAll();
            if (count($userSettings)) {
                foreach ($userSettings as $setting) {
                    $settingsArray[$setting['setting_name']] = $setting['setting_value'];
                }
            }
        }

        $users = auth()->getProvider()->limit(5)->findAll();
        /** Check if the registered user allows the invitations */
        $userSettingService = service('userSettings');

        $filteredUsers = [];
        if ($users) {
            foreach ($users as $user) {
                if ($userSettingService->get('disable_invitations', $user->id)) {
                    continue;
                }

                $filteredUsers[] = $user;
            }
        }

        $settingsBlock = view('tournament/tournament-settings', []);
        $audioSettingsBlock = view('tournament/audio-setting', []);

        return view('tournament/create', ['audioSettingsBlock' => $audioSettingsBlock, 'settingsBlock' => $settingsBlock, 'userSettings' => $settingsArray, 'users' => $filteredUsers]);
    }

    public function view($id)
    {
        $tournamentModel = model('\App\Models\TournamentModel');
        $bracketModel = model('\App\Models\BracketModel');
        $audioSettingModel = model('\App\Models\AudioSettingModel');
        $userSettingModel = model('\App\Models\UserSettingModel');
        $shareSettingsModel = model('\App\Models\ShareSettingsModel');

        $session = \Config\Services::session();
        
        $userSettingService = service('userSettings');
        
        $tournament = $tournamentModel->find($id);

        if (!$tournament) {
            $session->setFlashdata(['error' => "This Tournament doesn't exist!"]);
            return view('/errors/html/error_404', ['message' => "This Tournament doesn't exist!"]);
        }

        $user_id = auth()->user() ? auth()->user()->id : 0;

        $created_by = auth()->getProvider()->findById($tournament['user_id']);

        $tournament['created_by'] = $created_by;
        
        if (!$tournament) {
            $session = \Config\Services::session();
            $session->setFlashdata(['error' => "This tournament doesn't exist!"]);

            return redirect()->to('/tournaments');
        }

        /** Fetch the user list for the actions "Change Participant/Add Participant */
        $users = auth()->getProvider()->limit(5)->findAll();
        //Filter the registered users allow the invitations
        $filteredUsers = [];
        if ($users) {
            foreach ($users as $user) {
                if ($userSettingService->get('disable_invitations', $user->id)) {
                    continue;
                }

                $filteredUsers[] = $user;
            }

            $users = $filteredUsers;
        }

        $hosted_by_this = true;
        if($tournament['user_id'] == 0){
            $hosted_by_this = false;
            $existingHistory = $this->request->getCookie('guest_tournaments');
            $tournamentHistory = $existingHistory ? json_decode($existingHistory, true) : [];
            $shareSetting = $shareSettingsModel->where(['tournament_id' => $id, 'user_id' => 0])->first();

            if ($shareSetting) {
                $cookie_value = $id . "_" . $shareSetting['token'];
            } else {
                $cookie_value = $id . "_" . 'guest';
            }

            if (in_array($cookie_value, $tournamentHistory)) {
                $hosted_by_this = true;
            }
        }

        if (!auth()->user() && !$tournament['visibility'] && !$hosted_by_this) {
            return view('bracket-invisible', ['tournament' => $tournament, 'created_by' => $created_by]);
        }

        /** Check if the tournament is associated with guest user */
        $editable = false;
        $votingEnabled = false;
        $votingBtnEnabled = false;
        if (auth()->user() && $tournament['user_id'] == auth()->user()->id) {
            $editable = true;
            $votingBtnEnabled = true;
        }
        
        if($tournament['user_id'] == 0 && $hosted_by_this){
            $editable = true;
            $votingBtnEnabled = true;
        }

        /** 
         * Check if vote is available 
         */
        if ($tournament['evaluation_method'] == EVALUATION_METHOD_VOTING) {
            $votingEnabled = true;
            if ($tournament['voting_accessibility'] == EVALUATION_VOTING_RESTRICTED) {
                if (auth()->user()) {
                    $votingBtnEnabled = true;
                } else {
                    $shareSettings = $shareSettingsModel->where(['tournament_id' => $id])->findAll();
                    foreach ($shareSettings as $share) {
                        if ($share['target'] == SHARE_TO_PUBLIC) {
                            $votingBtnEnabled = true;
                        }
                    }
                }
            }

            if ($tournament['voting_accessibility'] == EVALUATION_VOTING_UNRESTRICTED) {
                $votingBtnEnabled = true;
            }
        }

        /** Check if the availability start */
        $current_date = date('Y-m-d H:i:s');
        if ($tournament['availability']) {
            if (date('Y-m-d H:i:s', strtotime($tournament['available_start'])) > $current_date || date('Y-m-d H:i:s', strtotime($tournament['available_end'])) < $current_date) {
                $votingBtnEnabled = false;
            }
        }

        if ($tournament['status'] == TOURNAMENT_STATUS_COMPLETED) {
            $votingBtnEnabled = false;
        }

        $brackets = $bracketModel->where('tournament_id', $id)->findAll();
        
        if (!$brackets) {
            if (auth()->user() && $tournament['user_id'] != auth()->user()->id) {
                $session = \Config\Services::session();
                $session->setFlashdata(['error' => "The brackets was not generated yet."]);

                return redirect()->to('/tournaments');
            }

            $settingsBlock = view('tournament/tournament-settings', []);
            $audioSettingsBlock = view('tournament/audio-setting', []);
            $audioSettings = $audioSettingModel->where(['tournament_id' => $id])->whereIn('type', [AUDIO_TYPE_BRACKET_GENERATION, AUDIO_TYPE_BRACKET_GENERATION_VIDEO])->orderBy('type','asc')->findAll();
            if ($audioSettings) {
                $audios = [];
                foreach ($audioSettings as $audio) {
                    $audios[$audio['type']] = $audio;
                }

                $tournament['audio'] = $audios;
            }
            
            $userSettings = $userSettingModel->where('user_id', $user_id)->findAll();

            // Convert settings to key-value array
            $settingsArray = [];
            if (count($userSettings)) {
                foreach ($userSettings as $setting) {
                    $settingsArray[$setting['setting_name']] = $setting['setting_value'];
                }
            }

            return view('tournament/create', ['tournament' => $tournament, 'users' => $users, 'settingsBlock' => $settingsBlock, 'audioSettingsBlock' => $audioSettingsBlock, 'userSettings' => $settingsArray]);
        }

        $audioSettings = $audioSettingModel->where(['tournament_id' => $id, 'type' => AUDIO_TYPE_FINAL_WINNER])->orderBy('type','asc')->findAll();
        $tournament['win_audio_enabled'] = 0;
        if ($audioSettings) {
            foreach ($audioSettings as $aSetting) {
                if ($aSetting['type'] == AUDIO_TYPE_FINAL_WINNER) {
                    $tournament['win_audio_enabled'] = 1;
                }
            }
        }

        $tournament['vote_displaying'] = $userSettingService->get('vote_displaying_mode', $user_id);
        if (!$tournament['vote_displaying']) {
            $tournament['vote_displaying'] = 'n';
        }
        
        return view('brackets', ['brackets' => $brackets, 'tournament' => $tournament, 'users' => $users, 'audioSettings' => $audioSettings, 'editable' => $editable, 'votingEnabled' => $votingEnabled, 'votingBtnEnabled' => $votingBtnEnabled, 'page' => 'view']);
    }
    
    public function viewShared($token)
    {
        $shareSettingModel = model('\App\Models\ShareSettingsModel');
        $tournamentModel = model('\App\Models\TournamentModel');
        $bracketModel = model('\App\Models\BracketModel');
        $audioSettingModel = model('\App\Models\AudioSettingModel');

        $session = \Config\Services::session();

        $userSettingService = service('userSettings');

        $user_id = auth()->user() ? auth()->user()->id : 0;

        $settings = $shareSettingModel->where(['token'=> $token])->first();
        if (!$settings) {
            return view('bracket-invisible');
        }else{
            if($settings['user_id'] == 0 && (time() - strtotime($settings['created_at'])) > 24*60*60){
                $session = \Config\Services::session();
                $session->setFlashdata(['error' => "This link has been expired!"]);
                $shareSettingModel->where(['token'=> $token])->delete();
                $tournamentModel->where(['tournament_id' => $settings['tournament_id']])->delete();

                return redirect()->to('/gallery');
            }
        }

        $tournament = $tournamentModel->find($settings['tournament_id']);
        if (!$tournament) {
            $session->setFlashdata(['error' => "This Tournament doesn't exist!"]);
            return view('/errors/html/error_404', ['message' => "This Tournament doesn't exist!"]);
        }

        $created_by = auth()->getProvider()->findById($tournament['user_id']);

        $tournament['created_by'] = $created_by;
        
        if (!auth()->user() && !$tournament['visibility']) {
            return view('bracket-invisible', ['tournament' => $tournament, 'created_by' => $created_by]);
        }
        
        $brackets = $bracketModel->where('tournament_id', $settings['tournament_id'])->findAll();

        $shareAccessModel = model('\App\Models\TournamentShareAccessLogModel');
        if (auth()->user()) {
            $shareAccessModel->insert(['share_id' => $settings['id'], 'user_id' => auth()->user()->id]);
        } else {
            //$shareAccessModel->insert(['share_id' => $settings['id'], 'user_id' => 0]);
        }
        
        /** Check if the user has the editable permission */
        $editable = false;
        if ($settings['permission'] == SHARE_PERMISSION_EDIT) {
            if ($settings['target'] == SHARE_TO_PUBLIC || $settings['target'] == SHARE_TO_EVERYONE) {
                $editable = true;
            }

            if (isset($settings['users']) && auth()->user() && in_array(auth()->user()->id, explode(",", $settings['users']))) {
                $editable = true;
            }
        }
        
        /** 
         * Check if vote is available 
         */
        $votingEnabled = false;
        $votingBtnEnabled = false;
        if ($tournament['evaluation_method'] == EVALUATION_METHOD_VOTING) {
            $votingEnabled = true;
            if ($tournament['voting_accessibility'] == EVALUATION_VOTING_RESTRICTED) {
                if (auth()->user()) {
                    $votingBtnEnabled = true;
                } else {
                    if ($settings['target'] == SHARE_TO_PUBLIC) {
                        $votingBtnEnabled = true;
                    }
                }
            }

            if ($tournament['voting_accessibility'] == EVALUATION_VOTING_UNRESTRICTED) {
                $votingBtnEnabled = true;
            }

            
            if ($tournament['status'] == TOURNAMENT_STATUS_COMPLETED) {
                $votingBtnEnabled = false;
            }

        }

        if (!$brackets) {
            $session = \Config\Services::session();
            $session->setFlashdata(['error' => "The brackets was not generated yet."]);

            return redirect()->to('/tournaments');
        }

        $audioSettings = $audioSettingModel->where(['tournament_id' => $settings['tournament_id'], 'type' => AUDIO_TYPE_FINAL_WINNER])->orderBy('type','asc')->findAll();
        $tournament['win_audio_enabled'] = 0;
        if ($audioSettings) {
            foreach ($audioSettings as $aSetting) {
                if ($aSetting['type'] == AUDIO_TYPE_FINAL_WINNER) {
                    $tournament['win_audio_enabled'] = 1;
                }
            }
        }

        $tournament['vote_displaying'] = $userSettingService->get('vote_displaying_mode', $user_id);
        if (!$tournament['vote_displaying']) {
            $tournament['vote_displaying'] = 'n';
        }
        
        /** Fetch the user list for the actions "Change Participant/Add Participant */
        $users = auth()->getProvider()->limit(5)->findAll();
        //Filter the registered users allow the invitations
        $filteredUsers = [];
        if ($users) {
            foreach ($users as $user) {
                if ($userSettingService->get('disable_invitations', $user->id)) {
                    continue;
                }

                $filteredUsers[] = $user;
            }

            $users = $filteredUsers;
        }

        return view('brackets', ['brackets' => $brackets, 'tournament' => $tournament, 'users' => $users, 'settings' => $settings, 'audioSettings' => $audioSettings, 'votingEnabled' => $votingEnabled, 'votingBtnEnabled' => $votingBtnEnabled, 'editable' => $editable, 'page' => 'view']);
    }

    public function export()
    {
        $tournamentModel = model('\App\Models\TournamentModel');
        $tournamentMembers = model('\App\Models\TournamentMembersModel');
        $shareSettingsModel = model('App\Models\ShareSettingsModel');
        $userModel = model('CodeIgniter\Shield\Models\UserModel');
        
        if ($this->request->getGet('filter') == 'shared') {
            $tournaments = $shareSettingsModel->tournamentDetails();
            
            if ($this->request->getGet('query')) {
                $searchString = $this->request->getGet('query');
                $tournaments->like(['tournaments.searchable' => $searchString]);
            }
            
            if ($this->request->getGet('type') == 'wh') {
                $tournaments->groupStart();
                $tournaments->whereIn('share_settings.target', [SHARE_TO_EVERYONE, SHARE_TO_PUBLIC]);
                $tournaments->orLike('share_settings.users', strval(auth()->user()->id));
                $tournaments->groupEnd();
                $tempRows = $tournaments->findAll();
                
                $tournaments = [];
                $access_tokens = [];
                if ($tempRows) {
                    foreach ($tempRows as $tempRow) {
                        $user_ids = $tempRow['users'] ? explode(',', $tempRow['users']) : null;
                        
                        $add_in_list = false;
                        if ($tempRow['target'] == SHARE_TO_USERS && in_array(auth()->user()->id, $user_ids)) {
                            $add_in_list = true;
                        }

                        if (($tempRow['target'] == SHARE_TO_EVERYONE || $tempRow['target'] == SHARE_TO_PUBLIC) && $tempRow['access_time']) {
                            $add_in_list = true;
                        }

                        /** Omit the record from Shared with me if the share was created by himself */
                        if ($tempRow['deleted_at']) {
                            $add_in_list = false;
                        }

                        if ($add_in_list && !in_array($tempRow['token'], $access_tokens)) {
                            $tempRow['access_time'] = $tempRow['access_time'] ? convert_to_user_timezone($tempRow['access_time'], user_timezone(auth()->user()->id)) : null;
                            $tournaments[$tempRow['tournament_id']] = $tempRow;
                            $access_tokens[] = $tempRow['token'];
                        }
                    }
                }
            } else {
                $tempRows = $tournaments->where('share_settings.user_id', auth()->user()->id)->findAll();

                $tournaments = [];
                if ($tempRows) {
                    foreach ($tempRows as $tempRow) {
                        $tournaments[$tempRow['tournament_id']] = $tempRow;
                    }
                }
            }
        } else {
            $tournaments = $tournamentModel->where(['user_id' => auth()->user()->id]);

            if ($this->request->getGet('filter') == 'archived') {
                $tournaments->where(['archive' => 1]);
            } else {
                $tournaments->where('archive', 0);
            }

            if ($this->request->getGet('query')) {
                $searchString = $this->request->getGet('query');
                $tournaments->like(['searchable' => $searchString]);
            }
            
            $tournaments = $tournaments->findAll();
        }

        $filename = 'tournaments_' . date('Ymd') . '.csv';

        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        $output = fopen('php://output', 'w');

        // Add the CSV column headers
        if ($this->request->getGet('filter') == 'shared') {
            if ($this->request->getGet('type') == 'wh') {
                fputcsv($output, ['ID', 'Name', 'Type', 'Evaluation Method', 'Status', 'Accessbility', 'Shared By', 'Shared Time', 'URL']);
            } else {
                fputcsv($output, ['ID', 'Name', 'Type', 'Evaluation Method', 'Status', 'Created Time', 'URL']);
            }
        } else {
            fputcsv($output, ['ID', 'Name', 'Type', 'Evaluation Method', 'Status', 'Participants', 'Availability Start', 'Availability End', 'Public URL', 'Created By', 'Created Time', 'URL']);
        }

        // Fetch the data and write it to the CSV
        foreach ($tournaments as $tournament) {
            $statusLabel = TOURNAMENT_STATUS_LABELS[$tournament['status']];
            $type = $tournament['type'] == 1 ? 'Single' : 'Double';

            $tournamentId = ($tournament['tournament_id']) ?? $tournament['id'];
            $tournament['evaluation_method'] = ($tournament['evaluation_method'] == EVALUATION_METHOD_MANUAL) ? "Manual" : "Voting";

            if ($this->request->getGet('filter') == 'shared') {
                $createdTime = convert_to_user_timezone($tournament['access_time'], user_timezone(auth()->user()->id));
                
                if ($this->request->getGet('type') == 'wh') {
                    if ($tournament['permission'] == SHARE_PERMISSION_EDIT) {
                        $tournament['permission'] = 'Can Edit';
                    }

                    if ($tournament['permission'] == SHARE_PERMISSION_VIEW) {
                        $tournament['permission'] = 'Can View';
                    }

                    $url = base_url('tournaments/shared/' . $tournament['token']);
                    fputcsv($output, [
                        $tournamentId,
                        $tournament['name'],
                        $type,
                        $tournament['evaluation_method'],
                        $statusLabel,
                        $tournament['permission'],
                        $tournament['username'],
                        $createdTime,
                        $url
                    ]);
                } else {
                    fputcsv($output, [
                        $tournamentId,
                        $tournament['name'],
                        $type,
                        $tournament['evaluation_method'],
                        $statusLabel,
                        $createdTime,
                        base_url('tournaments/' . $tournamentId . '/view')
                    ]);
                }
            } else {
                $participants = count($tournamentMembers->where('tournament_members.tournament_id', $tournament['id'])->participantInfo()->findAll());
                $availability_start = $tournament['available_start'];
                $availability_end = $tournament['available_end'];

                $sharedTournament = $shareSettingsModel->where(['tournament_id' => $tournament['id'], 'target' => SHARE_TO_PUBLIC])->orderBy('created_at', 'DESC')->first();
                $public_url = ($sharedTournament) ? base_url('/tournaments/shared/') . $sharedTournament['token'] : '';
                $createdTime = convert_to_user_timezone($tournament['created_at'], user_timezone(auth()->user()->id));
                
                $user = $userModel->find($tournament['user_id']);
                $username = ($user) ? $user->username : 'Guest';

                fputcsv($output, [
                    $tournamentId,
                    $tournament['name'],
                    $type,
                    $tournament['evaluation_method'],
                    $statusLabel,
                    $participants,
                    $availability_start,
                    $availability_end,
                    $public_url,
                    $username,
                    $createdTime,
                    base_url('tournaments/' . $tournamentId . '/view')
                ]);
            }
        }

        fclose($output);
        exit;
    }

    public function exportGallery(){
        $tournamentModel = model('\App\Models\TournamentModel');
        $participantModel = model('\App\Models\ParticipantModel');
        $userModel = model('CodeIgniter\Shield\Models\UserModel');
        $shareSettingsModel = model('App\Models\ShareSettingsModel');

        $tournaments = $tournamentModel->where(['visibility' => 1]);
        $tournaments = $tournaments->findAll();
        $filename = 'tournaments_' . date('Ymd') . '.csv';

        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        $output = fopen('php://output', 'w');

        // Add the CSV column headers
        fputcsv($output, ['ID', 'Name', 'Type', 'Evaluation Method', 'Status', 'Participants', 'Availability Start', 'Availability End', 'Public URL', 'Created By', 'Created Time', 'URL']);

        // Fetch the data and write it to the CSV
        foreach ($tournaments as $tournament) {
            $statusLabel = TOURNAMENT_STATUS_LABELS[$tournament['status']];
            $type = $tournament['type'] == 1 ? 'Single' : 'Double';
            $evaluation_method = $tournament['evaluation_method'] == EVALUATION_METHOD_MANUAL ? "Manual" : "Voting";
            $participants = count($participantModel->where('tournament_id', $tournament['id'])->findAll());
            $availability_start = $tournament['available_start'];
            $availability_end = $tournament['available_end'];

            $sharedTournament = $shareSettingsModel->where(['tournament_id' => $tournament['id'], 'target' => SHARE_TO_PUBLIC])->orderBy('created_at', 'DESC')->first();
            $public_url = ($sharedTournament) ? base_url('/tournaments/shared/') . $sharedTournament['token'] : '';
            
            $tournamentId = ($tournament['tournament_id']) ?? $tournament['id'];

            $user = $userModel->find($tournament['user_id']);
            $username = ($user) ? $user->username : 'Guest';

            $createdTime = $tournament['created_at'];
            fputcsv($output, [
                $tournamentId,
                $tournament['name'],
                $type,
                $evaluation_method,
                $statusLabel,
                $participants,
                $availability_start,
                $availability_end,
                $public_url,
                $username,
                $createdTime,
                base_url('gallery/' . $tournamentId . '/view')
            ]);
        }

        fclose($output);
        exit;
    }

    public function apply()
    {
        return view('tournament/apply');
    }

    public function saveApply()
    {
        // Validate the input
        $validation = \Config\Services::validation();
        
        $validation->setRules([
            'name' => 'required',
            'participation_mode' => 'required',
            'agree' => 'required'
        ]);

        if (!$this->validate($validation->getRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Process the registration (e.g., save to database)

        return redirect()->to('/tournaments/apply')->with('message', 'Registration successful');
    }
}