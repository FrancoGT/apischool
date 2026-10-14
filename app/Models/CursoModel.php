<?php

namespace App\Models;

use CodeIgniter\Model;

class CursoModel extends Model
{
    protected $table = 'curso';
    protected $primaryKey = 'idCurso';

    protected $allowedFields = [
        'idCurso',
        'idArea',
        'descripcionCurso',
        'fechaCreacion',
        'fechaActualizacion',
        'usuarioCreacion',
        'usuarioActualizacion',
        'estadoCurso',
    ];
}