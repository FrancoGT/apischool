<?php

namespace App\Models;

use CodeIgniter\Model;

class AlumnoModel extends Model
{
    protected $table      = 'alumno'; // Nombre de la tabla
    protected $primaryKey = 'idAlumno';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    // Campos que pueden ser insertados o actualizados
    protected $allowedFields = [
        'estadoSiagie', 'codAlumnoCaja', 'nombresAlumno', 'apellidosAlumno', 'sexoAlumno', 'dniAlumno',
        'fechaNacimiento', 'direccionAlumno', 'distritoAlumno', 'IEPProcedencia', 'seguroSalud',
        'fechaIngresoVolta', 'nuevoAlumno', 'numeroEmergencia', 'enfermedades', 'fechaCreacion', 'fechaActualizacion', 
        'usuarioCreacion', 'usuarioActualizacion'
    ];

    protected $useTimestamps = false;  // Si manejas tus propias fechas de creación y actualización
    protected $createdField  = 'fechaCreacion';
    protected $updatedField  = 'fechaActualizacion';
    protected $dateFormat    = 'datetime';
}