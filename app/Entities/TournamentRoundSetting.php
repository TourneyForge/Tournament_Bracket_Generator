<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class TournamentRoundSetting extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];
    protected $attributes = [
        'id'            => null,
        'user_id'  => null,
        'tournament_id' => null,
        'round_no' => null,
        'round_name' => null,
        'knockout_second' => null
    ];
}