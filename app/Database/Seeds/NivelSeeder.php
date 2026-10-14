<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class NivelSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'descripcionNivel'   => 'Inicial',
                'fechaCreacion'      => '2024-01-31 09:14:10',
                'fechaActualizacion' => '2024-01-31 09:14:10',
                'usuarioCreacion'    => 1,
                'usuarioActualizacion'=> 1
            ],
            [
                'descripcionNivel'   => 'Primaria',
                'fechaCreacion'      => '2024-01-31 09:14:10',
                'fechaActualizacion' => '2024-01-31 09:14:10',
                'usuarioCreacion'    => 1,
                'usuarioActualizacion'=> 1
            ],
            [
                'descripcionNivel'   => 'Secundaria',
                'fechaCreacion'      => '2024-01-31 09:14:10',
                'fechaActualizacion' => '2024-01-31 09:14:10',
                'usuarioCreacion'    => 1,
                'usuarioActualizacion'=> 1
            ]
        ];

        $this->db->table('nivel')->insertBatch($data);
    }
}
