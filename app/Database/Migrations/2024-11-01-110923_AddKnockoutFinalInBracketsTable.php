<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKnockoutFinalInBracketsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('brackets', [
            'knockout_final' => ['type' => 'tinyint', 'constraint' => 1, 'null' => true, 'default' => null],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('brackets', 'knockout_final');
    }
}