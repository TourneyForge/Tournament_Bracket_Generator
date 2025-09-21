<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddScoreEnabledColumnInTournamentsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournaments', [
            'score_enabled' => [
                'type' => 'tinyint',
                'null' => true,
                'default' => 0
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', 'score_enabled');
    }
}