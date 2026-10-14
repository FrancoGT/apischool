<?php

namespace App\Models;

use CodeIgniter\Model;

class CursoGradoModel extends Model
{
    protected $table = 'curso_grado';
    protected $primaryKey = 'idCursoGrado';
    protected $allowedFields = [
        'idCursoGrado',
        'idCursoGrado',
        'idCurso',
        'idGrado',
        'fechaCreacion',
        'fechaActualizacion',
        'usuarioCreacion',
        'usuarioActualizacion',
    ];
    protected $useTimestamps = false;
}