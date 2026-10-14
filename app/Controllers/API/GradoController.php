<?php

namespace App\Controllers\API;

use App\Models\GradoModel;
use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

class GradoController extends ResourceController
{
    protected $gradoModel;

    public function __construct()
    {
        $this->gradoModel = new GradoModel();
    }

    // Mostrar todos los grados
    #[OA\Get(
        path: '/api/grados',
        summary: 'Listar grados',
        tags: ['Grados'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de grados',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'grados', type: 'array', items: new OA\Items(ref: '#/components/schemas/Grado')),
                ])
            ),
        ],
    )]
    public function index()
    {
        $data['grados'] = $this->gradoModel->findAll();
        return $this->respond($data, 200);
    }

    // Mostrar un grado específico
    #[OA\Get(
        path: '/api/grados/{id}',
        summary: 'Obtener grado por ID',
        tags: ['Grados'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Grado encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Grado')),
            new OA\Response(response: 404, description: 'Grado no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function show($id = null)
    {
        $grado = $this->gradoModel->find($id);
        if (!$grado) {
            return $this->failNotFound('Grado no encontrado');
        }
        return $this->respond($grado, 200);
    }

    // Mostrar formulario para agregar un grado (opcional)
    public function create()
    {
        return view('grado/create');
    }

    // Guardar un nuevo grado
    public function store()
    {
        $validation = \Config\Services::validation();

        // Reglas de validación
        if ($this->validate([
            'nombreGrado'   => 'required|max_length[100]',
            'descripcion'   => 'max_length[255]',
        ])) {
            $this->gradoModel->save([
                'nombreGrado'   => $this->request->getPost('nombreGrado'),
                'descripcion'   => $this->request->getPost('descripcion'),
                'estado'        => $this->request->getPost('estado'),
                'usuario_creacion' => 1,
                'usuario_actualizacion' => 1,
            ]);

            return redirect()->to('/grado');
        } else {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
    }

    // Actualizar un grado existente
    public function update($id = null)
    {
        $validation = \Config\Services::validation();

        // Reglas de validación
        if ($this->validate([
            'nombreGrado'   => 'required|max_length[100]',
            'descripcion'   => 'max_length[255]',
        ])) {
            $this->gradoModel->update($id, [
                'nombreGrado'   => $this->request->getPost('nombreGrado'),
                'descripcion'   => $this->request->getPost('descripcion'),
                'estado'        => $this->request->getPost('estado'),
                'usuario_actualizacion' => 1,
            ]);

            return redirect()->to('/grado');
        } else {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
    }

    // Eliminar un grado
    #[OA\Delete(
        path: '/api/grados/{id}',
        summary: 'Eliminar grado',
        tags: ['Grados'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Grado eliminado',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string')])
            ),
            new OA\Response(response: 404, description: 'Grado no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function delete($id = null)
    {
        $grado = $this->gradoModel->find($id);
        if (!$grado) {
            return $this->failNotFound('Grado no encontrado');
        }

        $this->gradoModel->delete($id);
        return $this->respondDeleted(['message' => 'Grado eliminado correctamente']);
    }
}