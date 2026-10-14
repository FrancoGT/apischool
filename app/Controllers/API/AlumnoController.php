<?php

namespace App\Controllers\API;

use App\Models\AlumnoModel;
use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

class AlumnoController extends ResourceController
{
    protected $alumnoModel;

    public function __construct()
    {
        $this->alumnoModel = new AlumnoModel();
    }

    // Mostrar todos los alumnos
    #[OA\Get(
        path: '/api/alumnos',
        summary: 'Listar alumnos',
        tags: ['Alumnos'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de alumnos',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'alumnos', type: 'array', items: new OA\Items(ref: '#/components/schemas/Alumno')),
                ])
            ),
        ],
    )]
    public function index()
    {
        $data['alumnos'] = $this->alumnoModel->findAll();
        return $this->respond($data, 200);
    }

    // Mostrar un alumno específico
    #[OA\Get(
        path: '/api/alumnos/{id}',
        summary: 'Obtener alumno por ID',
        tags: ['Alumnos'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Alumno encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Alumno')),
            new OA\Response(response: 404, description: 'Alumno no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function show($id = null)
    {
        $alumno = $this->alumnoModel->find($id);
        if (!$alumno) {
            return $this->failNotFound('Alumno no encontrado');
        }
        return $this->respond($alumno, 200);
    }

    // Mostrar formulario para agregar un alumno (opcional, para vistas HTML)
    public function create()
    {
        return view('alumno/create');
    }

    // Guardar un nuevo alumno
    public function store()
    {
        $validation = \Config\Services::validation();

        // Reglas de validación
        if ($this->validate([
            'nombre'      => 'required|max_length[200]',
            'apellido'    => 'required|max_length[200]',
            'dni'         => 'required|integer',
            'fecha_nacimiento' => 'required|valid_date',
            'estado'      => 'required|integer',
        ])) {
            $this->alumnoModel->save([
                'nombre'            => $this->request->getPost('nombre'),
                'apellido'          => $this->request->getPost('apellido'),
                'dni'               => $this->request->getPost('dni'),
                'fecha_nacimiento'  => $this->request->getPost('fecha_nacimiento'),
                'estado'            => $this->request->getPost('estado'),
                'usuario_creacion'  => 1,
                'usuario_actualizacion' => 1,
            ]);

            return redirect()->to('/alumno');
        } else {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
    }

    // Actualizar un alumno existente
    public function update($id = null)
    {
        $validation = \Config\Services::validation();

        // Reglas de validación
        if ($this->validate([
            'nombre'      => 'required|max_length[200]',
            'apellido'    => 'required|max_length[200]',
            'dni'         => 'required|integer',
            'fecha_nacimiento' => 'required|valid_date',
            'estado'      => 'required|integer',
        ])) {
            $this->alumnoModel->update($id, [
                'nombre'            => $this->request->getPost('nombre'),
                'apellido'          => $this->request->getPost('apellido'),
                'dni'               => $this->request->getPost('dni'),
                'fecha_nacimiento'  => $this->request->getPost('fecha_nacimiento'),
                'estado'            => $this->request->getPost('estado'),
                'usuario_actualizacion' => 1,
            ]);

            return redirect()->to('/alumno');
        } else {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
    }

    // Eliminar un alumno
    #[OA\Delete(
        path: '/api/alumnos/{id}',
        summary: 'Eliminar alumno',
        tags: ['Alumnos'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Alumno eliminado',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string')])
            ),
            new OA\Response(response: 404, description: 'Alumno no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function delete($id = null)
    {
        $alumno = $this->alumnoModel->find($id);
        if (!$alumno) {
            return $this->failNotFound('Alumno no encontrado');
        }

        $this->alumnoModel->delete($id);
        return $this->respondDeleted(['message' => 'Alumno eliminado correctamente']);
    }
}