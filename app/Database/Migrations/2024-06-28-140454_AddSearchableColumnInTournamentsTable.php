<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSearchableColumnInTournamentsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournaments', [
            'searchable' => [
                'type' => 'text',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', 'searchable');
    }
}