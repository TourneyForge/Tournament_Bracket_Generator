<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Schedule extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];
    
    protected $attributes = [
        'id'            => null,
        'schedule_name'  => null,
        'tournament_id' => null,
        'round_no' => null,
        'result' => null,
        'schedule_time' => null,
    ];
}