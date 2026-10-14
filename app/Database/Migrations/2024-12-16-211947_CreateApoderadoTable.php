<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApoderadoTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'idApoderado' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,  // Dejamos 'unsigned' si corresponde, de lo contrario eliminamos
                'auto_increment' => true,
            ],
            'idUsuario' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,  // Es opcional si la tabla no tiene 'unsigned'
                'null'           => true,
                'default'        => null,
            ],
            'nombreApoderado' => [
                'type'           => 'VARCHAR',
                'constraint'     => 150,
                'null'           => false,
                'collation'      => 'utf8mb4_unicode_ci',
            ],
            'apellidoApoderado' => [
                'type'           => 'VARCHAR',
                'constraint'     => 150,
                'null'           => false,
                'collation'      => 'utf8mb4_unicode_ci',
            ],
            'dniApoderado' => [
                'type'           => 'VARCHAR',
                'constraint'     => 255,
                'null'           => true,
                'default'        => null,
                'collation'      => 'utf8mb4_unicode_ci',
            ],
            'convivenciaAlumno' => [
                'type'           => 'VARCHAR',
                'constraint'     => 25,
                'null'           => true,
                'default'        => null,
                'collation'      => 'utf8mb4_unicode_ci',
            ],
            'fechaNacimiento' => [
                'type'           => 'DATE',
                'null'           => true,
                'default'        => null,
            ],
            'tipoApoderado' => [
                'type'           => 'VARCHAR',
                'constraint'     => 25,
                'null'           => false,
                'collation'      => 'utf8mb4_unicode_ci',
            ],
            'correoApoderado' => [
                'type'           => 'VARCHAR',
                'constraint'     => 150,
                'null'           => false,
                'collation'      => 'utf8mb4_unicode_ci',
            ],
            'celularApoderado' => [
                'type'           => 'VARCHAR',
                'constraint'     => 15,
                'null'           => true,
                'default'        => null,
                'collation'      => 'utf8mb4_unicode_ci',
            ],
            'dependenciaApoderado' => [
                'type'           => 'VARCHAR',
                'constraint'     => 50,
                'null'           => true,
                'default'        => null,
                'collation'      => 'utf8mb4_unicode_ci',
            ],
            'centroLaboral' => [
                'type'           => 'VARCHAR',
                'constraint'     => 150,
                'null'           => true,
                'default'        => null,
                'collation'      => 'utf8mb4_unicode_ci',
            ],
            'telefonoTrabajo' => [
                'type'           => 'VARCHAR',
                'constraint'     => 15,
                'null'           => true,
                'default'        => null,
                'collation'      => 'utf8mb4_unicode_ci',
            ],
            'ingresoMensual' => [
                'type'           => 'VARCHAR',
                'constraint'     => 15,
                'null'           => true,
                'default'        => null,
                'collation'      => 'utf8mb4_unicode_ci',
            ],
            'gradoInstruccion' => [
                'type'           => 'VARCHAR',
                'constraint'     => 50,
                'null'           => true,
                'default'        => null,
                'collation'      => 'utf8mb4_unicode_ci',
            ],
            'profesionApoderado' => [
                'type'           => 'VARCHAR',
                'constraint'     => 50,
                'null'           => true,
                'default'        => null,
                'collation'      => 'utf8mb4_unicode_ci',
            ],
            'listaAlumnos' => [
                'type'           => 'VARCHAR',
                'constraint'     => 100,
                'null'           => true,
                'default'        => null,
                'collation'      => 'utf8mb4_unicode_ci',
            ],
            'cuentaCreada' => [
                'type'           => 'INT',
                'constraint'     => 11,
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
                'null'           => false,
            ],
            'usuarioActualizacion' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'null'           => false,
            ]
        ]);
        
        $this->forge->addPrimaryKey('idApoderado');
        $this->forge->addKey('idUsuario');
        $this->forge->addForeignKey('idUsuario', 'usuario', 'idUsuario', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('apoderado', true);
    }

    public function down()
    {
        $this->forge->dropTable('apoderado');
    }
}