<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddParticipantImageUpdateEnabledToTournaments extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournaments', [
            'pt_image_update_enabled' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', 'pt_image_update_enabled');
    }
}