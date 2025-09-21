<?php

namespace App\Libraries;

class TournamentLibrary
{
    protected $bracketsModel;
    protected $participantsModel;
    protected $tournamentsModel;
    protected $tournamentMembersModel;
    protected $votesModel;
    protected $shareSettingsModel;
    protected $audioSettingsModel;
    protected $roundSettingsModel;
    protected $schedulesModel;
    protected $logActionsModel;
    
    public function __construct()
    {
        // This is called when the library is initialized
        // You can load models, helpers, or any setup here
        $this->bracketsModel = model('\App\Models\BracketModel');
        $this->participantsModel = model('\App\Models\ParticipantModel');
        $this->tournamentsModel = model('\App\Models\TournamentModel');
        $this->tournamentMembersModel = model('\App\Models\TournamentMembersModel');
        $this->votesModel = model('\App\Models\VotesModel');
        $this->shareSettingsModel = model('\App\Models\ShareSettingsModel');
        $this->audioSettingsModel = model('\App\Models\AudioSettingModel');
        $this->roundSettingsModel = model('\App\Models\TournamentRoundSettingsModel');
        $this->schedulesModel = model('\App\Models\SchedulesModel');
        $this->logActionsModel = model('\App\Models\LogActionsModel');
    }

    public function deleteTournament($tournament_id)
    {
        $this->shareSettingsModel->where(['tournament_id' => $tournament_id])->delete();
        $this->roundSettingsModel->where(['tournament_id' => $tournament_id])->delete();
        $this->logActionsModel->where(['tournament_id' => $tournament_id])->delete();
        $this->votesModel->where(['tournament_id' => $tournament_id])->delete();

        $registeredUsers = $this->tournamentMembersModel->where(['tournament_members.tournament_id' => $tournament_id])->participantInfo()->where('registered_user_id Is Not Null')->findColumn('registered_user_id');
        $participants = $this->tournamentMembersModel->where(['tournament_members.tournament_id' => $tournament_id])->participantInfo()->findAll();
        if ($participants) {
            foreach ($participants as $participant) {
                // Check if the participant was participated to multiple tournaments and delete it if not
                if (!$participant['id'] || count($this->tournamentMembersModel->where('tournament_members.participant_id', $participant['id'])->groupBy('tournament_id')->findAll()) > 1) {
                    continue;
                }

                if ($participant['image']) {
                    unlink(WRITEPATH . $participant['image']);
                }

                $this->participantsModel->delete($participant['id']);
            }

            $this->tournamentMembersModel->where(['tournament_members.tournament_id' => $tournament_id])->delete();
        }

        $audioSettings = $this->audioSettingsModel->where(['tournament_id' => $tournament_id])->findAll();
        if ($audioSettings) {
            foreach ($audioSettings as $setting) {
                if ($setting['path'] && file_exists(WRITEPATH . 'uploads/' . $setting['path'])) {
                    unlink(WRITEPATH . 'uploads/' . $setting['path']);
                }
            }
        }
        $this->audioSettingsModel->where(['tournament_id' => $tournament_id])->delete();

        $this->bracketsModel->where(['tournament_id' => $tournament_id])->delete();
        
        $this->schedulesModel->where(['tournament_id' => $tournament_id])->delete();

        $tournament = $this->tournamentsModel->find($tournament_id);
        $this->tournamentsModel->where('id', $tournament_id)->delete();

        /** Send the notification and emails to the registered users */
        $auth_user_id = auth()->user() ? auth()->user()->id : 0;
        if ($registeredUsers) {
            $userProvider = auth()->getProvider();
            $userSettingService = service('userSettings');
            $notificationService = service('notification');

            $tournamentEntity = new \App\Entities\Tournament($tournament);
            foreach ($registeredUsers as $user_id) {
                $user = $userProvider->findById($user_id);

                $message = lang('Notifications.tournamentDeleted', [$tournamentEntity->name]);
                $notificationService->addNotification(['user_id' => $auth_user_id, 'user_to' => $user->id, 'message' => $message, 'type' => NOTIFICATION_TYPE_FOR_TOURNAMENT_DELETE, 'link' => "tournaments/$tournamentEntity->id/view"]);

                if (!$userSettingService->get('email_notification', $user_id) || $userSettingService->get('email_notification', $user_id) == 'on') {
                    $creator = $userProvider->findById($tournamentEntity->user_id);
                    $email = service('email');
                    $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
                    $email->setTo($user->email);
                    $email->setSubject(lang('Emails.tournamentDeleteEmailSubject'));
                    $email->setMessage(view(
                        'email/tournament-delete',
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
        
        return true;
    }
}