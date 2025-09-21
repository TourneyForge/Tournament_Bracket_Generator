<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAvailableColumnsInTournamentsTable extends Migration
{
    public function up()
    {
        //Add columns for availability, available_start, available_end
        $this->forge->addColumn('tournaments', [
            'availability' => [
                'type' => 'tinyint',
                'null' => true,
                'default' => 0
            ],
            'available_start' => [
                'type' => 'datetime',
                'null' => true
            ],
            'available_end' => [
                'type' => 'datetime',
                'null' => true
            ],
        ]);
    }

    public function down()
    {
        //
        $this->forge->dropColumn('tournaments', ['availability', 'available_start', 'available_end']);
    }
}
