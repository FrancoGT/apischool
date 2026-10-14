<?php

namespace App\Controllers\API;

use App\Models\TipoUsuarioModel;
use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

class TipoUsuarioController extends ResourceController
{
    protected $tipoUsuarioModel;

    public function __construct()
    {
        $this->tipoUsuarioModel = new TipoUsuarioModel();
    }

    // Mostrar todos los tipos de usuarios
    #[OA\Get(
        path: '/api/tipos-usuario',
        summary: 'Listar tipos de usuario',
        tags: ['Tipos de usuario'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de tipos de usuario',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'tiposUsuarios', type: 'array', items: new OA\Items(ref: '#/components/schemas/TipoUsuario')),
                ])
            ),
        ],
    )]
    public function index()
    {
        $data['tiposUsuarios'] = $this->tipoUsuarioModel->findAll();
        return $this->respond($data, 200);
    }

    // Mostrar un tipo de usuario específico
    #[OA\Get(
        path: '/api/tipos-usuario/{id}',
        summary: 'Obtener tipo de usuario por ID',
        tags: ['Tipos de usuario'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Tipo de usuario encontrado', content: new OA\JsonContent(ref: '#/components/schemas/TipoUsuario')),
            new OA\Response(response: 404, description: 'Tipo de usuario no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function show($id = null)
    {
        $tipoUsuario = $this->tipoUsuarioModel->find($id);
        if (!$tipoUsuario) {
            return $this->failNotFound('Tipo de usuario no encontrado');
        }
        return $this->respond($tipoUsuario, 200);
    }

    // Mostrar formulario para agregar un tipo de usuario (opcional)
    public function create()
    {
        return view('tipousuario/create');
    }

    // Guardar un nuevo tipo de usuario
    public function store()
    {
        $validation = \Config\Services::validation();

        // Reglas de validación
        if ($this->validate([
            'nombreTipo' => 'required|max_length[100]',
            'descripcion' => 'max_length[255]',
        ])) {
            $this->tipoUsuarioModel->save([
                'nombreTipo' => $this->request->getPost('nombreTipo'),
                'descripcion' => $this->request->getPost('descripcion'),
                'estado' => $this->request->getPost('estado'),
                'usuario_creacion' => 1,
                'usuario_actualizacion' => 1,
            ]);

            return redirect()->to('/tipousuario');
        } else {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
    }

    // Actualizar un tipo de usuario existente
    public function update($id = null)
    {
        $validation = \Config\Services::validation();

        // Reglas de validación
        if ($this->validate([
            'nombreTipo' => 'required|max_length[100]',
            'descripcion' => 'max_length[255]',
        ])) {
            $this->tipoUsuarioModel->update($id, [
                'nombreTipo' => $this->request->getPost('nombreTipo'),
                'descripcion' => $this->request->getPost('descripcion'),
                'estado' => $this->request->getPost('estado'),
                'usuario_actualizacion' => 1,
            ]);

            return redirect()->to('/tipousuario');
        } else {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
    }

    // Eliminar un tipo de usuario
    #[OA\Delete(
        path: '/api/tipos-usuario/{id}',
        summary: 'Eliminar tipo de usuario',
        tags: ['Tipos de usuario'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tipo de usuario eliminado',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string')])
            ),
            new OA\Response(response: 404, description: 'Tipo de usuario no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function delete($id = null)
    {
        $tipoUsuario = $this->tipoUsuarioModel->find($id);
        if (!$tipoUsuario) {
            return $this->failNotFound('Tipo de usuario no encontrado');
        }

        $this->tipoUsuarioModel->delete($id);
        return $this->respondDeleted(['message' => 'Tipo de usuario eliminado correctamente']);
    }
}