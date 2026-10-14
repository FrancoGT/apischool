<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAlumnoTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'idAlumno' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'estadoSiagie' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'null'           => true,
                'default'        => null,
            ],
            'codAlumnoCaja' => [
                'type'           => 'VARCHAR',
                'constraint'     => '25',
                'null'           => true,
                'default'        => null,
            ],
            'nombresAlumno' => [
                'type'           => 'VARCHAR',
                'constraint'     => '150',
                'null'           => false,
            ],
            'apellidosAlumno' => [
                'type'           => 'VARCHAR',
                'constraint'     => '150',
                'null'           => false,
            ],
            'sexoAlumno' => [
                'type'           => 'VARCHAR',
                'constraint'     => '25',
                'null'           => true,
                'default'        => null,
            ],
            'dniAlumno' => [
                'type'           => 'VARCHAR',
                'constraint'     => '25',
                'null'           => false,
            ],
            'fechaNacimiento' => [
                'type'           => 'DATE',
                'null'           => false,
            ],
            'direccionAlumno' => [
                'type'           => 'VARCHAR',
                'constraint'     => '200',
                'null'           => true,
                'default'        => null,
            ],
            'distritoAlumno' => [
                'type'           => 'VARCHAR',
                'constraint'     => '100',
                'null'           => true,
                'default'        => null,
            ],
            'IEPProcedencia' => [
                'type'           => 'VARCHAR',
                'constraint'     => '200',
                'null'           => true,
                'default'        => null,
            ],
            'seguroSalud' => [
                'type'           => 'VARCHAR',
                'constraint'     => '150',
                'null'           => true,
                'default'        => null,
            ],
            'fechaIngresoVolta' => [
                'type'           => 'DATE',
                'null'           => true,
                'default'        => null,
            ],
            'nuevoAlumno' => [
                'type'           => 'BIT',
                'constraint'     => 1,
                'null'           => true,
                'default'        => null,
            ],
            'numeroEmergencia' => [
                'type'           => 'VARCHAR',
                'constraint'     => '25',
                'null'           => true,
                'default'        => null,
            ],
            'enfermedades' => [
                'type'           => 'VARCHAR',
                'constraint'     => '200',
                'null'           => true,
                'default'        => null,
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
        $this->forge->addKey('idAlumno', true);  // Clave primaria
        $this->forge->createTable('alumno');
    }

    public function down()
    {
        $this->forge->dropTable('alumno');
    }
}