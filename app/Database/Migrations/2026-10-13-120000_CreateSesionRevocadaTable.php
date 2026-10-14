<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Sesiones JWT cerradas con POST /api/auth/logout.
 * Cada fila se puede borrar después de fechaExpiracion (fin de la sesión).
 */
class CreateSesionRevocadaTable extends Migration
{
    public function up()
    {
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

    public function down()
    {
        $this->forge->dropTable('sesion_revocada');
    }
}
