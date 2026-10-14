<?php

namespace App\Models;

use CodeIgniter\Model;

class AnioPostulanteModel extends Model
{
    protected $table = 'anio_postulante';
    protected $primaryKey = 'idAnioPostulante';
    protected $allowedFields = [
        'idAnioPostulante',
        'idAnioEscolar', 
        'idPostulante', 
        'fechaCreacion', 
        'fechaActualizacion', 
        'usuarioCreacion', 
        'usuarioActualizacion'
    ];

    protected $useTimestamps = false;  // Si no deseas usar el timestamp de 'created_at' y 'updated_at'

    // Relaciones con otras tablas
    public function anioEscolar()
    {
        return $this->belongsTo(AnioEscolarModel::class, 'idAnioEscolar');
    }

    public function postulante()
    {
        return $this->belongsTo(PostulanteModel::class, 'idPostulante');
    }
}