<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGradoTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'idGrado' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'idNivel' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'descripcionGrado' => [
                'type'           => 'VARCHAR',
                'constraint'     => '150',
                'null'           => false,
            ],
            'fechaCreacion' => [
                'type'           => 'DATETIME',
                'null'           => false,
            ],
            'fechaActualizacion' => [
                'type'           => 'DATETIME',
                'null'           => false,
            ],
            'usuarioCreacion' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'null'           => false,
            ],
            'usuarioActualizacion' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'null'           => false,
            ],
        ]);
        $this->forge->addKey('idGrado', true);  // Clave primaria
        $this->forge->addKey('idNivel');        // Índice para la clave foránea
        $this->forge->addForeignKey('idNivel', 'nivel', 'idNivel', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('grado');
    }

    public function down()
    {
        $this->forge->dropTable('grado');
    }
}