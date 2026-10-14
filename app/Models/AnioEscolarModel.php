<?php

namespace App\Models;

use CodeIgniter\Model;

class AnioEscolarModel extends Model
{
    protected $table      = 'anio_escolar';
    protected $primaryKey = 'idAnioEscolar';
    
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'descripcionAnio',
        'estadoAnio',
        'cuotaInicial',
        'matriculaInicial',
        'pensionInicial',
        'matriculaPrimaria',
        'pensionPrimaria',
        'matriculaSecundaria',
        'pensionSecundaria',
        'fechaCreacion',
        'fechaActualizacion',
        'usuarioCreacion',
        'usuarioActualizacion'
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'fechaCreacion';
    protected $updatedField  = 'fechaActualizacion';
}