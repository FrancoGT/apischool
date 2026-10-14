<?php

namespace App\Controllers\API;

use App\Models\CompetenciaModel;
use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

class CompetenciaController extends ResourceController
{
    protected $competenciaModel;

    public function __construct()
    {
        $this->competenciaModel = new CompetenciaModel();
    }

    // Mostrar todas las competencias con los joins
    #[OA\Get(
        path: '/api/competencias',
        summary: 'Listar competencias con unidad, bimestre, curso y grado',
        tags: ['Competencias'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de competencias',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'competencias', type: 'array', items: new OA\Items(type: 'object', additionalProperties: true)),
                ])
            ),
            new OA\Response(response: 404, description: 'No se encontraron competencias', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ]
    )]
    public function index()
    {
        // Obtener las competencias con la información relacionada
        $data['competencias'] = $this->competenciaModel->getCompetenciasWithJoin();

        if (count($data['competencias']) > 0) {
            return $this->respond($data, 200);
        } else {
            return $this->failNotFound('No se encontraron competencias');
        }
    }

    // Mostrar una competencia por ID
    #[OA\Get(
        path: '/api/competencias/{id}',
        summary: 'Obtener una competencia por ID',
        tags: ['Competencias'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Competencia encontrada', content: new OA\JsonContent(ref: '#/components/schemas/Competencia')),
            new OA\Response(response: 404, description: 'Competencia no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ]
    )]
    public function show($id = null)
    {
        $competencia = $this->competenciaModel->find($id);
        if ($competencia) {
            return $this->respond($competencia, 200);
        } else {
            return $this->failNotFound('Competencia no encontrada');
        }
    }

    // Crear una nueva competencia
    #[OA\Post(
        path: '/api/competencias',
        summary: 'Crear una competencia',
        tags: ['Competencias'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    required: ['idUnidad', 'descripcionCompetencia'],
                    properties: [
                        new OA\Property(property: 'idUnidad', type: 'integer'),
                        new OA\Property(property: 'descripcionCompetencia', type: 'string', maxLength: 255),
                        new OA\Property(property: 'capacidadesCompetencia', type: 'string'),
                        new OA\Property(property: 'estandarCompetencia', type: 'string'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Competencia creada',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Competencia creada exitosamente'),
                ])
            ),
            new OA\Response(response: 400, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ]
    )]
    public function create()
    {
        $validation = \Config\Services::validation();

        if ($this->validate([
            'idUnidad'                => 'required|integer',
            'descripcionCompetencia'  => 'required|max_length[255]',
            'capacidadesCompetencia'  => 'permit_empty',
            'estandarCompetencia'     => 'permit_empty',
        ])) {
            $this->competenciaModel->save($this->request->getPost());
            return $this->respondCreated(['message' => 'Competencia creada exitosamente']);
        } else {
            return $this->fail($validation->getErrors());
        }
    }
}