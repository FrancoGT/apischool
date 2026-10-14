<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AllSeeders extends Seeder
{
    public function run()
    {
        // Llamar a todos los seeders
        $this->call('NivelSeeder');
        $this->call('TipoUsuarioSeeder');
        $this->call('UsuarioSeeder');
        $this->call('SuperAdminSeeder');
        $this->call('GradoSeeder');
        $this->call('AnioEscolarSeeder');
        $this->call('PostulanteSeeder');
        $this->call('AnioPostulanteSeeder');
        $this->call('ApoderadoSeeder');
        $this->call('AreaSeeder');
        $this->call('CursoSeeder');
        $this->call('CursoGradoSeeder');
        $this->call('BimestreSeeder');
        $this->call('UnidadSeeder');
        $this->call('CompetenciaSeeder');
        // Agrega aquí otros seeders según vayas creando más tablas
        // $this->call('OtroSeeder');
    }
}