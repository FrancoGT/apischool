<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAnioEscolarTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'idAnioEscolar'        => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true, 'unsigned' => true],
            'descripcionAnio'      => ['type' => 'VARCHAR', 'constraint' => 25, 'null' => false, 'charset' => 'utf8mb4', 'collate' => 'utf8mb4_unicode_ci'],
            'estadoAnio'           => ['type' => 'INT', 'null' => false],
            'cuotaInicial'         => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => false],
            'matriculaInicial'     => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true, 'default' => null],
            'pensionInicial'       => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true, 'default' => null],
            'matriculaPrimaria'    => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true, 'default' => null],
            'pensionPrimaria'      => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true, 'default' => null],
            'matriculaSecundaria'  => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true, 'default' => null],
            'pensionSecundaria'    => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true, 'default' => null],
            'fechaCreacion'        => ['type' => 'DATETIME', 'null' => false],
            'fechaActualizacion'   => ['type' => 'DATETIME', 'null' => false],
            'usuarioCreacion'      => ['type' => 'INT', 'constraint' => 11, 'null' => false],
            'usuarioActualizacion' => ['type' => 'INT', 'constraint' => 11, 'null' => false]
        ]);

        $this->forge->addPrimaryKey('idAnioEscolar');
        $this->forge->createTable('anio_escolar', true);
    }

    public function down()
    {
        $this->forge->dropTable('anio_escolar');
    }
}