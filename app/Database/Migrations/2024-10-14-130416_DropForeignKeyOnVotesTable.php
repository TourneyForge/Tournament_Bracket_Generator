<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropForeignKeyOnVotesTable extends Migration
{
    public function up()
    {
        $this->forge->dropForeignKey('votes', 'votes_bracket_id_foreign');
    }

    public function down()
    {
        //
    }
}