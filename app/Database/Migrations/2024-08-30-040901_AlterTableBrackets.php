<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterTableBrackets extends Migration
{
    public function up()
    {
        //
        $this->forge->modifyColumn('brackets', [
            'teamnames' => [
                'name' => 'teamnames',
                'type' => 'text',
                'null' => true
            ]
        ]);
    }

    public function down()
    {
        //
    }
}
