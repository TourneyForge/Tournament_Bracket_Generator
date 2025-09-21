<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGroupToParticipantsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('participants', [
            'is_group' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0],
            'group_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
        ]);

        $this->forge->addForeignKey('group_id', 'groups', 'id', '', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropColumn('participants', 'is_group');
        $this->forge->dropColumn('participants', 'group_id');
    }
}