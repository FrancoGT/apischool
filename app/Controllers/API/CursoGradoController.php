<?php

namespace App\Controllers\API;

use App\Models\CursoGradoModel;
use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

class CursoGradoController extends ResourceController
{
    protected $cursoGradoModel;

    public function __construct()
    {
        $this->cursoGradoModel = new CursoGradoModel();
    }

    // Mostrar todos los registros
    #[OA\Get(
        path: '/api/cursos-grados',
        summary: 'Listar cursos por grado',
        tags: ['Cursos por grado'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de cursos por grado',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/CursoGrado'))
            ),
        ],
    )]
    public function index()
    {
        $data = $this->cursoGradoModel->findAll();
        return $this->respond($data, 200);
    }

    // Crear nuevo registro
    #[OA\Post(
        path: '/api/cursos-grados',
        summary: 'Crear curso por grado',
        tags: ['Cursos por grado'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    required: ['idCurso', 'idGrado', 'usuarioCreacion', 'usuarioActualizacion'],
                    properties: [
                        new OA\Property(property: 'idCurso', type: 'integer'),
                        new OA\Property(property: 'idGrado', type: 'integer'),
                        new OA\Property(property: 'usuarioCreacion', type: 'integer'),
                        new OA\Property(property: 'usuarioActualizacion', type: 'integer'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Registro creado',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string')])
            ),
            new OA\Response(response: 400, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function create()
    {
        $validation = \Config\Services::validation();

        if ($this->validate([
            'idCurso'            => 'required|integer',
            'idGrado'            => 'required|integer',
            'usuarioCreacion'    => 'required|integer',
            'usuarioActualizacion' => 'required|integer',
        ])) {
            $this->cursoGradoModel->save([
                'idCurso'            => $this->request->getPost('idCurso'),
                'idGrado'            => $this->request->getPost('idGrado'),
                'fechaCreacion'      => date('Y-m-d H:i:s'),
                'fechaActualizacion' => date('Y-m-d H:i:s'),
                'usuarioCreacion'    => $this->request->getPost('usuarioCreacion'),
                'usuarioActualizacion' => $this->request->getPost('usuarioActualizacion'),
            ]);

            return $this->respondCreated(['message' => 'Registro creado exitosamente']);
        } else {
            return $this->failValidationErrors($validation->getErrors());
        }
    }
}