<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Files\File;
use YoutubeDl\YoutubeDl;
use YoutubeDl\Options;
use App\Services\NotificationService;
use App\Libraries\VoteLibrary;
use App\Libraries\TournamentLibrary;
use Config\UploadConfig;

class TournamentController extends BaseController
{
    protected $notificationService;
    protected $tournamentModel;
    protected $tournamentMembersModel;
    protected $participantModel;
    protected $bracketModel;
    protected $shareSettingModel;
    protected $groupsModel;
    protected $groupMembersModel;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
        $this->tournamentModel = model('\App\Models\TournamentModel');
        $this->tournamentMembersModel = model('\App\Models\TournamentMembersModel');
        $this->participantModel = model('\App\Models\ParticipantModel');
        $this->bracketModel = model('\App\Models\BracketModel');
        $this->shareSettingModel = model('\App\Models\ShareSettingsModel');
        $this->groupsModel = model('\App\Models\GroupsModel');
        $this->groupMembersModel = model('\App\Models\GroupMembersModel');
    }

    public function index()
    {
        //
    }

    public function fetch()
    {
        $tournaments = $this->tournamentModel;

        $searchable = $this->request->getPost('search_tournament');
        $type = $this->request->getPost('type');
        $evaluation_method = $this->request->getPost('evaluation_method');
        $status = $this->request->getPost('status');
        $created_by = $this->request->getPost('created_by');
        $accessibility = $this->request->getPost('accessibility');

        $userProvider = auth()->getProvider();
        $userSettingService = service('userSettings');

        /** Filter the tournaments by my tournament, archived, shared, gallery */
        if ($this->request->getGet('filter') == 'shared') {
            $tournaments = $this->shareSettingModel->tournamentDetails();

            if ($searchable) {
                $tournaments->like(['tournaments.searchable' => $searchable]);
            }

            if ($type) {
                $tournaments->like(['tournaments.type' => $type]);
            }

            if ($evaluation_method) {
                $tournaments->where('evaluation_method', $evaluation_method);
            }

            if ($status) {
                $tournaments->like(['tournaments.status' => $status]);
            }

            if ($created_by || $created_by == 0) {
                $tournaments->like(['tournaments.user_id' => $created_by]);
            }

            if ($this->request->getGet('type') == 'wh') {
                $tournaments->groupStart()
                    ->whereIn('share_settings.target', [SHARE_TO_EVERYONE, SHARE_TO_PUBLIC])
                    ->where('tournament_share_access_logs.created_at Is Not null')
                    ->where('tournament_share_access_logs.created_at', auth()->user()->id)
                    ->groupEnd()
                    ->orLike('share_settings.users', strval(auth()->user()->id));
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
            // Get the user_id parameter from the request
            $userBy = $this->request->getPost('user_id');

            // Apply the filter if the user_id parameter is provided
            if ($userBy) {
                $tournaments->where('user_id', $userBy);
            } else {
                $tournaments->where(['visibility' => 1]);
            }

            if ($type) {
                $tournaments->where('type', $type);
            }

            if ($evaluation_method) {
                $tournaments->where('evaluation_method', $evaluation_method);
            }

            if ($status) {
                $tournaments->where('status', $status);
            }

            if ($this->request->getGet('filter') == 'archived') {
                $tournaments->where(['archive' => 1]);
            } else {
                $tournaments->where('archive', 0);
            }

            // Apply the filter if the searchable parameter is provided
            if ($searchable) {
                $tournaments->like('searchable', $searchable);
            }

            // Fetch the tournaments
            $tournaments = $tournaments->findAll();
        }

        // Fetch participants and public URL for each tournament
        $result_tournaments = [];
        foreach ($tournaments as &$tournament) {
            $tournament_id = (isset($tournament['tournament_id'])) ? $tournament['tournament_id'] : $tournament['id'];
            $shareSetting = $this->shareSettingModel->where(['tournament_id' => $tournament_id, 'target' => SHARE_TO_PUBLIC])->orderBy('created_at', 'DESC')->first();
            $tournament['public_url'] = '';
            if ($shareSetting) {
                $tournament['public_url'] = base_url('/tournaments/shared/') . $shareSetting['token'];
            }

            $tournament['created_at'] = (auth()->user()) ? convert_to_user_timezone($tournament['created_at'], user_timezone(auth()->user()->id)) : $tournament['created_at'];

            if ($userSettingService->get('hide_email_host', $tournament['user_id'])) {
                $tournament['email'] = null;
            }

            $tournament['participants_count'] = 0;
            if ($participants = $this->tournamentMembersModel->where('tournament_id', $tournament_id)->findAll()) {
                $tournament['participants_count'] = count($participants);
            }

            $result_tournaments[] = $tournament;
        }

        // Return the tournaments as a JSON response
        return $this->response->setJSON($result_tournaments);
    }

    public function fetch_gallery()
    {
        $type = $this->request->getPost('type');
        $evaluation_method = $this->request->getPost('evaluation_method');
        $status = $this->request->getPost('status');
        $created_by = $this->request->getPost('created_by');

        $userProvider = auth()->getProvider();
        $userSettingService = service('userSettings');

        $tournaments = $this->tournamentModel->where(['visibility' => 1]);
        $searchString = '';
        if ($searchString = $this->request->getPost('search_tournament')) {
            $tournaments->like(['tournaments.searchable' => $searchString]);
        }

        if ($type) {
            $tournaments->where('type', $type);
        }

        if ($evaluation_method) {
            $tournaments->where('evaluation_method', $evaluation_method);
        }

        if ($status) {
            $tournaments->where('status', $status);
        }

        if ($created_by || $created_by == 0) {
            $tournaments->where('user_id', $created_by);
        }

        if ($this->request->getPost('is_reuse') && auth()->user()) {
            if (!$created_by) {
                $tournaments->orWhere('user_id', auth()->user()->id);
            }

            $sharedWithMe = $this->shareSettingModel->tournamentDetails()
                ->groupStart()
                ->whereIn('share_settings.target', [SHARE_TO_EVERYONE, SHARE_TO_PUBLIC])
                ->where('tournament_share_access_logs.created_at Is Not null')
                ->where('tournament_share_access_logs.created_at', auth()->user()->id)
                ->groupEnd()
                ->orLike('share_settings.users', strval(auth()->user()->id))
                ->findAll();

            $shared_ids = [];
            if ($sharedWithMe) {
                foreach ($sharedWithMe as $tempRow) {
                    $user_ids = $tempRow['users'] ? explode(',', $tempRow['users']) : null;

                    if ($tempRow['target'] == SHARE_TO_USERS && in_array(auth()->user()->id, $user_ids)) {
                        $shared_ids[] = $tempRow['tournament_id'];
                    }

                    if (($tempRow['target'] == SHARE_TO_EVERYONE || $tempRow['target'] == SHARE_TO_PUBLIC) && $tempRow['access_time']) {
                        $shared_ids[] = $tempRow['tournament_id'];
                    }
                }
            }

            if ($shared_ids) {
                $tournaments->orWhereIn('id', $shared_ids);
            }
        }

        $tournaments = $tournaments->orderBy('available_start', 'DESC')->orderBy('created_at', 'DESC')->findAll();

        $newTournaments = array();
        $existingHistory = $this->request->getCookie('guest_tournaments');
        $tournamentHistory = $existingHistory ? json_decode($existingHistory, true) : [];

        foreach ($tournaments as $tournament) {
            /* Check if participants added */
            if (!$this->tournamentMembersModel->where('tournament_id', $tournament['id'])->findAll()) {
                continue;
            }

            $temp = $tournament;

            $temp['username'] = 'Guest User';
            if ($tournament['user_id'] > 0) {
                $user = $userProvider->findById($tournament['user_id']);
                $temp['username'] = $user->username;

                if (!$userSettingService->get('hide_email_host', $user->id)) {
                    $temp['email'] = $user->email;
                }
            }

            $sharedTournament = $this->shareSettingModel->where(['tournament_id' => $tournament['id'], 'target' => SHARE_TO_PUBLIC])->orderBy('created_at', 'DESC')->first();
            $temp['public_url'] = ($sharedTournament) ? base_url('/tournaments/shared/') . $sharedTournament['token'] : '';

            $participants = $this->tournamentMembersModel->where('tournament_members.tournament_id', $tournament['id'])->participantInfo()->findAll();
            $temp['participants_count'] = 0;
            if ($participants) {
                $temp['participants_count'] = count($participants);
            }
            $newTournaments[] = $temp;
        }

        return $this->response->setJSON($newTournaments);
    }

    public function save()
    {
        helper('db_helper');

        $tournamentModel = model('\App\Models\TournamentModel');
        $user_id = (auth()->user()) ? auth()->user()->id : 0;

        /** Disable foreign key check for the guest users */
        if (!$user_id) {
            disableForeignKeyCheck();
        }

        $existing = $tournamentModel->where(['name' => $this->request->getPost('title'), 'user_id' => $user_id])->findAll();

        if ($existing && !$this->request->getPost('confirm_duplicate_save')) {
            $data = ['errors' => "duplicated", 'message' => "Chosen tournament name already exists. Do you want to proceed saving anyways?"];

            return $this->response->setJSON($data);
        }

        $data = [
            'name' => $this->request->getPost('title'),
            'user_id' => $user_id,
            'type' => $this->request->getPost('type'),
            'searchable' => $this->request->getPost('title'),
            'archive' => 0,
            'shuffle_enabled' => ($this->request->getPost('shuffle_enabled') == 'on') ? 1 : 0,
            'description' => $this->request->getPost('description'),
            'score_enabled' => ($this->request->getPost('score_enabled') == 'on') ? 1 : 0,
            'score_bracket' => $this->request->getPost('score_bracket'),
            'increment_score' => $this->request->getPost('increment_score'),
            'increment_score_enabled' => ($this->request->getPost('increment_score_enabled') == 'on') ? 1 : 0,
            'increment_score_type' => $this->request->getPost('increment_score_type'),
            'visibility' => ($this->request->getPost('visibility') == 'on') ? 1 : 0,
            'availability' => ($this->request->getPost('availability') && $this->request->getPost('availability') == 'on') ? 1 : 0,
            'evaluation_method' => $this->request->getPost('evaluation_method'),
            'voting_accessibility' => $this->request->getPost('voting_accessibility'),
            'voting_mechanism' => $this->request->getPost('voting_mechanism'),
            'max_vote_value' => $this->request->getPost('max_vote_value'),
            'round_duration_combine' => ($this->request->getPost('round_duration_combine') == 'on') ? 1 : 0,
            'voting_retain' => ($this->request->getPost('voting_retain') == 'on') ? 1 : 0,
            'vote_displaying' => $this->request->getPost('vote_displaying'),
            'allow_host_override' => ($this->request->getPost('allow_host_override') == 'on') ? 1 : 0,
            'pt_image_update_enabled' => ($this->request->getPost('pt_image_update_enabled') == 'on') ? 1 : 0,
            'theme' => $this->request->getPost('theme'),
            'winner_audio_everyone' => ($this->request->getPost('winner_audio_everyone') == 'on') ? 1 : null
        ];

        if ($this->request->getPost('availability')) {
            $data['available_start'] = date('Y-m-d H:i:s', strtotime($this->request->getPost('startAvPicker')));
            $data['available_end'] = date('Y-m-d H:i:s', strtotime($this->request->getPost('endAvPicker')));

            if ($data['available_start'] > date('Y-m-d H:i:s')) {
                $data['status'] = TOURNAMENT_STATUS_NOTSTARTED;
            }
        }

        $tournamentData = new \App\Entities\Tournament($data);

        $tournament_id = $tournamentModel->insert($tournamentData);
        // End saving the tournament settings

        if (!$tournament_id) {
            $data = ['errors' => "tournament_saving", 'message' => "Failed to save the tournament."];

            return $this->response->setJSON($data);
        }

        $tournamentData->id = $tournament_id;

        if ($this->request->getPost('setting-toggle')) {
            $uploadConfig = new UploadConfig();
            $audioSettingsModel = model('\App\Models\AudioSettingModel');

            foreach ($this->request->getPost('audioType') as $index => $value) {
                if (isset($this->request->getPost('setting-toggle')[$index]) && $this->request->getPost('setting-toggle')[$index] == 'on') {
                    if ($index == 2) {
                        $path = ($this->request->getPost('source')[$index] == 'f') ? $this->request->getPost('file-path')[$index] : $uploadConfig->urlVideoUploadPath . $this->process($this->request->getPost('url')[$index], 'video');
                    } else {
                        $path = ($this->request->getPost('source')[$index] == 'f') ? $this->request->getPost('file-path')[$index] : $uploadConfig->urlAudioUploadPath . $this->process($this->request->getPost('url')[$index]);
                    }
                    $url = ($this->request->getPost('source')[$index] == 'f') ? null : $this->request->getPost('url')[$index];

                    $setting = [
                        'path' => $path,
                        'source' => $this->request->getPost('source')[$index],
                        'tournament_id' => $tournament_id,
                        'user_id' => $user_id,
                        'type' => $index,
                        'duration' => $this->request->getPost('duration')[$index],
                        'start' => $this->request->getPost('start')[$index],
                        'end' => $this->request->getPost('stop')[$index],
                        'url' => $url
                    ];

                    $audio_setting = $audioSettingsModel->insert($setting);

                    if (!$audio_setting) {
                        $data = ['errors' => "audio_saving", 'message' => "Failed to save the audio settings."];

                        return $this->response->setJSON($data);
                    }

                    $data['audio'][$index] = $setting;
                }
            }
        }

        /**
         * Add the tournament created by guest users to share table
         */
        if ($tournamentData->visibility) {
            $shareSetting = $this->shareSettingModel->where(['tournament_id' => $tournament_id, 'user_id' => $user_id])->first();
            if (!$shareSetting) {
                $config = new \Config\Encryption();
                $token = hash_hmac('sha256', 'tournament_' . $tournament_id . "_created_by_" . $user_id . "_" . time(), $config->key);
                $token = substr($token, 0, 5);
                $shareData = array(
                    'user_id' => $user_id,
                    'tournament_id' => $tournament_id,
                    'target' => 'p',
                    'permission' => SHARE_PERMISSION_VIEW,
                    'token' => $token
                );
                $this->shareSettingModel->insert($shareData);
            }
        }

        /** Add the tournament Id into the cookie for guest users */
        if (!$user_id) {
            $existingHistory = $this->request->getCookie('guest_tournaments');
            $tournamentHistory = $existingHistory ? json_decode($existingHistory, true) : [];

            $shareSetting = $this->shareSettingModel->where(['tournament_id' => $tournament_id, 'user_id' => 0])->first();

            // Add the new tournament to the history
            if ($shareSetting) {
                $tournamentHistory[] = $tournament_id . "_" . $shareSetting['token'];
            } else {
                $tournamentHistory[] = $tournament_id . "_" . 'guest';
            }

            // Store updated history in cookies (expire in 1 days)
            $this->response->setCookie('guest_tournaments', json_encode($tournamentHistory), 24 * 60 * 60);
        }
        /** End adding the tournament Id into the cookie for guest users */

        /** Enable foreign key check */
        if (!$user_id) {
            enableForeignKeyCheck();
        }

        $data['id'] = $tournament_id;

        return $this->response->setJSON(['msg' => "Tournament settings successfully saved.", 'tournament' => $data]);
    }

    public function getSettings($id)
    {
        $tournamentModel = model('\App\Models\TournamentModel');
        $tournament = $tournamentModel->find($id);

        $audioSettingModel = model('\App\Models\AudioSettingModel');

        $settings = $audioSettingModel->where(['tournament_id' => $id])->findAll();
        if ($settings) {
            $new_settings = [];
            foreach ($settings as $setting) {
                $new_settings[$setting['type']] = $setting;
            }

            $settings = $new_settings;
        }

        $settingsBlock = view('tournament/tournament-settings', []);
        $html = view('tournament/audio-setting', []);

        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON(['msg' => "Tournament was updated successfully.", 'audioSettings' => $settings, 'tournamentSettings' => $tournament, 'settingsBlock' => $settingsBlock, 'html' => $html]);
    }

    public function update($tournament_id)
    {
        $tournamentModel = model('\App\Models\TournamentModel');
        $tournament = $tournamentModel->find(intval($tournament_id));

        $scheduleLibrary = new \App\Libraries\ScheduleLibrary();

        if ($this->request->getPost('title')) {
            $tournament['name'] = $this->request->getPost('title');
        }
        if ($this->request->getPost('type')) {
            $tournament['type'] = $this->request->getPost('type');
        }
        if ($this->request->getPost('description')) {
            $tournament['description'] = $this->request->getPost('description');
        }
        if ($this->request->getPost('status')) {
            $tournament['status'] = $this->request->getPost('status');
        }

        if ($this->request->getPost('visibility')) {
            $tournament['visibility'] = ($this->request->getPost('visibility') == 'on') ? 1 : 0;

            if ($this->request->getPost('visibility') == 'on') {
                $shareSetting = $this->shareSettingModel->where(['tournament_id' => $tournament_id, 'user_id' => $tournament['user_id']])->first();
                if (!$shareSetting) {
                    $config = new \Config\Encryption();
                    $token = hash_hmac('sha256', 'tournament_' . $tournament_id . "_created_by_" . $tournament['user_id'] . "_" . time(), $config->key);
                    $shareData = array(
                        'user_id' => $tournament['user_id'],
                        'tournament_id' => $tournament_id,
                        'target' => SHARE_TO_PUBLIC,
                        'permission' => SHARE_PERMISSION_VIEW,
                        'token' => $token
                    );
                    $this->shareSettingModel->insert($shareData);
                }
            } else {
                $this->shareSettingModel->where(['tournament_id' => $tournament_id, 'user_id' => $tournament['user_id'], 'target' => SHARE_TO_PUBLIC])->delete();
            }
        }

        if ($this->request->getPost('shuffle_enabled')) {
            $tournament['shuffle_enabled'] = ($this->request->getPost('shuffle_enabled') == 'on') ? 1 : 0;
        }

        if ($this->request->getPost('score_enabled')) {
            $tournament['score_enabled'] = ($this->request->getPost('score_enabled') == 'on') ? 1 : 0;
        }

        if ($this->request->getPost('score_bracket')) {
            $tournament['score_bracket'] = $this->request->getPost('score_bracket');
        }

        if ($this->request->getPost('increment_score_enabled')) {
            $tournament['increment_score_enabled'] = ($this->request->getPost('increment_score_enabled') == 'on') ? 1 : 0;
        }

        if ($this->request->getPost('increment_score')) {
            $tournament['increment_score'] = $this->request->getPost('increment_score');
        }

        if ($this->request->getPost('increment_score_type')) {
            $tournament['increment_score_type'] = $this->request->getPost('increment_score_type');
        }

        if ($this->request->getPost('availability')) {
            $tournament['availability'] = ($this->request->getPost('availability') == 'on') ? 1 : 0;

            $availabilityChanged = false;

            if ($tournament['availability']) {
                if ($tournament['available_start'] != date('Y-m-d H:i:s', strtotime($this->request->getPost('startAvPicker')))) {
                    $availabilityChanged = true;
                }

                if ($tournament['available_end'] != date('Y-m-d H:i:s', strtotime($this->request->getPost('endAvPicker')))) {
                    $availabilityChanged = true;
                }

                $tournament['available_start'] = date('Y-m-d H:i:s', strtotime($this->request->getPost('startAvPicker')));
                $tournament['available_end'] = date('Y-m-d H:i:s', strtotime($this->request->getPost('endAvPicker')));

                $maxRound = $this->bracketModel->where('tournament_id', $tournament_id)->selectMax('roundNo')->first() ?? 1;
                $scheduleLibrary->registerSchedule($tournament_id, SCHEDULE_NAME_TOURNAMENTSTART, 1, $tournament['available_start']);
                $scheduleLibrary->registerSchedule($tournament_id, SCHEDULE_NAME_TOURNAMENTEND, $maxRound, $tournament['available_end']);
            } else {
                $tournament['available_start'] = null;
                $tournament['available_end'] = null;

                $scheduleLibrary->unregisterSchedule($tournament_id);
            }

            /** Send the notification to the registered user participants
             *  Notify the availability duration was updated
             */
            if ($availabilityChanged) {
                $registeredUsers = $this->tournamentMembersModel->where(['tournament_members.tournament_id' => $tournament['id']])->where('registered_user_id Is Not Null')->participantInfo()->findColumn('registered_user_id');

                if ($registeredUsers) {
                    $userProvider = auth()->getProvider();
                    $userSettingService = service('userSettings');

                    $creator = $userProvider->findById($tournament['user_id']);
                    $tournamentEntity = new \App\Entities\Tournament($tournament);
                    foreach ($registeredUsers as $user_id) {
                        $user = $userProvider->findById($user_id);

                        if (!$user) {
                            continue;
                        }

                        $message = "The availability of the tournament \"$tournamentEntity->name\" was updated!";
                        $this->notificationService->addNotification(['user_id' => auth()->user()->id, 'user_to' => $user->id, 'message' => $message, 'type' => NOTIFICATION_TYPE_FOR_AVAILABILITY_UPDATED, 'link' => "tournaments/$tournamentEntity->id/view"]);

                        if (!$userSettingService->get('email_notification', $user_id) || $userSettingService->get('email_notification', $user_id) == 'on') {
                            $email = service('email');
                            $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
                            $email->setTo($user->email);
                            $email->setSubject(lang('Emails.tournamentAvailabilityUpdateEmailSubject'));
                            $email->setMessage(view(
                                'email/tournament-availability-update',
                                ['username' => $user->username, 'tournament' => $tournamentEntity, 'creator' => $creator, 'startTime' => $tournament['available_start'], 'endTime' => $tournament['available_end'], 'tournamentCreatorName' => setting('Email.fromName')],
                                ['debug' => false]
                            ));

                            if ($email->send(false) === false) {
                                $data = ['errors' => "sending_emails", 'message' => "Failed to send the emails."];
                            }

                            $email->clear();
                        }
                    }
                }

                if ($tournament['available_start'] > date('Y-m-d H:i:s')) {
                    $tournament['status'] = TOURNAMENT_STATUS_NOTSTARTED;
                }

                if ($tournament['available_end'] > date('Y-m-d H:i:s')) {
                    $tournament['status'] = TOURNAMENT_STATUS_INPROGRESS;
                }
            }
        }

        if ($this->request->getPost('evaluation_method')) {
            $tournament['evaluation_method'] = $this->request->getPost('evaluation_method');
            if ($tournament['evaluation_method'] == EVALUATION_METHOD_VOTING) {
                $tournament['voting_accessibility'] = $this->request->getPost('voting_accessibility');
                $tournament['voting_mechanism'] = $this->request->getPost('voting_mechanism');
                if ($tournament['voting_mechanism'] == EVALUATION_VOTING_MECHANISM_MAXVOTE) {
                    $tournament['max_vote_value'] = $this->request->getPost('max_vote_value');
                } else {
                    $tournament['max_vote_value'] = null;
                }
            } else {
                $tournament['voting_accessibility'] = null;
                $tournament['voting_mechanism'] = null;
                $tournament['max_vote_value'] = null;
            }

            if ($this->request->getPost('voting_retain')) {
                $tournament['voting_retain'] = ($this->request->getPost('voting_retain') == 'on') ? 1 : 0;
            }
            if ($this->request->getPost('allow_host_override')) {
                $tournament['allow_host_override'] = ($this->request->getPost('allow_host_override') == 'on') ? 1 : 0;
            }
            if ($this->request->getPost('round_duration_combine')) {
                $tournament['round_duration_combine'] = ($this->request->getPost('round_duration_combine') == 'on') ? 1 : 0;
            }
            if ($this->request->getPost('vote_display')) {
                $tournament['vote_displaying'] = $this->request->getPost('vote_display');
            }
        }

        if ($this->request->getPost('pt_image_update_enabled')) {
            $tournament['pt_image_update_enabled'] = ($this->request->getPost('pt_image_update_enabled') == 'on') ? 1 : 0;
        }

        if ($this->request->getPost('theme')) {
            $tournament['theme'] = $this->request->getPost('theme');
        }

        if ($this->request->getPost('winner_audio_everyone') == 'on') {
            $tournament['winner_audio_everyone'] = 1;
        } else {
            $tournament['winner_audio_everyone'] = null;
        }

        if (!is_null($this->request->getPost('archive'))) {
            $tournament['archive'] = $this->request->getPost('archive');
        }

        $tournamentModel->save($tournament);

        /** Schedule to update the rounds by cron */
        if (isset($tournament['availability']) && $tournament['availability']) {
            if ($tournament['round_duration_combine'] || ($tournament['evaluation_method'] == EVALUATION_METHOD_VOTING && ($tournament['voting_mechanism'] == EVALUATION_VOTING_MECHANISM_ROUND || $tournament['voting_mechanism'] == EVALUATION_VOTING_MECHANISM_OPENEND))) {
                $scheduleLibrary->scheduleRoundUpdate($tournament_id);
            }
        }

        /**
         * Update Audio Settings
         */
        if ($this->request->getPost('audioType')) {
            $uploadConfig = new UploadConfig();

            $audioSettingModel = model('\App\Models\AudioSettingModel');
            foreach ($this->request->getPost('audioType') as $index => $value) {

                $audioSetting = $audioSettingModel->where(['tournament_id' => $tournament_id, 'type' => $value])->findAll();

                if (count($audioSetting)) {
                    $audioSetting = $audioSetting[0];
                } else {
                    $audioSetting = [];
                }

                if (isset($this->request->getPost('setting-toggle')[$index]) && $this->request->getPost('setting-toggle')[$index] == 'on') {
                    if ($index == 2) {
                        $path = ($this->request->getPost('source')[$index] == 'f') ? $this->request->getPost('file-path')[$index] : $uploadConfig->urlVideoUploadPath . $this->process($this->request->getPost('url')[$index], 'video');
                    } else {
                        $path = ($this->request->getPost('source')[$index] == 'f') ? $this->request->getPost('file-path')[$index] : $uploadConfig->urlAudioUploadPath . $this->process($this->request->getPost('url')[$index]);
                    }

                    $audioSetting['path'] = $path;
                    $audioSetting['source'] = $this->request->getPost('source')[$index];
                    $audioSetting['tournament_id'] = $tournament_id;
                    $audioSetting['user_id'] = auth()->user()->id;
                    $audioSetting['type'] = $index;
                    $audioSetting['duration'] = $this->request->getPost('duration')[$index];
                    $audioSetting['start'] = $this->request->getPost('start')[$index];
                    $audioSetting['end'] = $this->request->getPost('stop')[$index];
                    $audioSetting['url'] = ($this->request->getPost('source')[$index] == 'f') ? null : $this->request->getPost('url')[$index];

                    $audioSettingModel->save($audioSetting);
                } else {
                    if ($audioSetting) {
                        $audioSettingModel->delete($audioSetting['id']);
                    }
                }
            }
        }

        $tournamentName = $tournament['name'];
        $msg = "Tournament [$tournamentName] was updated successfully.";
        if (!is_null($this->request->getPost('archive'))) {
            if ($this->request->getPost('archive')) {
                $msg = "Tournament [$tournamentName] was archived successfully.";
            } else {
                $msg = "Tournament [$tournamentName] was restored successfully.";
            }
        }

        return json_encode(['msg' => $msg, 'data' => $this->request->getPost()]);
    }

    public function process($youtubeLink, $type = 'audio')
    {
        $uploadConfig = new UploadConfig();
        $filetype = ($type == 'audio') ? '.mp3' : '.mp4';

        parse_str(parse_url($youtubeLink, PHP_URL_QUERY), $vars);
        $video_id = null;

        if (isset($vars['v'])) {
            $video_id = $vars['v'];
        } elseif (isset($vars['si'])) {
            $video_id = $vars['si'];
        } else {
            // Try to extract video ID from different YouTube URL formats
            if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/', $youtubeLink, $matches)) {
                $video_id = $matches[1];
            }
        }

        if (!$video_id) {
            throw new \Exception("Could not extract video ID from YouTube URL: " . $youtubeLink);
        }

        $yt = new YoutubeDl();
        $ytDlpPath = $uploadConfig->ffmpegPath . 'yt-dlp';
        
        if (!file_exists($ytDlpPath)) {
            throw new \Exception("yt-dlp binary not found at: " . $ytDlpPath);
        }
        
        $yt->setBinPath($ytDlpPath);
        if ($type == 'audio') {
            if (file_exists(WRITEPATH . "uploads/$uploadConfig->urlAudioUploadPath/" . $video_id . '.mp3')) {
                return $video_id . '.mp3';
            }

            $collection = $yt->download(
                Options::create()
                    ->downloadPath(WRITEPATH . "uploads/$uploadConfig->urlAudioUploadPath")
                    ->extractAudio(true)
                    ->audioFormat('mp3')
                    ->audioQuality('0') // best
                    ->output($video_id)
                    ->url($youtubeLink)
                    ->cookies($uploadConfig->ffmpegPath . 'www.youtube.com_cookies.txt')
            );
        } else {
            if (file_exists(WRITEPATH . "uploads/$uploadConfig->urlVideoUploadPath/" . $video_id . '.mp4')) {
                return $video_id . '.mp4';
            }

            $collection = $yt->download(
                Options::create()
                    ->downloadPath(WRITEPATH . "uploads/$uploadConfig->urlVideoUploadPath")
                    ->format('mp4')
                    ->output($video_id . '.mp4')
                    ->url($youtubeLink)
                    ->cookies($uploadConfig->ffmpegPath . 'www.youtube.com_cookies.txt')
            );
        }

        foreach ($collection->getVideos() as $video) {
            if ($video->getError() !== null) {
                log_message('error', "Error downloading video: {$video->getError()}");
                throw new \Exception("Error downloading video: {$video->getError()}");
            }
        }

        return $video_id . $filetype;
    }

    public function delete($id)
    {
        $tournamentLibrary = new TournamentLibrary();
        $tournamentLibrary->deleteTournament($id);

        return json_encode(['msg' => "Tournament was deleted successfully."]);
    }

    public function upload()
    {
        $validationRule = [
            'audio' => [
                'label' => 'Audio File',
                'rules' => [
                    'uploaded[audio]',
                    'mime_in[audio,audio/mpeg,audio/wav,audio/ogg,audio/mid,audio/x-midi]',
                    'max_size[audio,104857600]', // Limits file size to 100MB (102400 KB)
                ],
                'errors' => [
                    'uploaded' => 'Please upload a file.',
                    'mime_in' => 'The uploaded file must be a valid audio format.',
                    'max_size' => 'The file size must not exceed 10MB.',
                ],
            ],
        ];

        if (!$this->validateData([], $validationRule)) {
            $data = ['errors' => $this->validator->getErrors()];

            return $this->response->setJSON($data);
        }

        $uploadConfig = new UploadConfig();

        $audio = $this->request->getFile('audio');

        if (!$audio->hasMoved()) {
            $filepath = $audio->store($uploadConfig->localAudioUploadPath);

            $data = ['uploaded_fileinfo' => new File($filepath), 'path' => $filepath];

            return $this->response->setJSON($data);
        }

        $data = ['errors' => 'The file has already been moved.'];

        return $this->response->setJSON($data);
    }

    public function uploadVideo()
    {
        $validationRule = [
            'video' => [
                'label' => 'Video File',
                'rules' => [
                    'uploaded[video]',
                    'mime_in[video,video/mp4]',
                    'max_size[video, 524288000]', // Limits file size to 500MB
                ],
                'errors' => [
                    'uploaded' => 'Please upload a file.',
                    'mime_in' => 'The uploaded file must be a valid video format.',
                    'max_size' => 'The file size must not exceed 500MB.',
                ],
            ],
        ];

        if (!$this->validateData([], $validationRule)) {
            $data = ['errors' => $this->validator->getErrors()];

            return $this->response->setJSON($data);
        }

        $uploadConfig = new UploadConfig();

        $video = $this->request->getFile('video');

        if (!$video->hasMoved()) {
            $filepath = $video->store($uploadConfig->localVideoUploadPath);

            $data = ['uploaded_fileinfo' => new File($filepath), 'path' => $filepath];

            return $this->response->setJSON($data);
        }

        $data = ['errors' => 'The file has already been moved.'];

        return $this->response->setJSON($data);
    }

    public function fetchShareSettings($tournament_id)
    {
        $tournament = $this->tournamentModel->find($tournament_id);
        if (!$tournament) {
            return json_encode(['status' => 'failed', 'msg' => 'Tournament was not found!']);
        }

        $settings = $this->shareSettingModel->where('tournament_id', $tournament_id)->findAll();

        $settings_with_users = [];
        if ($settings) {
            $userModel = model('CodeIgniter\Shield\Models\UserModel');

            foreach ($settings as $setting) {
                $setting['private_users'] = null;

                if ($setting['target'] == SHARE_TO_USERS) {
                    $users = explode(',', $setting['users']);

                    $setting['private_users'] = implode(',', array_column($userModel->select('username')->find($users), 'username'));
                }

                $setting['created_at'] = convert_to_user_timezone($setting['created_at'], user_timezone(auth()->user()->id));
                $setting['updated_at'] = convert_to_user_timezone($setting['updated_at'], user_timezone(auth()->user()->id));

                $settings_with_users[] = $setting;
            }
        }

        $token = $this->generateUniqueTokenToShare($tournament_id);

        return json_encode(['status' => 'success', 'settings' => $settings_with_users, 'token' => $token]);
    }

    public function generateShareToken($tournament_id)
    {
        if ($this->request->isAJAX()) {
            $token = $this->generateUniqueTokenToShare($tournament_id);

            return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                ->setJSON(['status' => 'success', 'message' => 'Vote saved successfully', 'token' => $token]);
        }

        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
            ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }

    private function generateUniqueTokenToShare($tournament_id)
    {
        $config = new \Config\Encryption();

        $maxAttempts = 10; // Prevent infinite loops
        $attempts = 0;

        do {
            // Generate a longer token for better uniqueness
            $rawToken = hash_hmac(
                'sha256',
                'tournament_' . $tournament_id .
                '_user_' . auth()->user()->id .
                '_time_' . microtime(true),
                $config->key
            );

            // Take a portion of the hash (consider 8-10 chars for better uniqueness)
            $token = substr($rawToken, 0, 8);

            // Check if token exists in database
            $exists = $this->shareSettingModel->where('token', $token)->first();

            $attempts++;

            if ($attempts >= $maxAttempts) {
                throw new \RuntimeException('Failed to generate unique token after multiple attempts');
            }

        } while ($exists !== null);

        return $token;
    }

    public function share($id)
    {
        $data = $this->request->getPost();
        $data['user_id'] = auth()->user()->id;

        $shareSetting = $this->shareSettingModel->where(['tournament_id' => $data['tournament_id'], 'token' => $data['token']])->first();
        if ($shareSetting) {
            $data['id'] = $shareSetting['id'];
        }

        $this->shareSettingModel->save($data);

        $share = $this->shareSettingModel->where(['tournament_id' => $data['tournament_id'], 'token' => $data['token']])->first();

        $share['private_users'] = null;
        if ($share['target'] == SHARE_TO_USERS) {
            $userModel = model('CodeIgniter\Shield\Models\UserModel');
            $users = explode(',', $share['users']);

            $share['private_users'] = implode(',', array_column($userModel->select('username')->find($users), 'username'));
        }

        $role = 'Viewer';
        if ($share['permission'] == SHARE_PERMISSION_EDIT) {
            $role = 'Editor';
        }

        /** Notifiy to the users */
        if (isset($users) && count($users)) {
            $userSettingsService = service('userSettings');
            $tournament = $this->tournamentModel->find($data['tournament_id']);
            $tournamentEntity = new \App\Entities\Tournament($tournament);

            foreach ($users as $user) {
                if ($shareSetting && $shareSetting['target'] == SHARE_TO_USERS) {
                    $msg = lang('Notifications.tournamentShareUpdated', [$tournamentEntity->name]);
                    $notificationType = NOTIFICATION_TYPE_FOR_SHARE_UPDATED;
                    $emailSubject = lang('Emails.tournamentShareResetEmailSubject');
                    $emailTemplate = 'email/tournament-share-reset';
                } else {
                    $msg = lang('Notifications.tournamentShared', [$tournamentEntity->name]);
                    $notificationType = NOTIFICATION_TYPE_FOR_SHARE;
                    $emailSubject = lang('Emails.tournamentShareEmailSubject');
                    $emailTemplate = 'email/tournament-share';
                }

                $shared_by = (auth()->user()) ? auth()->user()->id : 0;

                $notification = ['message' => $msg, 'type' => $notificationType, 'user_id' => $shared_by, 'user_to' => $user, 'link' => 'tournaments/shared/' . $share['token']];
                $this->notificationService->addNotification($notification);

                $shared_to = auth()->getProvider()->findById($user);

                if (!$shared_to) {
                    continue;
                }

                /** Send the email */
                if (!$userSettingsService->get('email_notification', $shared_to->id) || $userSettingsService->get('email_notification', $shared_to->id) == 'on') {
                    $email = service('email');
                    $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
                    $email->setTo($shared_to->email);
                    $email->setSubject($emailSubject);
                    $email->setMessage(view(
                        $emailTemplate,
                        ['username' => $shared_to->username, 'tournament' => $tournamentEntity, 'share' => $share, 'role' => $role, 'tournamentCreatorName' => setting('Email.fromName')],
                        ['debug' => false]
                    ));

                    if ($email->send(false) === false) {
                        $data = ['errors' => "sending_emails", 'message' => "Failed to send the emails."];
                    }

                    $email->clear();
                }
            }
        }

        return json_encode(['msg' => "Success to save the sharing information.", 'share' => $share]);
    }

    public function purgechShareSettings($share_id)
    {
        $share = $this->shareSettingModel->find($share_id);
        if ($share['target'] == SHARE_TO_USERS) {
            $users = explode(',', $share['users']);
        }

        $this->shareSettingModel->delete([$share_id]);
        $shares = $this->shareSettingModel->where(['tournament_id' => $share['tournament_id']])->findAll();

        /** Notifiy to the users */
        if (isset($users) && count($users)) {
            $userSettingsService = service('userSettings');
            $tournament = $this->tournamentModel->find($share['tournament_id']);
            $tournamentEntity = new \App\Entities\Tournament($tournament);

            foreach ($users as $user) {
                $shared_by = (auth()->user()) ? auth()->user()->id : 0;

                $msg = lang('Notifications.tournamentSharePurged', [$tournamentEntity->name]);
                $notification = ['message' => $msg, 'type' => NOTIFICATION_TYPE_FOR_SHARE_PURGED, 'user_id' => $shared_by, 'user_to' => $user, 'link' => 'tournaments/shared/' . $share['token']];
                $this->notificationService->addNotification($notification);

                $shared_to = auth()->getProvider()->findById($user);

                if (!$shared_to) {
                    continue;
                }

                /** Send the email */
                if (!$userSettingsService->get('email_notification', $shared_to->id) || $userSettingsService->get('email_notification', $shared_to->id) == 'on') {
                    $email = service('email');
                    $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
                    $email->setTo($shared_to->email);
                    $email->setSubject(lang('Emails.tournamentSharePurgedEmailSubject'));
                    $email->setMessage(view(
                        'email/tournament-share-purged',
                        ['username' => $shared_to->username, 'tournament' => $tournamentEntity, 'share' => $share, 'tournamentCreatorName' => setting('Email.fromName')],
                        ['debug' => false]
                    ));

                    if ($email->send(false) === false) {
                        $data = ['errors' => "sending_emails", 'message' => "Failed to send the emails."];
                    }

                    $email->clear();
                }
            }
        }

        return json_encode(['status' => 'success', 'shares' => $shares, 'tournament_id' => $share['tournament_id']]);
    }

    public function fetchShareSetting($share_id)
    {
        $share = $this->shareSettingModel->find($share_id);
        $share['private_users'] = null;
        if ($share['target'] == SHARE_TO_USERS) {
            $userModel = model('CodeIgniter\Shield\Models\UserModel');
            $users = explode(',', $share['users']);

            $share['private_users'] = $userModel->find($users);
        }

        return json_encode(['status' => 'success', 'share' => $share]);
    }

    public function getActionHistory($tournament_id)
    {
        $logActionsModel = model('\App\Models\LogActionsModel');
        $roundSettingsModel = model('\App\Models\TournamentRoundSettingsModel');

        $history = $logActionsModel->getLogs()->where('tournament_id', $tournament_id)->findAll();

        $tournament = $this->tournamentModel->find($tournament_id);

        $data = [];
        $type = null;
        $description = null;
        if ($history && count($history)) {
            foreach ($history as $row) {

                if ($row['action'] == BRACKET_ACTIONCODE_CLEAR) {
                    $tournamentName = $tournament['name'];
                    $type = 'Reset';
                    $description = "Tournament \"$tournamentName\" was reset.";
                } else {
                    $params = json_decode($row['params']);
                    $participants = [];
                    if (isset($params->participants)) {
                        $participants = $params->participants;
                    }

                    $roundSetting = $roundSettingsModel->where(['tournament_id' => $row['tournament_id'], 'round_no' => $params->round_no])->first();

                    $roundName = ($roundSetting) ? $roundSetting['round_name'] : "round $params->round_no";
                    if ($row['action'] == BRACKET_ACTIONCODE_MARK_WINNER) {
                        $type = 'Mark Winner';
                        $participantName = isset($participants->type) ? "Group \"$participants->name\"" : "Participant \"$participants->name\"";
                        $description = "$participantName in bracket #$params->bracket_no marked as a winner in $roundName";
                    }

                    if ($row['action'] == BRACKET_ACTIONCODE_UNMARK_WINNER) {
                        $type = 'Unmark Winner';
                        $participantName = isset($participants->type) ? "Group \"$participants->name\"" : "Participant \"$participants->name\"";
                        $description = "$participantName in bracket #$params->bracket_no unmarked winner in $roundName";
                    }

                    if ($row['action'] == BRACKET_ACTIONCODE_CHANGE_PARTICIPANT) {
                        $type = 'Change Participant';
                        $ptName1 = $participants[0] ? $participants[0]->name : "Empty";
                        $ptName2 = $participants[1]->name;

                        $name2 = isset($participants[1]->type) ? "Group \"$ptName2\"" : "Participant \"$ptName2\"";

                        if ($participants[0]) {
                            $name1 = isset($participants[0]->type) ? "Group \"$ptName1\"" : "Participant \"$ptName1\"";
                            $description = "$name1 in bracket #$params->bracket_no of $roundName was changed to $name2";
                        } else {
                            $description = "$name2 was added to the bracket #$params->bracket_no of $roundName";
                        }
                    }

                    if ($row['action'] == BRACKET_ACTIONCODE_ADD_PARTICIPANT) {
                        $type = 'Add Participant';
                        $name = isset($participants->type) ? "Group \"$participants->name\"" : "Participant \"$participants->name\"";
                        $description = "$name added in bracket #$params->bracket_no in $roundName";
                    }

                    if ($row['action'] == BRACKET_ACTIONCODE_REMOVE_PARTICIPANT) {
                        $type = 'Remove Participant';
                        $name = isset($participants->type) ? "Group \"$participants->name\"" : "Participant \"$participants->name\"";

                        $description = "$name removed from bracket #$params->bracket_no in $roundName";
                    }

                    if ($row['action'] == BRACKET_ACTIONCODE_DELETE) {
                        $type = 'Delete Bracket';
                        if (isset($participants[1])) {
                            $description = "Bracket #$params->bracket_no containing participants \"$participants[0]\" and \"$participants[1]\" in \"$roundName\" deleted";
                        } else {
                            $description = "Bracket #$params->bracket_no containing participants \"$participants[0]\" in \"$roundName\" deleted";
                        }
                    }

                    if ($row['action'] == BRACKET_ACTIONCODE_VOTE) {
                        $type = 'Vote';
                        $name = $participants->type ? "Group \"$participants->name\"" : "Participant \"$participants->name\"";

                        $description = "$name in bracket #$params->bracket_no of $roundName was voted";
                    }
                }

                $time = auth()->user() ? convert_to_user_timezone($row['updated_at'], user_timezone(auth()->user()->id)) : $row['updated_at'];
                $data[] = [
                    'name' => $row['username'],
                    'system_log' => intval($row['system_log']),
                    'type' => $type,
                    'description' => $description,
                    'time' => $time
                ];
            }
        }

        return json_encode(['result' => 'success', 'history' => $data, 'tournament' => $tournament]);
    }

    public function fetchUsersList()
    {
        $userModel = model('CodeIgniter\Shield\Models\UserModel');

        if ($this->request->getPost('query')) {
            $userModel->like('username', $this->request->getPost('query'));
        }

        $users = $userModel->select(['id', 'username'])->findAll();

        return json_encode(['result' => 'success', 'users' => $users, 'query' => $this->request->getPost()]);
    }

    public function bulkDelete()
    {
        $ids = $this->request->getPost('id');

        $tournamentLibrary = new TournamentLibrary();

        /** Alert Message */
        $tournaments = $this->tournamentModel->whereIn('id', $ids)->findAll();
        $tournament_names = '';
        foreach ($tournaments as $index => $tournament) {
            if ($index == (count($tournaments) - 1)) {
                $tournament_names .= $tournament['name'];
            } else {
                $tournament_names .= $tournament['name'] . ',';
            }

            $tournamentLibrary->deleteTournament($tournament['id']);
        }

        return json_encode(['status' => 'success', 'msg' => "The following tournaments was deleted successfully.<br/>" . $tournament_names, 'data' => $ids]);
    }

    public function bulkReset()
    {
        $ids = $this->request->getPost('id');
        $user_id = auth()->user() ? auth()->user()->id : 0;

        $userProvider = auth()->getProvider();
        $userSettingService = service('userSettings');
        $notificationService = service('notification');

        $this->bracketModel->whereIn('tournament_id', $ids)->delete();

        /** Alert Message */
        $tournaments = $this->tournamentModel->whereIn('id', $ids)->findAll();
        $tournament_names = '';
        foreach ($tournaments as $index => $tournament) {
            if ($index == (count($tournaments) - 1)) {
                $tournament_names .= $tournament['name'];
            } else {
                $tournament_names .= $tournament['name'] . ',';
            }

            /** Send the notification and emails to the registered users */
            $registeredUsers = $this->participantModel->where(['tournament_id' => $tournament['id']])->where('registered_user_id Is Not Null')->findColumn('registered_user_id');
            if ($registeredUsers) {
                $tournamentEntity = new \App\Entities\Tournament($tournament);
                foreach ($registeredUsers as $user_id) {
                    $user = $userProvider->findById($user_id);

                    $message = lang('Notifications.tournamentReset', [$tournamentEntity->name]);
                    $notificationService->addNotification(['user_id' => $user_id, 'user_to' => $user->id, 'message' => $message, 'type' => NOTIFICATION_TYPE_FOR_TOURNAMENT_RESET, 'link' => "tournaments/$tournamentEntity->id/view"]);

                    if (!$userSettingService->get('email_notification', $user_id) || $userSettingService->get('email_notification', $user_id) == 'on') {
                        $creator = $userProvider->findById($tournamentEntity->user_id);
                        $email = service('email');
                        $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
                        $email->setTo($user->email);
                        $email->setSubject(lang('Emails.tournamentResetEmailSubject'));
                        $email->setMessage(view(
                            'email/tournament-reset',
                            ['username' => $user->username, 'tournament' => $tournamentEntity, 'creator' => $creator, 'tournamentCreatorName' => setting('Email.fromName')],
                            ['debug' => false]
                        ));

                        if ($email->send(false) === false) {
                            $data = ['errors' => "sending_emails", 'message' => "Failed to send the emails."];
                        }

                        $email->clear();
                    }
                }
            }
        }

        return json_encode(['status' => 'success', 'msg' => "The following tournaments was reseted successfully.<br/>" . $tournament_names, 'data' => $ids]);
    }

    public function bulkUpdate()
    {
        $ids = $this->request->getPost('id');
        $status = $this->request->getPost('status');
        $archive = $this->request->getPost('archive');
        $restore = $this->request->getPost('restore');
        $tournamentModel = model('\App\Models\TournamentModel');

        $msg = "The status of following tournaments was updated successfully.<br/>";
        if ($status) {
            $tournamentModel->whereIn('id', $ids)->set(['status' => $status])->update();
        }

        if ($archive) {
            $tournamentModel->whereIn('id', $ids)->set(['archive' => 1])->update();
            $msg = "The following tournaments was archived successfully.<br/>";
        }

        if ($restore) {
            $tournamentModel->whereIn('id', $ids)->set(['archive' => 0])->update();
            $msg = "The following tournaments was restored successfully.<br/>";
        }

        /** Alert Message */
        $tournaments = $tournamentModel->whereIn('id', $ids)->findAll();
        $tournament_names = '';
        foreach ($tournaments as $index => $tournament) {
            if ($index == (count($tournaments) - 1)) {
                $tournament_names .= $tournament['name'];
            } else {
                $tournament_names .= $tournament['name'] . ',';
            }
        }

        return json_encode(['status' => 'success', 'msg' => $msg . $tournament_names, 'data' => $ids]);
    }

    public function reuseParticipants()
    {
        helper('db');
        helper('participant');

        $userSettingService = service('userSettings');

        $reuse_Id = $this->request->getPost('id');
        $tournament_id = $this->request->getPost('tournament_id') ?? 0;
        $user_id = auth()->user() ? auth()->user()->id : 0;

        if (!$tournament_id || !$user_id) {
            disableForeignKeyCheck();
        }

        // Apply the filter if the user_id parameter is provided
        if (!$reuse_Id) {
            return $this->response->setJSON(['status' => 'error', 'msg' => "Tournament was not selected."]);
        }

        // Fetch the participants
        $tournamentMembers = $this->tournamentMembersModel->asObject()->where('tournament_id', $reuse_Id)->findAll();
        $groupMembers = $this->groupMembersModel->asObject()->where('group_members.tournament_id', $reuse_Id)->details()->findAll();
        $ptIdsOfGroupMembers = array_map(function ($item) {
            return (int) $item->id;
        }, $groupMembers);

        /** Clear existing participants */
        if ($user_id) {
            $this->tournamentMembersModel->where(['tournament_id' => $tournament_id, 'created_by' => $user_id])->delete();
        } else {
            $this->tournamentMembersModel->where(['tournament_id' => $tournament_id, 'hash' => $this->request->getPost('hash')])->delete();
        }

        $notAllowedList = [];

        /** Create new tournament member and group member lists from previous tournaments */
        foreach ($tournamentMembers as $member) {
            // Check if the participant allows the invitation
            if (($participant = $this->tournamentMembersModel->asObject()->participantInfo()->find($member->id)) && $participant->registered_user_id) {
                if (!$available = checkAvailabilityAddToTournament($participant->registered_user_id)) {
                    $notAllowedList[] = $participant->name;
                    continue;
                }
            }
            $newMember = new \App\Entities\TournamentMember([
                'participant_id' => $member->participant_id,
                'tournament_id' => $tournament_id,
                'order' => $member->order,
                'created_by' => $user_id,
                'hash' => $this->request->getPost('hash'),
            ]);

            $member_id = $this->tournamentMembersModel->insert($newMember);

            // Add the group members
            if ($ptIdsOfGroupMembers && in_array($member->participant_id, $ptIdsOfGroupMembers)) {
                $group_id = null;
                if ($groupMembers) {
                    foreach ($groupMembers as $gMember) {
                        if ($gMember->id == $member->participant_id) {
                            $group_id = $gMember->group_id;
                            break;
                        }
                    }
                }

                if ($group_id) {
                    $groupMember = new \App\Entities\GroupMember([
                        'tournament_id' => $tournament_id,
                        'tournament_member_id' => $member_id,
                        'group_id' => $group_id
                    ]);
                    $this->groupMembersModel->insert($groupMember);
                }
            }
        }

        if (!$tournament_id || !$user_id) {
            enableForeignKeyCheck();
        }

        helper('participant_helper');
        $list = getParticipantsAndReusedGroupsInTournament($tournament_id, $this->request->getPost('hash'));

        return $this->response->setJSON(["participants" => $list['participants'], 'notAllowedParticipants' => $notAllowedList, "reusedGroups" => $list['reusedGroups']]);
    }

    public function getParticipants($tournament_id)
    {
        helper('participant_helper');
        $list = getParticipantsAndReusedGroupsInTournament($tournament_id);

        return $this->response->setJSON(["participants" => $list['participants'], "reusedGroups" => $list['reusedGroups']]);
    }

    public function getUsers()
    {
        helper('participant');

        $query = $this->request->getGet('query');

        $userSettingService = service('userSettings');

        $users = auth()->getProvider();
        if ($query) {
            $users = $users->like('username', $query);
        }

        /** Check if the registered user allows the invitations */
        $filteredUsers = [];
        if ($users = $users->findAll()) {
            foreach ($users as $user) {
                if (!$available = checkAvailabilityAddToTournament($user->id)) {
                    continue;
                }

                $filteredUsers[] = $user;
            }
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON($filteredUsers);
    }

    public function saveVote()
    {
        // Check if it's an AJAX request
        if ($this->request->isAJAX()) {
            $voteData = $this->request->getPost(); // Get the posted data
            $voteData['is_group'] = (isset($voteData['is_group']) && $voteData['is_group']) ? 1 : 0;

            $voteModel = model('\App\Models\VotesModel');

            $bracket = $this->bracketModel->find($voteData['bracket_id']);
            $nextBracket = $this->bracketModel->where(['tournament_id' => $bracket['tournament_id'], 'bracketNo' => $bracket['nextGame']])->findAll();
            if (count($nextBracket) == 1) {
                $nextBracket = $nextBracket[0];
            } else {
                $nextBracket = $this->bracketModel->where(['tournament_id' => $bracket['tournament_id'], 'bracketNo' => $bracket['nextGame'], 'is_double' => $bracket['is_double']])->first();
            }

            // Validation (optional, based on your form fields)
            $validation = \Config\Services::validation();
            $validation->setRules([
                'user_id' => 'required|integer',
                'tournament_id' => 'required|integer',
                'participant_id' => 'required|integer',
                'bracket_id' => 'required|integer',
                'round_no' => 'required|integer'
            ]);
            if (auth()->user()) {
                $voteData['user_id'] = auth()->user()->id;
            } else {
                $voteData['user_id'] = 0;
            }

            if (!$validation->run($voteData)) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                    ->setJSON(['status' => 'error', 'message' => $validation->getErrors()]);
            }

            $saveData = $voteData;
            // Update the bracket ID on knockout final
            $tournament_settings = $this->tournamentModel->find($voteData['tournament_id']);
            if ($tournament_settings['type'] == TOURNAMENT_TYPE_KNOCKOUT && $bracket['final_match'] == 1 && $nextBracket) {
                $saveData['bracket_id'] = $nextBracket['id'];
            }
            // End update the bracket ID on knockout final

            //Check if there is the data saved before
            if (auth()->user()) {
                $prevVote = $voteModel->where(['user_id' => auth()->user()->id, 'tournament_id' => $saveData['tournament_id'], 'bracket_id' => $saveData['bracket_id']])->first();
            } else {
                $prevVote = $voteModel->where(['uuid' => $saveData['uuid'], 'tournament_id' => $saveData['tournament_id'], 'bracket_id' => $saveData['bracket_id']])->first();
            }

            if ($prevVote) {
                if ($prevVote['participant_id'] == $voteData['participant_id']) {
                    return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                        ->setJSON(['status' => 'error', 'message' => 'You have already voted for this participant.']);
                } else {
                    $prevVote['participant_id'] = $voteData['participant_id'];
                }

                $saveData['id'] = $prevVote['id'];
            }

            // Check if participant is second one
            $currentTeams = json_decode($bracket['teamnames'], true);
            $isDouble = null;
            foreach ($currentTeams as $ct) {
                if ($ct && $ct['id'] == $voteData['participant_id'] && isset($ct['is_double'])) {
                    $isDouble = 1;
                }
            }
            if ($isDouble) {
                $saveData['is_double'] = 1;
            } else {
                $saveData['is_double'] = null;
            }
            // End check if participant is second one

            $db = \Config\Database::connect();
            $dbDriver = $db->DBDriver;
            if (!auth()->user() && $dbDriver === 'MySQLi') {
                $db->query('SET FOREIGN_KEY_CHECKS = 0;');
            }

            if (!auth()->user() && $dbDriver === 'SQLite3') {
                $db->query('PRAGMA foreign_keys = OFF');
            }

            // Save to database
            if ($voteModel->save($saveData)) {
                /** Save the record to actions log table */
                $logActionsModel = model('\App\Models\LogActionsModel');
                $insert_data = ['tournament_id' => $saveData['tournament_id'], 'action' => BRACKET_ACTIONCODE_VOTE];
                if (auth()->user()) {
                    $insert_data['user_id'] = auth()->user()->id;
                } else {
                    $insert_data['user_id'] = 0;
                }

                $data = [];
                $data['bracket_no'] = $bracket['bracketNo'];
                $data['round_no'] = $saveData['round_no'];

                if ($participant = $this->participantModel->find($saveData['participant_id'])) {
                    $data['participants'] = ['name' => $participant['name'], 'type' => (isset($saveData['is_group']) && $saveData['is_group']) ? 'group' : null];
                }

                $insert_data['params'] = json_encode($data);

                $logActionsModel->insert($insert_data);

                /** Mark Participant win if max vote count reaches */
                $voteData['is_double'] = $isDouble;
                $vote_max_limit = intval($tournament_settings['max_vote_value']);
                $search_params = array_diff_key($voteData, array('bracket_id' => true, 'user_id' => true, 'uuid' => true));
                $votes_in_round = $voteModel->where($search_params)->findAll();

                if ($tournament_settings['evaluation_method'] == EVALUATION_METHOD_VOTING && $tournament_settings['voting_retain']) {
                    $search_params = array_diff_key($search_params, array('round_no' => true));
                }

                if ($nextBracket && is_null($nextBracket['nextGame'])) {
                    $search_params = array_diff_key($search_params, array('is_double' => true));
                }

                /** Get Votes count in a round */
                $votes = $voteModel->where($search_params)->findAll();
                $saveData['votes'] = count($votes);
                if ($tournament_settings['voting_mechanism'] == EVALUATION_VOTING_MECHANISM_MAXVOTE && count($votes_in_round) >= $vote_max_limit) {
                    $voteLibrary = new VoteLibrary();
                    $result = $voteLibrary->markWinParticipant($voteData);

                    if ($tournament_settings && $tournament_settings['type'] == TOURNAMENT_TYPE_KNOCKOUT && $nextBracket['knockout_final']) {
                        $saveData['final_win'] = true;
                    }

                    if ($tournament_settings && $tournament_settings['type'] != TOURNAMENT_TYPE_KNOCKOUT && $nextBracket['final_match'] == 1) {
                        $saveData['final_win'] = true;
                    }
                }

                return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                    ->setJSON(['status' => 'success', 'message' => 'Vote saved successfully', 'data' => $saveData]);
            } else {
                return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                    ->setJSON(['status' => 'error', 'message' => 'Failed to save vote']);
            }

            if (!auth()->user() && $dbDriver === 'MySQLi') {
                $db->query('SET FOREIGN_KEY_CHECKS = 1;');
            }

            if (!auth()->user() && $dbDriver === 'SQLite3') {
                $db->query('PRAGMA foreign_keys = ON');
            }

        }

        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
            ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }
    public function exportLogs()
    {
        $tournament_id = $this->request->getGet('tid');
        $actionHistory = $this->getActionHistory($tournament_id);
        $data = json_decode($actionHistory, true);
        $actionHistory = $data['history'];

        $filename = 'tournaments_' . date('Ymd') . '.csv';

        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        $output = fopen('php://output', 'w');

        // Add the CSV column headers
        fputcsv($output, ['No', 'User', 'Action Type', 'Description', 'Time']);

        // Fetch the data and write it to the CSV
        foreach ($actionHistory as $index => $action) {
            $username = ($action['name']) ? $action['name'] : 'Guest';

            fputcsv($output, [
                $index + 1,
                $username,
                $action['type'],
                $action['description'],
                $action['time']
            ]);
        }

        fclose($output);
        exit;
    }
}