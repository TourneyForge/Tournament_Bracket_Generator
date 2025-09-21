<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMarkByHostToBracketsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('brackets', [
            'win_by_host' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('brackets', 'win_by_host');
    }
}