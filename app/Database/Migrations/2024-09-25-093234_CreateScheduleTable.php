<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateScheduleTable extends Migration
{
    public function up()
    {
        $attributes = ($this->db->getPlatform() === 'MySQLi') ? ['ENGINE' => 'InnoDB'] : [];

        $this->forge->addField([
            'id'            => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'schedule_name' => ['type' => 'varchar', 'constraint' => 16],
            'tournament_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'round_no'      => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'result'        => ['type' => 'boolean', 'default' => false],
            'schedule_time' => ['type' => 'datetime', 'null' => false],
            'created_at'    => ['type' => 'datetime', 'null' => false],
            'updated_at'    => ['type' => 'datetime', 'null' => false],
            'deleted_at'    => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('tournament_id', 'tournaments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('schedules', false, $attributes);
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();

        $this->forge->dropTable('schedules', true);
    }
}