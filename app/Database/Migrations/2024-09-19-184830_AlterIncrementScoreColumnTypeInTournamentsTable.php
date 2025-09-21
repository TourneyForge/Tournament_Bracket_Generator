<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterIncrementScoreColumnTypeInTournamentsTable extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('tournaments', [
            'increment_score' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2', // Define the precision (10 digits, 2 decimal places)
                'null'       => true,   // Optional: allow NULL values
            ],
        ]);
    }

    public function down()
    {
         $this->forge->modifyColumn('tournaments', [
            'increment_score' => [
                'type' => 'INT',  // Or the original type of your column
                'null' => true,
            ],
        ]);
    }
}