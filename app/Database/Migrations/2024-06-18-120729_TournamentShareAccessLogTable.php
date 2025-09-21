<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TournamentShareAccessLogTable extends Migration
{
    public function up()
    {
        $attributes = ($this->db->getPlatform() === 'MySQLi') ? ['ENGINE' => 'InnoDB'] : [];

        $this->forge->addField([
            'id'             => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'        => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'share_id'       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'created_at'     => ['type' => 'datetime', 'null' => false],
            'updated_at'     => ['type' => 'datetime', 'null' => false],
            'deleted_at'     => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('share_id', 'share_settings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tournament_share_access_logs', false, $attributes);
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();

        $this->forge->dropTable('tournament_share_access_logs', true);
    }
}