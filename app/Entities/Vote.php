<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Vote extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];
    
    protected $attributes = [
        'id'            => null,
        'user_id'  => null,
        'tournament_id' => null,
        'bracket_id' => null,
        'participant_id' => null,
        'is_group' => null,
        'round_no' => null,
        'uuid' => null,
        'is_double' => null
    ];
}