<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAllowHostOverrideColumnInTournamentsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tournaments', [
            'allow_host_override' => [
                'type'       => 'boolean',
                'default' => 0
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tournaments', 'allow_host_override');
    }
}