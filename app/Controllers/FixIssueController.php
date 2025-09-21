<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class FixIssueController extends BaseController
{
    public function index()
    {
        $shareSettingModel = model('\App\Models\ShareSettingsModel');
        $tournamentModel = model('\App\Models\TournamentModel');
        $bracketModel = model('\App\Models\BracketModel');
        $musicSettingModel = model('\App\Models\MusicSettingModel');

        $shareSettings = $shareSettingModel->where('user_id', 0)->findAll();
        if ($shareSettings) {
            foreach ($shareSettings as $setting) {
                $tournament = $tournamentModel->find($setting['tournament_id']);

                if ($tournament) {
                    if ($setting['user_id'] != $tournament['user_id']) {
                        $setting['user_id'] = $tournament['user_id'];
                        $shareSettingModel->save($setting);
                    }
                } else {
                    $shareSettingModel->delete($setting['id']);
                }
            }
        }

        return 'Completed';
    }
}