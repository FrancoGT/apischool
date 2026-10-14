<?php

namespace App\Models;

use CodeIgniter\Model;

class AreaModel extends Model
{
    protected $table = 'area';
    protected $primaryKey = 'idArea';
    protected $allowedFields = [
        'idArea',
        'descripcionArea',
        'fechaCreacion',
        'fechaActualizacion',
        'usuarioCreacion',
        'usuarioActualizacion'
    ];
    protected $useTimestamps = false; // Manejo manual de fechas
}