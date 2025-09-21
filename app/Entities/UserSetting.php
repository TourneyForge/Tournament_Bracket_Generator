<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class UserSetting extends Entity
{
    protected $datamap = [];
    protected $attributes = [
        'id'            => null,
        'user_id'       => null,
        'setting_name'  => null,
        'setting_value' => null,
        'uuid' => null
    ];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];
}