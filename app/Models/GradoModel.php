<?php

namespace App\Models;

use CodeIgniter\Model;

class GradoModel extends Model
{
    protected $table      = 'grado'; // Nombre de la tabla
    protected $primaryKey = 'idGrado';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    // Campos que pueden ser insertados o actualizados
    protected $allowedFields = ['idNivel', 'descripcionGrado', 'fechaCreacion', 'fechaActualizacion', 'usuarioCreacion', 'usuarioActualizacion'];

    protected $useTimestamps = false;  // Si manejas tus propias fechas de creación y actualización
    protected $createdField  = 'fechaCreacion';
    protected $updatedField  = 'fechaActualizacion';
    protected $dateFormat    = 'datetime';
}