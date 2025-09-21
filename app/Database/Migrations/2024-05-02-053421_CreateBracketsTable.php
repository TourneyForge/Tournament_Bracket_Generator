<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBracketsTable extends Migration
{
    public function up()
    {
        $attributes = ($this->db->getPlatform() === 'MySQLi') ? ['ENGINE' => 'InnoDB'] : [];

        $this->forge->addField([
            'id'             => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tournament_id' => ['type' => 'int', 'constraint' => 11, 'null' => 0],
            'bracketNo'       => ['type' => 'int', 'constraint' => 3, 'null' => 0],
            'bye'         => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0],
            'lastGames'         => ['type' => 'varchar', 'constraint' => 16, 'null' => true],
            'nextGame'         => ['type' => 'int', 'constraint' => 1, 'null' => true, 'default' => 0],
            'roundNo'         => ['type' => 'int', 'constraint' => 3, 'null' => 0],
            'teamnames'         => ['type' => 'varchar', 'constraint' => 128, 'null' => true, 'default' => 0],
            'winner'         => ['type' => 'int', 'constraint' => 11, 'null' => true],
            'user_id'         => ['type' => 'int', 'constraint' => 11, 'null' => 0],
            'final_match'         => ['type' => 'tinyint', 'constraint' => 1, 'null' => true, 'default' => 0],
            'created_at'     => ['type' => 'datetime', 'null' => 0],
            'updated_at'     => ['type' => 'datetime', 'null' => 0],
            'deleted_at'     => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('brackets', false, $attributes);
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();

        $this->forge->dropTable('brackets', true);
    }
}