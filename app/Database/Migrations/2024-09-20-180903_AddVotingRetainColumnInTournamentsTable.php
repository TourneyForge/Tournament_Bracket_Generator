<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVotingRetainColumnInTournamentsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournaments', [
            'voting_retain' => [
                'type'       => 'int',
                'constraint' => 11,
                'default' => 0
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', 'voting_retain');
    }
}