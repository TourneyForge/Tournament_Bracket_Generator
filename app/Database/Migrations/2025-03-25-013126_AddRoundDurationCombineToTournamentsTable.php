<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRoundDurationCombineToTournamentsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournaments', [
            'round_duration_combine' => ['type' => 'int', 'constraint' => 1, 'default' => '0'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', 'round_duration_combine');
    }
}