<?php

namespace App\Controllers\API;

use App\Models\UnidadModel;
use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

class UnidadController extends ResourceController
{
    protected $unidadModel;

    public function __construct()
    {
        $this->unidadModel = new UnidadModel();
    }

    #[OA\Get(
        path: '/api/unidades',
        summary: 'Listar unidades',
        tags: ['Unidades'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de unidades',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'unidades', type: 'array', items: new OA\Items(ref: '#/components/schemas/Unidad')),
                ])
            ),
        ]
    )]
    public function index()
    {
        $data['unidades'] = $this->unidadModel->findAll();
        return $this->respond($data, 200);
    }

    #[OA\Get(
        path: '/api/unidades/{id}',
        summary: 'Obtener una unidad por ID',
        tags: ['Unidades'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Unidad encontrada', content: new OA\JsonContent(ref: '#/components/schemas/Unidad')),
            new OA\Response(response: 404, description: 'Unidad no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ]
    )]
    public function show($id = null)
    {
        $unidad = $this->unidadModel->find($id);
        if ($unidad) {
            return $this->respond($unidad, 200);
        } else {
            return $this->failNotFound('Unidad no encontrada');
        }
    }

    #[OA\Get(
        path: '/api/unidades/reporte',
        summary: 'Reporte completo de unidades',
        tags: ['Unidades'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Reporte de unidades',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(type: 'object', additionalProperties: true))
            ),
        ]
    )]
    public function reporte()
    {
        $reporte = $this->unidadModel->getReporteUnidades();
        return $this->respond($reporte, 200);
    }
}