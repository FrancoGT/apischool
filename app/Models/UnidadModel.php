<?php

namespace App\Models;

use CodeIgniter\Model;

class UnidadModel extends Model
{
    protected $table      = 'unidad';
    protected $primaryKey = 'idUnidad';

    protected $allowedFields = [
        'idUnidad',
        'idBimestre',
        'descripcionUnidad',
        'estadoUnidad',
        'fechaCreacion',
        'fechaActualizacion',
        'usuarioCreacion',
        'usuarioActualizacion',
    ];

    protected $useTimestamps = false;

    public function getReporteUnidades()
    {
        return $this->db->table('unidad')
            ->select('unidad.idUnidad, 
                      unidad.descripcionUnidad, 
                      bimestre.descripcionBimestre, 
                      cursos.descripcionCurso, 
                      grados.descripcionGrado, 
                      niveles.descripcionNivel')
            ->join('bimestre', 'unidad.idBimestre = bimestre.idBimestre', 'INNER')
            ->join('curso_grado', 'bimestre.idCursoGrado = curso_grado.idCursoGrado', 'INNER')
            ->join('curso AS cursos', 'curso_grado.idCurso = cursos.idCurso', 'INNER')
            ->join('grado AS grados', 'curso_grado.idGrado = grados.idGrado', 'INNER')
            ->join('nivel AS niveles', 'grados.idNivel = niveles.idNivel', 'INNER')
            ->get()->getResultArray();
    }
}