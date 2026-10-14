<?php

namespace App\Models;

use CodeIgniter\Model;

class CompetenciaModel extends Model
{
    protected $table      = 'competencias';
    protected $primaryKey = 'idCompetencia';

    protected $allowedFields = [
        'idCompetencia',
        'idUnidad',
        'descripcionCompetencia',
        'capacidadesCompetencia',
        'estandarCompetencia',
        'fechaCreacion',
        'fechaActualizacion',
        'usuarioCreacion',
        'usuarioActualizacion',
    ];

    protected $useTimestamps = false;

    public function getCompetenciasWithJoin()
    {
        return $this->db->table('competencias')
            ->select('competencias.idCompetencia,
                 competencias.idUnidad,
                 competencias.descripcionCompetencia, 
                 unidades.descripcionUnidad, 
                 bimestres.descripcionBimestre, 
                 cursos.descripcionCurso, 
                 grados.descripcionGrado, 
                 niveles.descripcionNivel, 
                 areas.descripcionArea')
            ->join('unidad AS unidades', 'competencias.idUnidad = unidades.idUnidad', 'INNER')
            ->join('bimestre AS bimestres', 'unidades.idBimestre = bimestres.idBimestre', 'INNER')
            ->join('curso_grado AS curso_grado', 'bimestres.idCursoGrado = curso_grado.idCursoGrado', 'INNER')
            ->join('curso AS cursos', 'curso_grado.idCurso = cursos.idCurso', 'INNER')
            ->join('grado AS grados', 'curso_grado.idGrado = grados.idGrado', 'INNER')
            ->join('nivel AS niveles', 'grados.idNivel = niveles.idNivel', 'INNER')
            ->join('area AS areas', 'cursos.idArea = areas.idArea', 'INNER')
            ->get()->getResultArray();
    }
}
