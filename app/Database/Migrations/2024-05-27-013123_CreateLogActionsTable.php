<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLogActionsTable extends Migration
{
    public function up()
    {
        $attributes = ($this->db->getPlatform() === 'MySQLi') ? ['ENGINE' => 'InnoDB'] : [];

        $this->forge->addField([
            'id'             => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'        => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'tournament_id'  => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'action'         => ['type' => 'varchar', 'constraint' => 6],
            'params'         => ['type' => 'varchar', 'constraint' => 128],
            'created_at'     => ['type' => 'datetime', 'null' => false],
            'updated_at'     => ['type' => 'datetime', 'null' => false],
            'deleted_at'     => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tournament_id', 'tournaments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('log_actions', false, $attributes);
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();

        $this->forge->dropTable('log_actions', true);
    }
}