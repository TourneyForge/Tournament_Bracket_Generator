<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddURLInMusicSettingsTable extends Migration
{
    public function up()
    {
        $fields = array(
            'url' => array(
                'type' => 'varchar',
                'constraint' => 128,
                'after' => 'path'
            )
        );
        $this->forge->addColumn('music_settings', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('music_settings', 'url');
    }
}