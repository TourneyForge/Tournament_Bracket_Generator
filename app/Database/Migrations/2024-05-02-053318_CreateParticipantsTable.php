<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateParticipantsTable extends Migration
{
    public function up()
    {
        $attributes = ($this->db->getPlatform() === 'MySQLi') ? ['ENGINE' => 'InnoDB'] : [];

        $this->forge->addField([
            'id'             => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'varchar', 'constraint' => 30, 'null' => 0],
            'user_id'         => ['type' => 'int', 'constraint' => 11, 'null' => 0],
            'tournament_id'         => ['type' => 'int', 'constraint' => 11, 'null' => 0],
            'order'         => ['type' => 'int', 'constraint' => 3, 'null' => true],
            'active'         => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            'created_at'     => ['type' => 'datetime', 'null' => 0],
            'updated_at'     => ['type' => 'datetime', 'null' => 0],
            'deleted_at'     => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('participants', false, $attributes);
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();

        $this->forge->dropTable('participants', true);
    }
}