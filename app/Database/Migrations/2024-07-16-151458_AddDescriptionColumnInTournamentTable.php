<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDescriptionColumnInTournamentTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournaments', [
            'description' => [
                'type' => 'text',
                'null' => true
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', 'description');
    }
}