<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Participant extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];

    protected $attributes = [
        'id'            => null,
        'name'       => null,
        'image' => null,
        'hash' => null,
        'is_group' => null,
        'group_id' => null,
        'registered_user_id' => null,
        'active' => null,
        'created_by'  => null
    ];
}