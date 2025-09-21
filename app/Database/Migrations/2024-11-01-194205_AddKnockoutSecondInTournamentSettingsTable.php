<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKnockoutSecondInTournamentSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournament_round_settings', [
            'knockout_second' => ['type' => 'tinyint', 'constraint' => 1, 'null' => true, 'default' => null],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournament_round_settings', 'knockout_second');
    }
}