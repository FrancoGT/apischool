<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUnidadTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'idUnidad' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'idBimestre' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'descripcionUnidad' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'estadoUnidad' => [
                'type'       => 'BIT',
                'constraint' => 1,
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
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
            'usuarioActualizacion' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
        ]);

        $this->forge->addKey('idUnidad', true);
        $this->forge->addKey('idBimestre');
        $this->forge->addForeignKey('idBimestre', 'bimestre', 'idBimestre', 'CASCADE', 'CASCADE');
        $this->forge->createTable('unidad');
    }

    public function down()
    {
        $this->forge->dropTable('unidad');
    }
}