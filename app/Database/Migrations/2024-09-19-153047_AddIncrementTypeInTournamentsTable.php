<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIncrementTypeInTournamentsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournaments', [
            'increment_score_type' => [
                'type' => 'varchar',
                'constraint' => 1,
                'default' => TOURNAMENT_SCORE_INCREMENT_PLUS
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', 'increment_score_type');
    }
}