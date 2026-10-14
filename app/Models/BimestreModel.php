<?php

namespace App\Models;

use CodeIgniter\Model;

class BimestreModel extends Model
{
    protected $table      = 'bimestre';
    protected $primaryKey = 'idBimestre';

    protected $allowedFields = [
        'idBimestre',
        'idCursoGrado',
        'descripcionBimestre',
        'estadoBimestre',
        'fechaCreacion',
        'fechaActualizacion',
        'usuarioCreacion',
        'usuarioActualizacion',
    ];

    protected $useTimestamps = false;
}