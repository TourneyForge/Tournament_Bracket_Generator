<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EmailVerifications extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'user_id' => ['type' => 'INT'],
            'new_email' => ['type' => 'VARCHAR', 'constraint' => 255],
            'verification_code' => ['type' => 'VARCHAR', 'constraint' => 6],
            'expires_at' => ['type' => 'DATETIME'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('email_verifications');
    }

    public function down()
    {
        $this->forge->dropTable('email_verifications');
    }
}