<?php

namespace App\Models;

use CodeIgniter\Model;

class PostulanteModel extends Model
{
    protected $table      = 'postulante';
    protected $primaryKey = 'idPostulante';
    
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'idPostulante',
        'nombrePostulante',
        'apellidoPostulante',
        'sexoPostulante',
        'dniPostulante',
        'fechaNacimiento',
        'lugarNacimiento',
        'gradoPostulacion',
        'domicilioPostulante',
        'colegioProcedencia',
        'dificultadPostulante',
        'dificultadObservacion',
        'tipoAtencionPostulante',
        'tratamientoPostulante',
        'fechaPostulacion',
        'listaApoderados',
        'fichaPostulante',
        'fechaFichaPost',
        'estadoFichaPostulante',
        'fechaEntrevista',
        'estadoEntrevista',
        'informePsicologico',
        'fechaInformePsicologico',
        'estadoInformePsicologico',
        'constanciaAdeudo',
        'fechaConstanciaAdeudo',
        'cartaAdmision',
        'fechaCartaAdmision',
        'pagoMatricula',
        'fechaPagoMatricula',
        'pagoCuotaIngreso',
        'fechaCuotaIngreso',
        'contrato',
        'fechaContrato',
        'documentoTraslado',
        'fechaDocumentoTraslado',
        'constanciaVacante',
        'fechaConstanciaVacante',
        'estadoPostulante',
        'fechaCreacion',
        'fechaActualizacion',
        'usuarioCreacion',
        'usuarioActualizacion'
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'fechaCreacion';
    protected $updatedField  = 'fechaActualizacion';
}