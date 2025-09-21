<?php
use CodeIgniter\CodeIgniter;

if (!function_exists('checkParticipantExistingInTournament')) {
    /**
     * Retrieve the user's timezone from the database.
     *
     * @param int $tournament_id
     * @param int $participant_id
     * @return boolean
     */
    function checkParticipantExistingInTournament ($tournament_id, $participant_id): bool
    {
        $participantIsExists = false;

        $bracketsModel = model('\App\Models\BracketModel');
        $brackets = $bracketsModel->where('tournament_id', $tournament_id)->findAll();
                
        if ($brackets) {
            foreach ($brackets as $bt) {
                $teams = json_decode($bt['teamnames'], true);
                if ($teams) {
                    foreach ($teams as $team) {
                        if ($team && $team['id'] == $participant_id) {
                            $participantIsExists = true;
                            continue;
                        }
                    }
                }
            }
        }
        
        return $participantIsExists;
    }
}