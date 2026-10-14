<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Fecha del último "cerrar todas las sesiones" (POST /api/auth/logout-all).
 * Los tokens con sesión iniciada en o antes de esa fecha dejan de ser válidos.
 */
class AddCierreSesionesToUsuario extends Migration
{
    public function up()
    {
        $this->forge->addColumn('usuario', [
            'cierreSesiones' => ['type' => 'DATETIME', 'null' => true, 'after' => 'ultimaConexion'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('usuario', 'cierreSesiones');
    }
}
