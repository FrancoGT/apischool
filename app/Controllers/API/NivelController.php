<?php

namespace App\Controllers\API;

use App\Models\NivelModel;
use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

class NivelController extends ResourceController
{
    protected $nivelModel;

    public function __construct()
    {
        $this->nivelModel = new NivelModel();
    }

    // Mostrar todos los niveles
    #[OA\Get(
        path: '/api/niveles',
        summary: 'Listar niveles',
        tags: ['Niveles'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de niveles',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'niveles', type: 'array', items: new OA\Items(ref: '#/components/schemas/Nivel')),
                ])
            ),
        ],
    )]
    public function index()
    {
        $data['niveles'] = $this->nivelModel->findAll();
        return $this->respond($data, 200);
    }

    // Mostrar un nivel específico
    #[OA\Get(
        path: '/api/niveles/{id}',
        summary: 'Obtener nivel por ID',
        tags: ['Niveles'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Nivel encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Nivel')),
            new OA\Response(response: 404, description: 'Nivel no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function show($id = null)
    {
        $nivel = $this->nivelModel->find($id);
        if (!$nivel) {
            return $this->failNotFound('Nivel no encontrado');
        }
        return $this->respond($nivel, 200);
    }

    // Mostrar formulario para agregar un nivel (opcional)
    public function create()
    {
        return view('nivel/create');
    }

    // Guardar un nuevo nivel
    public function store()
    {
        $validation = \Config\Services::validation();

        // Reglas de validación
        if ($this->validate([
            'nombreNivel'   => 'required|max_length[100]',
            'descripcion'   => 'max_length[255]',
        ])) {
            $this->nivelModel->save([
                'nombreNivel'   => $this->request->getPost('nombreNivel'),
                'descripcion'   => $this->request->getPost('descripcion'),
                'estado'        => $this->request->getPost('estado'),
                'usuario_creacion' => 1,
                'usuario_actualizacion' => 1,
            ]);

            return redirect()->to('/nivel');
        } else {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
    }

    // Actualizar un nivel existente
    public function update($id = null)
    {
        $validation = \Config\Services::validation();

        // Reglas de validación
        if ($this->validate([
            'nombreNivel'   => 'required|max_length[100]',
            'descripcion'   => 'max_length[255]',
        ])) {
            $this->nivelModel->update($id, [
                'nombreNivel'   => $this->request->getPost('nombreNivel'),
                'descripcion'   => $this->request->getPost('descripcion'),
                'estado'        => $this->request->getPost('estado'),
                'usuario_actualizacion' => 1,
            ]);

            return redirect()->to('/nivel');
        } else {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
    }

    // Eliminar un nivel
    #[OA\Delete(
        path: '/api/niveles/{id}',
        summary: 'Eliminar nivel',
        tags: ['Niveles'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Nivel eliminado',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string')])
            ),
            new OA\Response(response: 404, description: 'Nivel no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function delete($id = null)
    {
        $nivel = $this->nivelModel->find($id);
        if (!$nivel) {
            return $this->failNotFound('Nivel no encontrado');
        }

        $this->nivelModel->delete($id);
        return $this->respondDeleted(['message' => 'Nivel eliminado correctamente']);
    }
}