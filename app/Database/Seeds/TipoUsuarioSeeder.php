<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TipoUsuarioSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'descripcionTipoUsuario' => 'Administrador',
                'fechaCreacion'          => '2024-01-29 12:50:58',
                'fechaActualizacion'     => '2024-01-29 12:50:58',
                'usuarioCreacion'        => 1,
                'usuarioActualizacion'   => 1,
            ],
            [
                'descripcionTipoUsuario' => 'Docente',
                'fechaCreacion'          => '2024-01-29 12:50:58',
                'fechaActualizacion'     => '2024-01-29 12:50:58',
                'usuarioCreacion'        => 1,
                'usuarioActualizacion'   => 1,
            ],
            [
                'descripcionTipoUsuario' => 'Administrativo',
                'fechaCreacion'          => '2024-01-29 12:50:58',
                'fechaActualizacion'     => '2024-01-29 12:50:58',
                'usuarioCreacion'        => 1,
                'usuarioActualizacion'   => 1,
            ],
            [
                'descripcionTipoUsuario' => 'Apoderado',
                'fechaCreacion'          => '2024-01-29 12:50:58',
                'fechaActualizacion'     => '2024-01-29 12:50:58',
                'usuarioCreacion'        => 1,
                'usuarioActualizacion'   => 1,
            ],
            [
                'descripcionTipoUsuario' => 'Dirección',
                'fechaCreacion'          => '2024-01-29 12:50:58',
                'fechaActualizacion'     => '2024-01-29 12:50:58',
                'usuarioCreacion'        => 1,
                'usuarioActualizacion'   => 1,
            ],
        ];

        $this->db->table('tipo_usuario')->insertBatch($data);
    }
}