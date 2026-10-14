<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNivelTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'idNivel'            => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'auto_increment' => true],
            'descripcionNivel'   => ['type' => 'VARCHAR', 'constraint' => '100', 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci'],
            'fechaCreacion'      => ['type' => 'DATETIME'],
            'fechaActualizacion' => ['type' => 'DATETIME'],
            'usuarioCreacion'    => ['type' => 'INT', 'constraint' => 11],
            'usuarioActualizacion'=> ['type' => 'INT', 'constraint' => 11],
        ]);
        $this->forge->addKey('idNivel', true);
        $this->forge->createTable('nivel');
    }

    public function down()
    {
        $this->forge->dropTable('nivel');
    }
}