<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTipoUsuarioTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'idTipoUsuario' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'descripcionTipoUsuario' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'fechaCreacion' => [
                'type' => 'DATETIME',
            ],
            'fechaActualizacion' => [
                'type' => 'DATETIME',
            ],
            'usuarioCreacion' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'usuarioActualizacion' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
        ]);

        $this->forge->addKey('idTipoUsuario', true);
        $this->forge->createTable('tipo_usuario');
    }

    public function down()
    {
        $this->forge->dropTable('tipo_usuario');
    }
}