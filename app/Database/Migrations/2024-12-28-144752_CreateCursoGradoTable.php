<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCursoGradoTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'idCursoGrado' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'idCurso' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'idGrado' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
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
                'unsigned'   => true,
                'null'       => false,
            ],
            'usuarioActualizacion' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);

        // Clave primaria
        $this->forge->addKey('idCursoGrado', true);

        // Claves foráneas
        $this->forge->addForeignKey('idCurso', 'curso', 'idCurso', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('idGrado', 'grado', 'idGrado', 'RESTRICT', 'RESTRICT');

        $this->forge->createTable('curso_grado');
    }

    public function down()
    {
        $this->forge->dropTable('curso_grado');
    }
}