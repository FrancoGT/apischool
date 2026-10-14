<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AnioEscolarSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'idAnioEscolar'       => 24,
            'descripcionAnio'     => 'Año 2025',
            'estadoAnio'          => 1,
            'cuotaInicial'        => 150.00,
            'matriculaInicial'    => 280.00,
            'pensionInicial'      => 280.00,
            'matriculaPrimaria'   => 350.00,
            'pensionPrimaria'     => 350.00,
            'matriculaSecundaria' => 350.00,
            'pensionSecundaria'   => 350.00,
            'fechaCreacion'       => '2024-12-12 11:02:57',
            'fechaActualizacion'  => '2024-12-12 11:04:20',
            'usuarioCreacion'     => 1,
            'usuarioActualizacion'=> 1
        ];

        // Insert the data
        $this->db->table('anio_escolar')->insert($data);
    }
}