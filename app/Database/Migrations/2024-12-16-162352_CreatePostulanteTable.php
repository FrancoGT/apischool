<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePostulanteTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'idPostulante'           => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true, 'unsigned' => true],
            'nombrePostulante'       => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => false, 'charset' => 'utf8mb4', 'collate' => 'utf8mb4_unicode_ci'],
            'apellidoPostulante'     => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => false, 'charset' => 'utf8mb4', 'collate' => 'utf8mb4_unicode_ci'],
            'sexoPostulante'         => ['type' => 'VARCHAR', 'constraint' => 25, 'null' => true, 'default' => null],
            'dniPostulante'          => ['type' => 'VARCHAR', 'constraint' => 15, 'null' => true, 'default' => null],
            'fechaNacimiento'        => ['type' => 'DATE', 'null' => true, 'default' => null],
            'lugarNacimiento'        => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true, 'default' => null],
            'gradoPostulacion'       => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => false],
            'domicilioPostulante'    => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true, 'default' => null],
            'colegioProcedencia'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'default' => null],
            'dificultadPostulante'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'default' => null],
            'dificultadObservacion'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null],
            'tipoAtencionPostulante' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null],
            'tratamientoPostulante'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null],
            'fechaPostulacion'       => ['type' => 'DATE', 'null' => false],
            'listaApoderados'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'fichaPostulante'        => ['type' => 'VARCHAR', 'constraint' => 300, 'null' => true, 'default' => null],
            'fechaFichaPost'         => ['type' => 'DATE', 'null' => true, 'default' => null],
            'estadoFichaPostulante'  => ['type' => 'INT', 'null' => true, 'default' => null],
            'fechaEntrevista'        => ['type' => 'DATE', 'null' => true, 'default' => null],
            'estadoEntrevista'       => ['type' => 'INT', 'null' => true, 'default' => null],
            'informePsicologico'     => ['type' => 'VARCHAR', 'constraint' => 300, 'null' => true, 'default' => null],
            'fechaInformePsicologico'=> ['type' => 'DATE', 'null' => true, 'default' => null],
            'estadoInformePsicologico'=> ['type' => 'INT', 'null' => true, 'default' => null],
            'constanciaAdeudo'       => ['type' => 'BIT', 'null' => true, 'default' => null],
            'fechaConstanciaAdeudo'  => ['type' => 'DATE', 'null' => true, 'default' => null],
            'cartaAdmision'          => ['type' => 'BIT', 'null' => true, 'default' => null],
            'fechaCartaAdmision'     => ['type' => 'DATE', 'null' => true, 'default' => null],
            'pagoMatricula'          => ['type' => 'INT', 'null' => true, 'default' => null],
            'fechaPagoMatricula'     => ['type' => 'DATE', 'null' => true, 'default' => null],
            'pagoCuotaIngreso'       => ['type' => 'INT', 'null' => true, 'default' => null],
            'fechaCuotaIngreso'      => ['type' => 'DATE', 'null' => true, 'default' => null],
            'contrato'               => ['type' => 'BIT', 'null' => true, 'default' => null],
            'fechaContrato'          => ['type' => 'DATE', 'null' => true, 'default' => null],
            'documentoTraslado'      => ['type' => 'BIT', 'null' => true, 'default' => null],
            'fechaDocumentoTraslado' => ['type' => 'DATE', 'null' => true, 'default' => null],
            'constanciaVacante'      => ['type' => 'BIT', 'null' => true, 'default' => null],
            'fechaConstanciaVacante' => ['type' => 'DATE', 'null' => true, 'default' => null],
            'estadoPostulante'       => ['type' => 'INT', 'null' => false],
            'fechaCreacion'          => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'fechaActualizacion'     => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'usuarioCreacion'        => ['type' => 'INT', 'constraint' => 11, 'null' => false],
            'usuarioActualizacion'   => ['type' => 'INT', 'constraint' => 11, 'null' => false]
        ]);

        $this->forge->addPrimaryKey('idPostulante');
        $this->forge->createTable('postulante', true);
    }

    public function down()
    {
        $this->forge->dropTable('postulante');
    }
}