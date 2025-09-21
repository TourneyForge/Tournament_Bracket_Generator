<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsDoubleInVotesTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('votes', [
            'is_double' => ['type' => 'tinyint', 'constraint' => 1, 'null' => true],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('votes', 'is_double');
    }
}