<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompetenciaTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'idCompetencia' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'idUnidad' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'descripcionCompetencia' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'capacidadesCompetencia' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'estandarCompetencia' => [
                'type'       => 'TEXT',
                'null'       => true,
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

        $this->forge->addKey('idCompetencia', true);
        $this->forge->addKey('idUnidad');
        $this->forge->addForeignKey('idUnidad', 'unidad', 'idUnidad', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('competencias');
    }

    public function down()
    {
        $this->forge->dropTable('competencias');
    }
}