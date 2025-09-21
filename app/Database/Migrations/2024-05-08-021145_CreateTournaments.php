<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTournaments extends Migration
{
    public function up()
    {
        // Attributes for the MySQL InnoDB engine
        $attributes = ($this->db->getPlatform() === 'MySQLi') ? ['ENGINE' => 'InnoDB'] : [];

        // Create Tournaments Table
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => false],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'type'       => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'status'     => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
            'updated_at' => ['type' => 'DATETIME', 'null' => false],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('tournaments', false, $attributes);

        // Create Music Settings Table
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'path'          => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => false],
            'source'        => ['type' => 'VARCHAR', 'constraint' => 1, 'null' => false, 'default' => 'f'],
            'tournament_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'type'          => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'duration'      => ['type' => 'VARCHAR', 'constraint' => 8, 'null' => false, 'default' => 1],
            'start'         => ['type' => 'VARCHAR', 'constraint' => 8, 'null' => false, 'default' => 1],
            'end'           => ['type' => 'VARCHAR', 'constraint' => 8, 'null' => false, 'default' => 1],
            'created_at'    => ['type' => 'DATETIME', 'null' => false],
            'updated_at'    => ['type' => 'DATETIME', 'null' => false],
            'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tournament_id', 'tournaments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('music_settings', false, $attributes);
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();

        $this->forge->dropTable('music_settings', true);
        $this->forge->dropTable('tournaments', true);

        $this->db->enableForeignKeyChecks();
    }
}