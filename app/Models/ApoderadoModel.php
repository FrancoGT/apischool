<?php

namespace App\Models;

use CodeIgniter\Model;

class ApoderadoModel extends Model
{
    protected $table = 'apoderado';
    protected $primaryKey = 'idApoderado';
    
    protected $allowedFields = [
        'idApoderado', 'idUsuario', 'nombreApoderado', 'apellidoApoderado', 'dniApoderado', 
        'convivenciaAlumno', 'fechaNacimiento', 'tipoApoderado', 'correoApoderado',
        'celularApoderado', 'dependenciaApoderado', 'centroLaboral', 'telefonoTrabajo', 
        'ingresoMensual', 'gradoInstruccion', 'profesionApoderado', 'listaAlumnos',
        'cuentaCreada', 'fechaCreacion', 'fechaActualizacion', 'usuarioCreacion', 'usuarioActualizacion'
    ];
    
    protected $useTimestamps = true;
    protected $createdField  = 'fechaCreacion';
    protected $updatedField  = 'fechaActualizacion';
}