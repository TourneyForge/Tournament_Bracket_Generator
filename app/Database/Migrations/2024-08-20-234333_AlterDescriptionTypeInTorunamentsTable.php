<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterDescriptionTypeInTorunamentsTable extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('tournaments', [
            'description' => [
                'name' => 'description',
                'type' => 'longtext',
                'null' => true
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', 'description');
    }
}
