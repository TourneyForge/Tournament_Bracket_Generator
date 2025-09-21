<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedByToBracketsTable extends Migration
{
    public function up()
    {
        $fields = array(
            'deleted_by' => array(
                'type' => 'int',
                'constraint' => 11,
                'after' => 'final_match'
            )
        );
        $this->forge->addColumn('brackets', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('brackets', 'deleted_by');
    }
}