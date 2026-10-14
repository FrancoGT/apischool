<?php

namespace App\Controllers\API;

use App\Models\AnioEscolarModel;
use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

class AnioEscolarController extends ResourceController
{
    protected $anioEscolarModel;

    public function __construct()
    {
        $this->anioEscolarModel = new AnioEscolarModel();
    }

    // Mostrar todos los años escolares
    #[OA\Get(
        path: '/api/anios-escolares',
        summary: 'Listar años escolares',
        tags: ['Años escolares'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de años escolares',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'aniosEscolares', type: 'array', items: new OA\Items(ref: '#/components/schemas/AnioEscolar')),
                ])
            ),
        ],
    )]
    public function index()
    {
        $data['aniosEscolares'] = $this->anioEscolarModel->findAll();
        return $this->respond($data, 200);
    }

    // Mostrar un año escolar específico
    #[OA\Get(
        path: '/api/anios-escolares/{id}',
        summary: 'Obtener año escolar por ID',
        tags: ['Años escolares'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Año escolar encontrado', content: new OA\JsonContent(ref: '#/components/schemas/AnioEscolar')),
            new OA\Response(response: 404, description: 'Año escolar no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function show($id = null)
    {
        $anioEscolar = $this->anioEscolarModel->find($id);
        if (!$anioEscolar) {
            return $this->failNotFound('Año escolar no encontrado');
        }
        return $this->respond($anioEscolar, 200);
    }

    // Mostrar formulario para agregar un año escolar (opcional)
    public function create()
    {
        return view('anioEscolar/create');
    }

    // Guardar un nuevo año escolar
    public function store()
    {
        $validation = \Config\Services::validation();

        // Reglas de validación
        if ($this->validate([
            'nombre_anio'  => 'required|max_length[100]',
            'descripcion'  => 'max_length[255]',
            'fecha_inicio' => 'required|valid_date',
            'fecha_fin'    => 'required|valid_date|after_date[fecha_inicio]',
        ])) {
            $this->anioEscolarModel->save([
                'nombre_anio'  => $this->request->getPost('nombre_anio'),
                'descripcion'  => $this->request->getPost('descripcion'),
                'fecha_inicio' => $this->request->getPost('fecha_inicio'),
                'fecha_fin'    => $this->request->getPost('fecha_fin'),
                'estado'       => $this->request->getPost('estado'),
                'usuario_creacion' => 1,
                'usuario_actualizacion' => 1,
            ]);

            return redirect()->to('/anioEscolar');
        } else {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
    }

    // Actualizar un año escolar existente
    public function update($id = null)
    {
        $validation = \Config\Services::validation();

        // Reglas de validación
        if ($this->validate([
            'nombre_anio'  => 'required|max_length[100]',
            'descripcion'  => 'max_length[255]',
            'fecha_inicio' => 'required|valid_date',
            'fecha_fin'    => 'required|valid_date|after_date[fecha_inicio]',
        ])) {
            $this->anioEscolarModel->update($id, [
                'nombre_anio'  => $this->request->getPost('nombre_anio'),
                'descripcion'  => $this->request->getPost('descripcion'),
                'fecha_inicio' => $this->request->getPost('fecha_inicio'),
                'fecha_fin'    => $this->request->getPost('fecha_fin'),
                'estado'       => $this->request->getPost('estado'),
                'usuario_actualizacion' => 1,
            ]);

            return redirect()->to('/anioEscolar');
        } else {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
    }

    // Eliminar un año escolar
    #[OA\Delete(
        path: '/api/anios-escolares/{id}',
        summary: 'Eliminar año escolar',
        tags: ['Años escolares'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Año escolar eliminado',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string')])
            ),
            new OA\Response(response: 404, description: 'Año escolar no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function delete($id = null)
    {
        $anioEscolar = $this->anioEscolarModel->find($id);
        if (!$anioEscolar) {
            return $this->failNotFound('Año escolar no encontrado');
        }

        $this->anioEscolarModel->delete($id);
        return $this->respondDeleted(['message' => 'Año escolar eliminado correctamente']);
    }
}