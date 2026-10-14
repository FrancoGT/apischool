<?php

namespace App\Models;

use CodeIgniter\Model;

class TipoUsuarioModel extends Model
{
    protected $table = 'tipo_usuario';
    protected $primaryKey = 'idTipoUsuario';
    protected $allowedFields = [
        'descripcionTipoUsuario',
        'fechaCreacion',
        'fechaActualizacion',
        'usuarioCreacion',
        'usuarioActualizacion'
    ];
    protected $useTimestamps = false;
}