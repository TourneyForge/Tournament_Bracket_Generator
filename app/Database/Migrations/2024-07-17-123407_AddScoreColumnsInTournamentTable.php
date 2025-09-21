<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddScoreColumnsInTournamentTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournaments', [
            'score_bracket' => [
                'type' => 'tinyint',
                'null' => true
            ],
        ]);
        $this->forge->addColumn('tournaments', [
            'increment_score' => [
                'type' => 'tinyint',
                'null' => true
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', 'score_bracket');
        $this->forge->dropColumn('tournaments', 'increment_score');
    }
}