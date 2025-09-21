<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddArchivedColumnToTournamentsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournaments', [
            'archive' => [
                'type' => 'tinyint',
                'null' => true, 
                'default' => 0
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', 'archive');
    }
}