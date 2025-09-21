<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUUIDintoUserSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('user_settings', [
            'uuid' => ['type' => 'varchar', 'constraint' => 255],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('votes', 'user_settings');
    }
}