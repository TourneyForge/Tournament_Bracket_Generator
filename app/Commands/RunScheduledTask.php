<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RunScheduledTask extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Tasks';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'task:run';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Run the scheduled tasks.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'task:run [arguments] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $voteLibrary = new \App\Libraries\VoteLibrary();
        $schedulesModel = model('\App\Models\SchedulesModel');
        $schedules = $schedulesModel->where(['result' => 0])->findAll();
        $userProvider = auth()->getProvider();
        $userSettingService = service('userSettings');
        $notificationService = service('notification');

        $tournamentsModel = model('\App\Models\TournamentModel');
        $tournamentMembersModel = model('\App\Models\TournamentMembersModel');
        $shareSettingsModel = model('\App\Models\ShareSettingsModel');
        $participantsModel = model('\App\Models\ParticipantModel');

        $host_id = auth()->user() ? auth()->user()->id : 0;
        
        if ($schedules) {
            foreach($schedules as $schedule) {
                $schedule_time = new \DateTime($schedule['schedule_time']);
                $current_time = new \DateTime();
                
                $tournament = $tournamentsModel->asObject()->find($schedule['tournament_id']);

                $sendNotificationsTo = [];
                if (($schedule['schedule_name'] == SCHEDULE_NAME_TOURNAMENTSTART || $schedule['schedule_name'] == SCHEDULE_NAME_TOURNAMENTEND) && $current_time >= $schedule_time) {
                    $registeredUsers = $tournamentMembersModel->where(['tournament_members.tournament_id' => $schedule['tournament_id']])->where('registered_user_id Is Not Null')->participantInfo()->findColumn('registered_user_id');

                    if ($registeredUsers) {
                        $sendNotificationsTo = $registeredUsers;
                    }

                    if ($userProvider->findById($tournament->user_id)) {
                        $sendNotificationsTo[] = $tournament->user_id;
                    }

                    $sharedTo = $shareSettingsModel->where(['tournament_id' => $tournament->id, 'target' => SHARE_TO_USERS])->findColumn('users');
                    if ($sharedTo) {
                        foreach($sharedTo as $to) {
                            $user_ids = json_decode($to, true);
                            if ($user_ids) {
                                array_push($sendNotificationsTo, $to);
                            }
                        }
                    }

                    $sendNotificationsTo = array_unique($sendNotificationsTo);
                    if ($sendNotificationsTo) {
                        foreach ($sendNotificationsTo as $user_id) {
                            $user = $userProvider->findById($user_id);
                            
                            if (!$user) {
                                continue;
                            }

                            $participantWithGroupInfo = $tournamentMembersModel->where('participant_id', $user_id)->participantInfo()->first();
                            $groupName = $participantWithGroupInfo ? $participantWithGroupInfo['group_name'] : null;
                            
                            if ($schedule['schedule_name'] == SCHEDULE_NAME_TOURNAMENTSTART) {
                                $string = $groupName ? $tournament->name . " (Group: $groupName)" : $tournament->name . " (Individual participant)";
                                $message = "The tournament $string has started!";
                                $notificationService->addNotification(['user_id' => $host_id, 'user_to' => $user->id, 'message' => $message, 'type' => NOTIFICATION_TYPE_FOR_TOURNAMENT_STARTED, 'link' => "tournaments/$tournament->id/view"]);
                            }
                            if ($schedule['schedule_name'] == SCHEDULE_NAME_TOURNAMENTEND) {
                                $string = $groupName ? $tournament->name . " (Group: $groupName)" : $tournament->name . " (Individual participant)";
                                $message = "The tournament $string has completed!";
                                $notificationService->addNotification(['user_id' => $host_id, 'user_to' => $user->id, 'message' => $message, 'type' => NOTIFICATION_TYPE_FOR_TOURNAMENT_COMPLETED, 'link' => "tournaments/$tournament->id/view"]);
                            }

                            if (!$userSettingService->get('email_notification', $user_id) || $userSettingService->get('email_notification', $user_id) == 'on') {
                                $creator = $userProvider->findById($tournament->user_id);
                                $email = service('email');
                                $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
                                $email->setTo($user->email);
                                if ($schedule['schedule_name'] == SCHEDULE_NAME_TOURNAMENTSTART) {
                                    $email->setSubject(lang('Emails.tournamentStartedEmailSubject', [$tournament->name]));
                                    $email->setMessage(view(
                                        'email/tournament-started',
                                        ['username' => $user->username, 'tournament' => $tournament, 'creator' => $creator, 'role' => 'Participant', 'tournamentCreatorName' => setting('Email.fromName'), 'groupName' => $groupName],
                                        ['debug' => false]
                                    ));
                                }

                                if ($schedule['schedule_name'] == SCHEDULE_NAME_TOURNAMENTEND) {
                                    $email->setSubject(lang('Emails.tournamentCompletedEmailSubject', [$tournament->name]));
                                    $email->setMessage(view(
                                        'email/tournament-completed',
                                        ['username' => $user->username, 'tournament' => $tournament, 'creator' => $creator, 'tournamentCreatorName' => setting('Email.fromName'), 'groupName' => $groupName],
                                        ['debug' => false]
                                    ));
                                }

                                if ($email->send(false) === false) {
                                    $data = ['errors' => "sending_emails", 'message' => "Failed to send the emails."];
                                }

                                $email->clear();
                            }
                        }
                    }

                    if ($schedule['schedule_name'] == SCHEDULE_NAME_TOURNAMENTSTART) {
                        $schedulesModel->update($schedule['id'], ['result' => 1]);

                        if ($tournament) {
                            $tournament->status = TOURNAMENT_STATUS_INPROGRESS;
                            $tournamentsModel->save($tournament);
                        }
                    }

                    if ($schedule['schedule_name'] == SCHEDULE_NAME_TOURNAMENTEND) {
                        if ($tournament) {
                            $tournament->status = TOURNAMENT_STATUS_COMPLETED;
                            $tournamentsModel->save($tournament);
                        }
                    }
                }
                
                if (($schedule['schedule_name'] == SCHEDULE_NAME_ROUNDUPDATE || $schedule['schedule_name'] == SCHEDULE_NAME_TOURNAMENTEND) && $current_time >= $schedule_time) {
                    $voteLibrary->finalizeRound($schedule['tournament_id'], $schedule['round_no']);

                    $schedulesModel->update($schedule['id'], ['result' => 1]);
                }
            }
        }

        /** Remove expired tournaments */
        $tournamentLibrary = new \App\Libraries\TournamentLibrary();

        $tournaments = $tournamentsModel->where(['user_id' => 0])->findAll();
        foreach ($tournaments as $tournament) {
            if(time() - strtotime($tournament['created_at']) > 86400){
                /** Remove expired temp tournaments from cookie value */
                $tournamentLibrary->deleteTournament($tournament['id']);
            }
        }

        /** Check the activity history and find the inactive users */
        $this->checkAndNotifyInactivity(30);
        $this->checkAndNotifyInactivity(60);
        $this->checkAndNotifyInactivity(90);

        /** Update the tournament status completed */
        $tournaments = $tournamentsModel->findAll();
        foreach ($tournaments as $tournament) {
            if($tournament['available_end'] && (time() > strtotime($tournament['available_end'])) && ($tournament['status'] != TOURNAMENT_STATUS_COMPLETED)){
                $tournament['status'] = TOURNAMENT_STATUS_COMPLETED;
                $tournamentsModel->save($tournament);
            }
        }
    }

    private function checkAndNotifyInactivity($days)
    {
        $inactiveNotifyHistoryModel = model('\App\Models\InactiveNotifyHistoryModel');
        $currentDate = new \DateTime();
        
        $userProvider = auth()->getProvider();
        $users = $userProvider->findAll();
        if ($users) {
            foreach ($users as $user) {
                $lastActive = $user->last_active ?? $user->created_at;
                if ($currentDate->format('Y-m-d') == (new \DateTime($lastActive))->modify("+$days days")->format('Y-m-d')) {
                    $notifyHistory = $inactiveNotifyHistoryModel->where(['user_id' => $user->id, 'inactive_days' => $days])->findAll();
                    if (!$notifyHistory) {
                        $inactiveNotifyHistoryModel->save(['user_id' => $user->id, 'inactive_days' => $days]);

                        $email = service('email');
                        $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
                        $email->setTo($user->email);
                        $email->setSubject(lang('Emails.inactive30DaysNotifyEmailSubject'));
                        $email->setMessage(view(
                            "email/inactivity-$days",
                            ['username' => $user->username, 'tournamentCreatorName' => setting('Email.fromName')],
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

        return true;
    }
}