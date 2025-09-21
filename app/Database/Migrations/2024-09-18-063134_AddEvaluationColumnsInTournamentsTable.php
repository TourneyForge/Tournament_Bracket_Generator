<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEvaluationColumnsInTournamentsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournaments', [
            'evaluation_method' => ['type' => 'varchar', 'constraint' => 1],
            'voting_accessibility' => ['type' => 'tinyint', 'null' => true],
            'voting_mechanism' => ['type' => 'tinyint', 'null' => true],
            'max_vote_value' => ['type' => 'int', 'null' => true],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', 'evaluation_method');
        $this->forge->dropColumn('tournaments', 'voting_accessibility');
        $this->forge->dropColumn('tournaments', 'voting_mechanism');
        $this->forge->dropColumn('tournaments', 'max_vote_value');
    }
}