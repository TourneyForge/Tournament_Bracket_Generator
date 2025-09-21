<?php

namespace App\Libraries;

class ScheduleLibrary
{
    protected $bracketsModel;
    protected $participantsModel;
    protected $tournamentsModel;
    protected $votesModel;
    protected $schedulesModel;
    
    public function __construct()
    {
        // This is called when the library is initialized
        // You can load models, helpers, or any setup here
        $this->bracketsModel = model('\App\Models\BracketModel');
        $this->participantsModel = model('\App\Models\ParticipantModel');
        $this->tournamentsModel = model('\App\Models\TournamentModel');
        $this->votesModel = model('\App\Models\VotesModel');
        $this->schedulesModel = model('\App\Models\SchedulesModel');
    }

    public function scheduleRoundUpdate($tournament_id)
    {
        $tournamentSettings = $this->tournamentsModel->find($tournament_id);
        $maxRounds = $this->bracketsModel->where(['tournament_id' => $tournament_id])->selectMax('roundNo')->get()->getRow();

        $this->schedulesModel->where(['tournament_id' => $tournament_id])->delete();

        if ($tournamentSettings['availability']) {
            $startDate = new \DateTime($tournamentSettings['available_start']);
            $endDate = new \DateTime($tournamentSettings['available_end']);
            $this->registerSchedule($tournament_id, SCHEDULE_NAME_TOURNAMENTSTART, 1, $startDate->format('Y-m-d H:i:s'));

            // Calculate the total hours between start and end date
            $tournamentDuration = $endDate->getTimestamp() - $startDate->getTimestamp(); // Total duration in seconds
            $totalHours = $tournamentDuration / 3600; // Convert seconds to hours
            
            // Divide hours by max rounds
            $roundDuration = ($maxRounds && $maxRounds->roundNo) ? $totalHours / $maxRounds->roundNo : $totalHours;
            $roundDuration = intval($roundDuration * 60);
            for ($i = 1; $i < $maxRounds->roundNo; $i++) {
                $scheduleTime = $startDate->modify("+$roundDuration minutes")->format('Y-m-d H:i:s');
                $this->registerSchedule($tournament_id, SCHEDULE_NAME_ROUNDUPDATE, $i, $scheduleTime);
            }

            $scheduleTime = $endDate->format('Y-m-d H:i:s');
            $this->registerSchedule($tournament_id, SCHEDULE_NAME_TOURNAMENTEND, $maxRounds->roundNo, $scheduleTime);
        }
        
        return true;
    }

    public function registerSchedule($tournament_id, $schedule_name, $round_no, $time) {
        $schedule = $this->schedulesModel->where(['schedule_name' => $schedule_name, 'tournament_id' => $tournament_id, 'round_no' => $round_no])->first();
        if ($schedule) {
            $schedule['schedule_time'] = $time;
            $schedule['result'] = 0;
        } else {
            $schedule = new \App\Entities\Schedule();
            $schedule->schedule_name = $schedule_name;
            $schedule->tournament_id = $tournament_id;
            $schedule->round_no = $round_no;
            $schedule->schedule_time = $time;
            $schedule->result = 0;
        }

        $this->schedulesModel->save($schedule);
    }

    public function unregisterSchedule($tournament_id) {
        $this->schedulesModel->where(['schedule_name' => SCHEDULE_NAME_ROUNDUPDATE, 'tournament_id' => $tournament_id])->delete();
    }
}