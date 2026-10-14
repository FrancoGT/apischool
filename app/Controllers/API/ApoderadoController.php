<?php

namespace App\Controllers\API;

use App\Models\ApoderadoModel;
use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

class ApoderadoController extends ResourceController
{
    protected $apoderadoModel;

    public function __construct()
    {
        $this->apoderadoModel = new ApoderadoModel();
    }

    // Mostrar todos los apoderados
    #[OA\Get(
        path: '/api/apoderados',
        summary: 'Listar apoderados',
        tags: ['Apoderados'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de apoderados',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'apoderados', type: 'array', items: new OA\Items(ref: '#/components/schemas/Apoderado')),
                ])
            ),
        ],
    )]
    public function index()
    {
        $data['apoderados'] = $this->apoderadoModel->findAll();
        return $this->respond($data, 200);
    }

    // Mostrar un apoderado específico
    #[OA\Get(
        path: '/api/apoderados/{id}',
        summary: 'Obtener apoderado por ID',
        tags: ['Apoderados'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Apoderado encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Apoderado')),
            new OA\Response(response: 404, description: 'Apoderado no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function show($id = null)
    {
        $apoderado = $this->apoderadoModel->find($id);
        if (!$apoderado) {
            return $this->failNotFound('Apoderado no encontrado');
        }
        return $this->respond($apoderado, 200);
    }

    // Mostrar formulario para agregar un apoderado (opcional)
    public function create()
    {
        return view('apoderado/create');
    }

    // Guardar un nuevo apoderado
    public function store()
    {
        $validation = \Config\Services::validation();

        // Reglas de validación
        if ($this->validate([
            'nombre'   => 'required|max_length[100]',
            'apellido' => 'required|max_length[100]',
            'dni'      => 'required|exact_length[8]|numeric',
            'telefono' => 'max_length[15]|numeric',
            'correo'   => 'valid_email|max_length[100]',
            'direccion' => 'max_length[255]',
        ])) {
            $this->apoderadoModel->save([
                'nombre'     => $this->request->getPost('nombre'),
                'apellido'   => $this->request->getPost('apellido'),
                'dni'        => $this->request->getPost('dni'),
                'telefono'   => $this->request->getPost('telefono'),
                'correo'     => $this->request->getPost('correo'),
                'direccion'  => $this->request->getPost('direccion'),
                'estado'     => $this->request->getPost('estado'),
                'usuario_creacion' => 1,
                'usuario_actualizacion' => 1,
            ]);

            return redirect()->to('/apoderado');
        } else {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
    }

    // Actualizar un apoderado existente
    public function update($id = null)
    {
        $validation = \Config\Services::validation();

        // Reglas de validación
        if ($this->validate([
            'nombre'   => 'required|max_length[100]',
            'apellido' => 'required|max_length[100]',
            'dni'      => 'required|exact_length[8]|numeric',
            'telefono' => 'max_length[15]|numeric',
            'correo'   => 'valid_email|max_length[100]',
            'direccion' => 'max_length[255]',
        ])) {
            $this->apoderadoModel->update($id, [
                'nombre'     => $this->request->getPost('nombre'),
                'apellido'   => $this->request->getPost('apellido'),
                'dni'        => $this->request->getPost('dni'),
                'telefono'   => $this->request->getPost('telefono'),
                'correo'     => $this->request->getPost('correo'),
                'direccion'  => $this->request->getPost('direccion'),
                'estado'     => $this->request->getPost('estado'),
                'usuario_actualizacion' => 1,
            ]);

            return redirect()->to('/apoderado');
        } else {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
    }

    // Eliminar un apoderado
    #[OA\Delete(
        path: '/api/apoderados/{id}',
        summary: 'Eliminar apoderado',
        tags: ['Apoderados'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Apoderado eliminado',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string')])
            ),
            new OA\Response(response: 404, description: 'Apoderado no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function delete($id = null)
    {
        $apoderado = $this->apoderadoModel->find($id);
        if (!$apoderado) {
            return $this->failNotFound('Apoderado no encontrado');
        }

        $this->apoderadoModel->delete($id);
        return $this->respondDeleted(['message' => 'Apoderado eliminado correctamente']);
    }
}