<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUUIDToVotesTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('votes', [
            'uuid' => ['type' => 'varchar', 'constraint' => 255],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('votes', 'uuid');
    }
}