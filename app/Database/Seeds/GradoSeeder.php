<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class GradoSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['idNivel' => 1, 'descripcionGrado' => '3 Años', 'fechaCreacion' => '2024-01-31 09:17:44', 'fechaActualizacion' => '2024-01-31 09:17:44', 'usuarioCreacion' => 1, 'usuarioActualizacion' => 1],
            ['idNivel' => 1, 'descripcionGrado' => '4 Años', 'fechaCreacion' => '2024-01-31 09:17:44', 'fechaActualizacion' => '2024-01-31 09:17:44', 'usuarioCreacion' => 1, 'usuarioActualizacion' => 1],
            ['idNivel' => 1, 'descripcionGrado' => '5 Años', 'fechaCreacion' => '2024-01-31 09:17:44', 'fechaActualizacion' => '2024-01-31 09:17:44', 'usuarioCreacion' => 1, 'usuarioActualizacion' => 1],
            ['idNivel' => 2, 'descripcionGrado' => '1er Grado', 'fechaCreacion' => '2024-01-31 09:17:44', 'fechaActualizacion' => '2024-01-31 09:17:44', 'usuarioCreacion' => 1, 'usuarioActualizacion' => 1],
            ['idNivel' => 2, 'descripcionGrado' => '2do Grado', 'fechaCreacion' => '2024-01-31 09:17:44', 'fechaActualizacion' => '2024-01-31 09:17:44', 'usuarioCreacion' => 1, 'usuarioActualizacion' => 1],
            ['idNivel' => 2, 'descripcionGrado' => '3er Grado', 'fechaCreacion' => '2024-01-31 09:17:44', 'fechaActualizacion' => '2024-01-31 09:17:44', 'usuarioCreacion' => 1, 'usuarioActualizacion' => 1],
            ['idNivel' => 2, 'descripcionGrado' => '4to Grado', 'fechaCreacion' => '2024-01-31 09:17:44', 'fechaActualizacion' => '2024-01-31 09:17:44', 'usuarioCreacion' => 1, 'usuarioActualizacion' => 1],
            ['idNivel' => 2, 'descripcionGrado' => '5to Grado', 'fechaCreacion' => '2024-01-31 09:17:44', 'fechaActualizacion' => '2024-01-31 09:17:44', 'usuarioCreacion' => 1, 'usuarioActualizacion' => 1],
            ['idNivel' => 2, 'descripcionGrado' => '6to Grado', 'fechaCreacion' => '2024-01-31 09:17:44', 'fechaActualizacion' => '2024-01-31 09:17:44', 'usuarioCreacion' => 1, 'usuarioActualizacion' => 1],
            ['idNivel' => 3, 'descripcionGrado' => '1er Año', 'fechaCreacion' => '2024-01-31 09:17:44', 'fechaActualizacion' => '2024-01-31 09:17:44', 'usuarioCreacion' => 1, 'usuarioActualizacion' => 1],
            ['idNivel' => 3, 'descripcionGrado' => '2do Año', 'fechaCreacion' => '2024-01-31 09:17:44', 'fechaActualizacion' => '2024-01-31 09:17:44', 'usuarioCreacion' => 1, 'usuarioActualizacion' => 1],
            ['idNivel' => 3, 'descripcionGrado' => '3er Año', 'fechaCreacion' => '2024-01-31 09:17:44', 'fechaActualizacion' => '2024-01-31 09:17:44', 'usuarioCreacion' => 1, 'usuarioActualizacion' => 1],
            ['idNivel' => 3, 'descripcionGrado' => '4to Año', 'fechaCreacion' => '2024-01-31 09:17:44', 'fechaActualizacion' => '2024-01-31 09:17:44', 'usuarioCreacion' => 1, 'usuarioActualizacion' => 1],
            ['idNivel' => 3, 'descripcionGrado' => '5to Año', 'fechaCreacion' => '2024-01-31 09:17:44', 'fechaActualizacion' => '2024-01-31 09:17:44', 'usuarioCreacion' => 1, 'usuarioActualizacion' => 1],
        ];

        $this->db->table('grado')->insertBatch($data);
    }
}