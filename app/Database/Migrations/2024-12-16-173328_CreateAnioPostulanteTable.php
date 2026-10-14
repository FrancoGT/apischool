<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAnioPostulanteTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'idAnioPostulante'    => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true, 'unsigned' => true],
            'idAnioEscolar'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'idPostulante'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'fechaCreacion'       => ['type' => 'DATETIME', 'null' => false],
            'fechaActualizacion'  => ['type' => 'DATETIME', 'null' => false],
            'usuarioCreacion'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'usuarioActualizacion'=> ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        ]);

        $this->forge->addPrimaryKey('idAnioPostulante');

        // Crear la tabla sin las claves foráneas primero.
        $this->forge->createTable('anio_postulante', true);

        // Añadir las claves foráneas después de la creación de la tabla
        // Esto evita posibles problemas si hay datos en la tabla referenciada.
        $this->forge->addForeignKey('idAnioEscolar', 'anio_escolar', 'idAnioEscolar', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('idPostulante', 'postulante', 'idPostulante', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        // Eliminar las claves foráneas antes de eliminar la tabla
        $this->forge->dropForeignKey('anio_postulante', 'anio_postulante_idAnioEscolar_foreign');
        $this->forge->dropForeignKey('anio_postulante', 'anio_postulante_idPostulante_foreign');

        // Eliminar la tabla
        $this->forge->dropTable('anio_postulante');
    }
}