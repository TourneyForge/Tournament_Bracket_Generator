<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddThemeToTournamentsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournaments', [
            'theme' => ['type' => 'varchar', 'constraint' => 2, 'default' => 'cl'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', 'theme');
    }
}