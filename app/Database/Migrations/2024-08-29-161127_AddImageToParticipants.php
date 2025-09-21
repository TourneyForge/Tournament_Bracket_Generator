<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImageToParticipants extends Migration
{
    public function up()
    {
        //
        $this->forge->addColumn('participants', [
            'image' => [
                'type' => 'text',
                'null' => true
            ],
        ]);
    }

    public function down()
    {
        //
        $this->forge->dropColumn('participants', 'image');
    }
}
