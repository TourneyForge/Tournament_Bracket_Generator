<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class GroupMember extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];
    protected $attributes = [
        'id'            => null,
        'tournament_id' => null,
        'tournament_member_id' => null,
        'group_id'  => null
    ];
}