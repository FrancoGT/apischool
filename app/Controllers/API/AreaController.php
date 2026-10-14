<?php

namespace App\Controllers\API;

use App\Models\AreaModel;
use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

class AreaController extends ResourceController
{
    protected $areaModel;

    public function __construct()
    {
        $this->areaModel = new AreaModel();
    }

    // Mostrar todos los registros
    #[OA\Get(
        path: '/api/areas',
        summary: 'Listar áreas',
        tags: ['Áreas'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de áreas',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'areas', type: 'array', items: new OA\Items(ref: '#/components/schemas/Area')),
                ])
            ),
        ],
    )]
    public function index()
    {
        $data['areas'] = $this->areaModel->findAll();
        return $this->respond($data, 200);
    }

    // Mostrar formulario para agregar un área
    public function create()
    {
        return view('area/create');
    }

    // Guardar nuevo área
    public function store()
    {
        $validation = \Config\Services::validation();

        if ($this->validate([
            'descripcionArea'    => 'required|max_length[100]',
            'fechaCreacion'      => 'required|valid_date',
            'fechaActualizacion' => 'required|valid_date',
            'usuarioCreacion'    => 'required|integer',
            'usuarioActualizacion' => 'required|integer',
        ])) {
            $this->areaModel->save([
                'descripcionArea'    => $this->request->getPost('descripcionArea'),
                'fechaCreacion'      => $this->request->getPost('fechaCreacion'),
                'fechaActualizacion' => $this->request->getPost('fechaActualizacion'),
                'usuarioCreacion'    => $this->request->getPost('usuarioCreacion'),
                'usuarioActualizacion' => $this->request->getPost('usuarioActualizacion'),
            ]);

            return redirect()->to('/area');
        } else {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
    }

    // Mostrar un área específica
    #[OA\Get(
        path: '/api/areas/{id}',
        summary: 'Obtener área por ID',
        tags: ['Áreas'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Área encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Area')),
            new OA\Response(response: 404, description: 'Área no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function show($id = null)
    {
        $data = $this->areaModel->find($id);
        if (!$data) {
            return $this->failNotFound('Área no encontrada');
        }
        return $this->respond($data);
    }

    // Actualizar un área
    #[OA\Put(
        path: '/api/areas/{id}',
        summary: 'Actualizar un área',
        tags: ['Áreas'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'descripcionArea', type: 'string', maxLength: 100),
                        new OA\Property(property: 'fechaActualizacion', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'usuarioActualizacion', type: 'integer'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Área actualizada',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string')])
            ),
            new OA\Response(response: 400, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function update($id = null)
    {
        $input = $this->request->getRawInput();

        if ($this->areaModel->update($id, $input)) {
            return $this->respond(['message' => 'Área actualizada con éxito']);
        }

        return $this->failValidationErrors($this->areaModel->errors());
    }

    // Eliminar un área
    #[OA\Delete(
        path: '/api/areas/{id}',
        summary: 'Eliminar área',
        tags: ['Áreas'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Área eliminado',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string')])
            ),
            new OA\Response(response: 404, description: 'Área no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function delete($id = null)
    {
        if ($this->areaModel->delete($id)) {
            return $this->respondDeleted(['message' => 'Área eliminada con éxito']);
        }

        return $this->failNotFound('Área no encontrada');
    }
}