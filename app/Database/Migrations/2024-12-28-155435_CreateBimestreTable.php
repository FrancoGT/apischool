<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBimestreTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'idBimestre' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'idCursoGrado' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'descripcionBimestre' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => false,
            ],
            'estadoBimestre' => [
                'type' => 'BIT',
                'constraint' => 1,
                'null' => false,
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

        $this->forge->addKey('idBimestre', true);
        $this->forge->addForeignKey('idCursoGrado', 'curso_grado', 'idCursoGrado', 'CASCADE', 'CASCADE');
        $this->forge->createTable('bimestre');
    }

    public function down()
    {
        $this->forge->dropTable('bimestre');
    }
}