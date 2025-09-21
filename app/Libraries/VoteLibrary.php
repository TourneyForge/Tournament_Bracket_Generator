<?php

namespace App\Libraries;

class VoteLibrary
{
    protected $bracketsModel;
    protected $participantsModel;
    protected $tournamentsModel;

    protected $votesModel;
    
    public function __construct()
    {
        // This is called when the library is initialized
        // You can load models, helpers, or any setup here
        $this->bracketsModel = model('\App\Models\BracketModel');
        $this->participantsModel = model('\App\Models\ParticipantModel');
        $this->tournamentsModel = model('\App\Models\TournamentModel');
        $this->votesModel = model('\App\Models\VotesModel');
    }

    public function markWinParticipant($voteData)
    {
        $currentBracket = $this->bracketsModel->find($voteData['bracket_id']);

        $tournament = $this->tournamentsModel->find($currentBracket['tournament_id']);
        if ($currentBracket['final_match'] == 1 && $tournament && $tournament['type'] == TOURNAMENT_TYPE_KNOCKOUT) {
            $final_bracket_ids = $this->bracketsModel->where(['tournament_id' => $currentBracket['tournament_id'], 'final_match' => 1])->findColumn('id');
            $this->bracketsModel->update($final_bracket_ids, ['winner' => null]);
        }

        $currentBracket['winner'] = $voteData['participant_id'];
        $this->bracketsModel->save($currentBracket);
        
        $nextBracket = $this->bracketsModel->where(['tournament_id' => $voteData['tournament_id'], 'bracketNo' => $currentBracket['nextGame']])->findAll();
        if (count($nextBracket) == 1) {
            $nextBracket = $nextBracket[0];
        } else {
            $nextBracket = $this->bracketsModel->where(['tournament_id' => $voteData['tournament_id'], 'bracketNo' => $currentBracket['nextGame'], 'is_double' => $currentBracket['is_double']])->first();
        }

        $participant = null;
        $currentTeams = json_decode($currentBracket['teamnames']);
        if ($currentTeams) {
            foreach ($currentTeams as $cTeam) {
                if ($cTeam && $cTeam->id == $voteData['participant_id']) {
                    $participant = $cTeam;
                }
            }
        }

        if ($nextBracket) {
            $index = 0;
            
            if ($nextBracket['knockout_final']) {
                $nextBracket['lastGames'] = json_encode([$currentBracket['bracketNo'], null]);
            }

            $lastGames = json_decode($nextBracket['lastGames']);

            if (is_int($lastGames)) {
                $lastGames = [$lastGames, null];
            }
            
            foreach ($lastGames as $key => $value) {
                if ($value == $currentBracket['bracketNo']) {
                    $index = $key;
                }
            }

            $teams = json_decode($nextBracket['teamnames']);
            $teams[$index] = $participant;
            if (isset($voteData['is_double']) && $voteData['is_double']) {
                $teams[$index]->is_double = 1;
            }
            $nextBracket['teamnames'] = json_encode($teams);

            if ($tournament && $tournament['type'] == TOURNAMENT_TYPE_KNOCKOUT && $nextBracket['knockout_final'] == 1) {
                $nextBracket['winner'] = $voteData['participant_id'];
            }

            if ($tournament && $tournament['type'] != TOURNAMENT_TYPE_KNOCKOUT && $nextBracket['final_match'] == 1) {
                $nextBracket['winner'] = $voteData['participant_id'];
            }
            
            $this->bracketsModel->save($nextBracket);
        }
        
        return true;
    }

    public function finalizeRound($tournament_id, $round_no)
    {
        $tournament_settings = $this->tournamentsModel->find($tournament_id);
        if ($tournament_settings['voting_mechanism'] == EVALUATION_VOTING_MECHANISM_MAXVOTE) {
            return false;
        }

        $brackets = $this->bracketsModel->where(['tournament_id' => $tournament_id, 'roundNo' => $round_no])->findAll();
        foreach ($brackets as $bracket) {
            $teams = json_decode($bracket['teamnames'], true);

            /** Get the vote count per participants */
            $pa_votes = 0;
            $pb_votes = 0;
            if ($teams[0]) {
                $pa_votes = $this->votesModel->where(['tournament_id' => $tournament_id, 'bracket_id' => $bracket['id'], 'participant_id' => $teams[0]['id'], 'round_no' => $round_no])->countAllResults();
            }

            if (!isset($teams[1])) {
                $teams[1] = null;
            }

            if ($teams[1]) {
                $pb_votes = $this->votesModel->where(['tournament_id' => $tournament_id, 'bracket_id' => $bracket['id'], 'participant_id' => $teams[1]['id'], 'round_no' => $round_no])->countAllResults();
            }
            
            /** Compare vote counts and decide who is a winner */
            if ($teams[0] && $pa_votes > $pb_votes) {
                $winner = $teams[0];
            }

            if ($teams[1] && $pa_votes < $pb_votes) {
                $winner = $teams[1];
            }

            if ($pa_votes == $pb_votes) {
                if (!$teams[0]) {
                    $winner = $teams[1];
                } elseif (!$teams[1]) {
                    $winner = $teams[0];
                } else {
                    $randomNumber = mt_rand(0, 1);
                    $winner = $teams[$randomNumber];
                }
            }
            
            if ($winner) {
                if ($this->markWinParticipant(['tournament_id' => $tournament_id, 'bracket_id' => $bracket['id'], 'participant_id' => $winner['id'], 'round_no' => $round_no])) {
                    $logActionsModel = model('\App\Models\LogActionsModel');
                    $insert_data = ['tournament_id' => $tournament_id, 'action' => BRACKET_ACTIONCODE_MARK_WINNER, 'user_id' => 0, 'system_log' => 1];

                    $participant = $this->participantsModel->find($winner['id']);
                    if (!$participant) {
                        continue;
                    }
                    
                    $data = [];
                    $data['bracket_no'] = $bracket['bracketNo'];
                    $data['round_no'] = $bracket['roundNo'];
                    $data['participants'] = [$participant['name']];

                    $insert_data['params'] = json_encode($data);

                    $db = \Config\Database::connect();
                    $dbDriver = $db->DBDriver;
                    if (!auth()->user() && $dbDriver === 'MySQLi') {
                        $db->query('SET FOREIGN_KEY_CHECKS = 0;');
                    }
                    
                    if (!auth()->user() && $dbDriver === 'SQLite3') {
                        $db->query('PRAGMA foreign_keys = OFF');
                    }

                    $logActionsModel->insert($insert_data);
                    
                    if (!auth()->user() && $dbDriver === 'MySQLi') {
                        $db->query('SET FOREIGN_KEY_CHECKS = 1;');
                    }
                    
                    if (!auth()->user() && $dbDriver === 'SQLite3') {
                        $db->query('PRAGMA foreign_keys = ON');
                    }
                }
            }
        }
        
        return true;
    }
}