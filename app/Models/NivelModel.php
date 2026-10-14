<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * @OA\Schema(
 *     schema="Nivel",
 *     type="object",
 *     properties={
 *         @OA\Property(property="id", type="integer", description="ID del nivel"),
 *         @OA\Property(property="nombre", type="string", description="Nombre del nivel"),
 *         @OA\Property(property="descripcion", type="string", description="Descripción del nivel")
 *     }
 * )
 */

class NivelModel extends Model
{
    protected $table      = 'nivel';
    protected $primaryKey = 'idNivel';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'descripcionNivel',
        'fechaCreacion',
        'fechaActualizacion',
        'usuarioCreacion',
        'usuarioActualizacion'
    ];

    protected $useTimestamps = false;

    // Configuración de campos de fechas
    protected $createdField  = 'fechaCreacion';
    protected $updatedField  = 'fechaActualizacion';
    protected $deletedField  = '';

    // Definir validaciones
    protected $validationRules = [
        'descripcionNivel'     => 'required|min_length[3]|max_length[100]',
        'usuarioCreacion'      => 'required|integer',
        'usuarioActualizacion' => 'required|integer',
    ];
}