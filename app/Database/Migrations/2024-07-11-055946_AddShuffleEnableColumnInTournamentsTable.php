<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShuffleEnableColumnInTournamentsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournaments', [
            'shuffle_enabled' => [
                'type' => 'tinyint',
                'null' => true, 
                'default' => 0
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', 'shuffle_enabled');
    }
}