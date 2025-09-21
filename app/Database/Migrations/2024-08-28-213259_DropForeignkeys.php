<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropForeignkeys extends Migration
{
    public function up()
    {   
        $this->forge->dropForeignKey('share_settings', 'share_settings_user_id_foreign');
    }

    public function down()
    {
        //
    }
}