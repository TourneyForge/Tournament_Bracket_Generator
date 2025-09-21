<?php
use CodeIgniter\CodeIgniter;

if (!function_exists('getParticipantsAndReusedGroupsInTournament')) {
    /**
     *
     * @param int $tournament_id
     * @return array
     */
    function getParticipantsAndReusedGroupsInTournament ($tournament_id, $hash = null)
    {
        $tournamentMembersModel = model('\App\Models\TournamentMembersModel');
        $groupMembersModel = model('\App\Models\GroupMembersModel');
        $userSettingService = service('userSettings');

        $participants = [];
        if ($tournament_id) {
            $participants = $tournamentMembersModel->where(['tournament_members.tournament_id' => $tournament_id])->participantInfo()->findAll();
        } else {
            $participants = $tournamentMembersModel->where(['tournament_members.tournament_id' => 0, 'tournament_members.hash' => $hash])->participantInfo()->findAll();
        }

        $filteredParticipants = [];
        $notAllowdParticipants = [];
        $reusedGroups = [];
        if ($participants) {
            foreach ($participants as $participant) {
                if (!$participant || !$participant['name']) {
                    continue;
                }
                
                if (isset($participant['group_id']) && $participant['group_id'] && !in_array($participant['group_id'], $reusedGroups)) {
                    if (count($groupMembersModel->where('group_id', $participant['group_id'])->groupBy('tournament_id')->findAll()) > 1) {
                        $reusedGroups[] = intval($participant['group_id']);
                    }
                }

                /** Check if the registered user allows the invitations */
                if ($participant['name'][0] == '@' && $participant['registered_user_id']) {
                    if ($userSettingService->get('disable_invitations', $participant['registered_user_id'])) {
                        $participant['invitation_disabled'] = true;
                        $notAllowdParticipants[] = $participant;
                        continue;
                    }
                }

                $filteredParticipants[] = $participant;
            }
        }

        // Remove the participants who refused the invitation in the profile setting
        if ($notAllowdParticipants) {
            foreach ($notAllowdParticipants as $participant) {
                if ($tournament_id) {
                    $participants = $tournamentMembersModel->where(['tournament_members.tournament_id' => $tournament_id, 'participant_id' => $participant['id']])->delete();
                } else {
                    $participants = $tournamentMembersModel->where(['tournament_members.tournament_id' => 0, 'tournament_members.hash' => $hash, 'participant_id' => $participant['id']])->delete();
                }
            }
        }
        
        return ['participants' => $filteredParticipants, 'notAllowed' => $notAllowdParticipants, 'reusedGroups' => $reusedGroups];
    }
}

if (!function_exists('checkAvailabilityAddToTournament')) {
    /**
     *
     * @param int $user_id
     * @return boolean
     */
    function checkAvailabilityAddToTournament ($user_id)
    {
        $userSettingsService = service('userSettings');

        if ($userSettingsService->get('disable_invitations', $user_id)) {
            return false;
        } 
        
        return true;
    }
}