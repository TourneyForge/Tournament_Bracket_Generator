<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSystemLogInLogActionsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('log_actions', [
            'system_log' => ['type' => 'tinyint', 'constraint' => 1, 'null' => true, 'default' => 0],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('system_log', 'log_actions');
    }
}