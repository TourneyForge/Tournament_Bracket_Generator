<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InactiveNotifyHistoryTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'       => ['type' => 'int', 'constraint' => 11, 'null' => 0],
            'inactive_days' => ['type' => 'tinyint', 'constraint' => 2, 'null' => 0, 'default' => 30],
            'created_at'    => ['type' => 'datetime', 'null' => 0],
            'updated_at'    => ['type' => 'datetime', 'null' => 0],
            'deleted_at'    => ['type' => 'datetime', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('inactive_notify_histories');
    }

    public function down()
    {
        $this->forge->dropTable('inactive_notify_histories');
    }
}