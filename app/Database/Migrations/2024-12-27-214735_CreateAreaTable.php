<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAreaTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'idArea' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'descripcionArea' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'collation'  => 'utf8mb4_unicode_ci',
                'null'       => false,
            ],
            'fechaCreacion' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'fechaActualizacion' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'usuarioCreacion' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'usuarioActualizacion' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
        ]);

        $this->forge->addKey('idArea', true);
        $this->forge->createTable('area');
    }

    public function down()
    {
        $this->forge->dropTable('area');
    }
}