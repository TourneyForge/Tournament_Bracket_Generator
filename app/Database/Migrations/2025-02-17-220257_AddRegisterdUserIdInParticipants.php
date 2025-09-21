<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRegisterdUserIdInParticipants extends Migration
{
    public function up()
    {
        $this->forge->addColumn('participants', [
            'registered_user_id' => [
                'type' => 'int',
                'null' => true, 
                'default' => null
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('participants', 'registered_user_id');
    }
}