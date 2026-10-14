<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'idArea' => '37',
                'descripcionArea'    => 'Cursos básicos',
                'fechaCreacion'      => date('Y-m-d H:i:s'),
                'fechaActualizacion' => date('Y-m-d H:i:s'),
                'usuarioCreacion'    => 1,
                'usuarioActualizacion' => 1,
            ],
            [
                'idArea' => '38',
                'descripcionArea'    => 'Cursos de Primaria',
                'fechaCreacion'      => date('Y-m-d H:i:s'),
                'fechaActualizacion' => date('Y-m-d H:i:s'),
                'usuarioCreacion'    => 1,
                'usuarioActualizacion' => 1,
            ],
            [
                'idArea' => '39',
                'descripcionArea'    => 'Cursos de Secundaria',
                'fechaCreacion'      => date('Y-m-d H:i:s'),
                'fechaActualizacion' => date('Y-m-d H:i:s'),
                'usuarioCreacion'    => 1,
                'usuarioActualizacion' => 1,
            ],
        ];

        $this->db->table('area')->insertBatch($data);
    }
}