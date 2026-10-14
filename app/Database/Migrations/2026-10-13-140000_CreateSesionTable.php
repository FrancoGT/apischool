<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Sesiones JWT: una fila por login, que se conserva en cada refresh.
 * Un token solo es válido si su sesión ("sid") existe aquí y no fue cerrada.
 *
 * Reemplaza a la tabla sesion_revocada y a la columna usuario.cierreSesiones:
 * cerrar una sesión o todas consiste en marcar fechaCierre en sus filas.
 */
class CreateSesionTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'idSesion'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'sid'               => ['type' => 'VARCHAR', 'constraint' => 64],
            'idUsuario'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'ipInicio'          => ['type' => 'VARCHAR', 'constraint' => 45],
            'ultimaIp'          => ['type' => 'VARCHAR', 'constraint' => 45],
            'dispositivo'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'fechaInicio'       => ['type' => 'DATETIME'],
            'ultimaActividad'   => ['type' => 'DATETIME'],
            'ultimoTokenExpira' => ['type' => 'DATETIME'],
            'fechaExpiracion'   => ['type' => 'DATETIME'],
            'fechaCierre'       => ['type' => 'DATETIME', 'null' => true],
            'motivoCierre'      => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
        ]);

        $this->forge->addPrimaryKey('idSesion');
        $this->forge->addUniqueKey('sid');
        $this->forge->addKey('idUsuario');
        $this->forge->addKey('fechaExpiracion');
        $this->forge->addForeignKey('idUsuario', 'usuario', 'idUsuario', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sesion');

        $this->forge->dropTable('sesion_revocada', true);
        $this->forge->dropColumn('usuario', 'cierreSesiones');
    }

    public function down()
    {
        $this->forge->dropTable('sesion', true);

        $this->forge->addColumn('usuario', [
            'cierreSesiones' => ['type' => 'DATETIME', 'null' => true, 'after' => 'ultimaConexion'],
        ]);

        $this->forge->addField([
            'sid'             => ['type' => 'VARCHAR', 'constraint' => 64],
            'idUsuario'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'fechaExpiracion' => ['type' => 'DATETIME'],
            'fechaCreacion'   => ['type' => 'DATETIME'],
        ]);
        $this->forge->addPrimaryKey('sid');
        $this->forge->addKey('fechaExpiracion');
        $this->forge->addForeignKey('idUsuario', 'usuario', 'idUsuario', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sesion_revocada');
    }
}
