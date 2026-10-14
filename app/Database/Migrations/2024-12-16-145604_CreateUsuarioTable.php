<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsuarioTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'idUsuario'          => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true, 'unsigned' => true],
            'idTipoUsuario'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'correoUsuario'      => ['type' => 'VARCHAR', 'constraint' => '150'],
            'password'           => ['type' => 'VARCHAR', 'constraint' => '200'],
            'nombreUsuario'      => ['type' => 'VARCHAR', 'constraint' => '150'],
            'apellidoUsuario'    => ['type' => 'VARCHAR', 'constraint' => '150'],
            'dniUsuario'         => ['type' => 'VARCHAR', 'constraint' => '20'],
            'estadoUsuario'      => ['type' => 'INT', 'constraint' => 11],
            'ultimaConexion'     => ['type' => 'DATETIME'],
            'fechaCreacion'      => ['type' => 'DATETIME'],
            'fechaActualizacion' => ['type' => 'DATETIME'],
            'usuarioCreacion'    => ['type' => 'INT', 'constraint' => 11],
            'usuarioActualizacion'=> ['type' => 'INT', 'constraint' => 11],
        ]);
        
        $this->forge->addPrimaryKey('idUsuario');
        
        // Aseguramos que la referencia a la clave foránea esté correcta
        $this->forge->addForeignKey('idTipoUsuario', 'tipo_usuario', 'idTipoUsuario', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('usuario');
    }

    public function down()
    {
        $this->forge->dropTable('usuario');
    }
}