<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterParticipantsTable extends Migration
{
    public function up()
    {
        $attributes = ($this->db->getPlatform() === 'MySQLi') ? ['ENGINE' => 'InnoDB'] : [];

        $fields = [
            'user_id' => ['name' => 'created_by', 'type' => 'INT', 'constraint' => 11],
            'sessionid' => ['name' => 'hash', 'type' => 'varchar', 'constraint' => 255]
        ];
        $this->forge->modifyColumn('Participants', $fields);

        $this->forge->dropColumn('participants', 'tournament_id');
        $this->forge->dropColumn('participants', 'order');

        // Tournament Members Table
        $this->forge->addField([
            'id'            => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'participant_id'=> ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'tournament_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'order'         => ['type' => 'int', 'constraint' => 3, 'null' => true],
            'hash'          => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'created_by'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'created_at'    => ['type' => 'datetime', 'null' => false],
            'updated_at'    => ['type' => 'datetime', 'null' => false],
            'deleted_at'    => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('tournament_id', 'tournaments', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('participant_id', 'participants', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', '', 'CASCADE');
        $this->forge->createTable('tournament_members', false, $attributes);
    }

    public function down()
    {
        $fields = [
            'created_by' => ['name' => 'user_id', 'type' => 'INT', 'constraint' => 11],
            'hash' => ['name' => 'sessionid', 'type' => 'varchar', 'constraint' => 255]
        ];
        $this->forge->modifyColumn('Participants', $fields);

        $this->forge->addColumn('Participants', [
            'tournament_id' => ['type' => 'int', 'constraint' => 11, 'null' => 0],
            'order'         => ['type' => 'int', 'constraint' => 3, 'null' => true]
        ]);
        
        $this->forge->dropTable('tournament_members', true);
    }
}