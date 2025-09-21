<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVisibilityInTournamentsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournaments', [
            'visibility' => [
                'type' => 'tinyint',
                'null' => true,
                'default' => 0
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', 'visibility');
    }
}