<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSessionIDInParticipants extends Migration
{
    public function up()
    {
        //        
        $this->forge->addColumn('participants', [
            'sessionid' => [
                'type' => 'varchar',
                'constraint' => 255
            ],
        ]);
    }

    public function down()
    {
        //
        $this->forge->dropColumn('participants', 'sessionid');
    }
}