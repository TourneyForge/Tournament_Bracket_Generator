<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIncrementScoreEnabledColumnInTournamentsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournaments', [
            'increment_score_enabled' => [
                'type' => 'tinyint',
                'null' => true,
                'default' => 0
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', 'increment_score_enabled');
    }
}