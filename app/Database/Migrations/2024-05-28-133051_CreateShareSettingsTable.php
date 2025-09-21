<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShareSettingsTable extends Migration
{
    public function up()
    {
        $attributes = ($this->db->getPlatform() === 'MySQLi') ? ['ENGINE' => 'InnoDB'] : [];

        $this->forge->addField([
            'id'             => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'        => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'tournament_id'  => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'target'         => ['type' => 'varchar', 'constraint' => 1, 'null' => 0],
            'users'          => ['type' => 'text', 'null' => true, 'default' => null],
            'permission'     => ['type' => 'varchar', 'constraint' => 1, 'null' => 0, 'default' => 'v'],
            'token'          => ['type' => 'varchar', 'constraint' => 64, 'null' => 0],
            'created_at'     => ['type' => 'datetime', 'null' => false],
            'updated_at'     => ['type' => 'datetime', 'null' => false],
            'deleted_at'     => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tournament_id', 'tournaments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('share_settings', false, $attributes);
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();

        $this->forge->dropTable('share_settings', true);
    }
}