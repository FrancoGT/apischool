<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCursoTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'idCurso' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'idArea' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'descripcionCurso' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
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
            'estadoCurso' => [
                'type'       => 'INT',
                'constraint' => 1,
                'null'       => true,
                'default'    => null,
            ],
        ]);

        $this->forge->addKey('idCurso', true);
        $this->forge->addForeignKey('idArea', 'area', 'idArea', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('curso');
    }

    public function down()
    {
        $this->forge->dropTable('curso');
    }
}