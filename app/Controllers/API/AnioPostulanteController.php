<?php

namespace App\Controllers\API;

use App\Models\AnioPostulanteModel;
use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

class AnioPostulanteController extends ResourceController
{
    protected $anioPostulanteModel;

    public function __construct()
    {
        $this->anioPostulanteModel = new AnioPostulanteModel();
    }

    // Mostrar todos los años postulantes
    #[OA\Get(
        path: '/api/anios-postulantes',
        summary: 'Listar años postulantes',
        tags: ['Años postulantes'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de años postulantes',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'aniosPostulantes', type: 'array', items: new OA\Items(ref: '#/components/schemas/AnioPostulante')),
                ])
            ),
        ],
    )]
    public function index()
    {
        $data['aniosPostulantes'] = $this->anioPostulanteModel->findAll();
        return $this->respond($data, 200);
    }

    // Mostrar un año postulante específico
    #[OA\Get(
        path: '/api/anios-postulantes/{id}',
        summary: 'Obtener año postulante por ID',
        tags: ['Años postulantes'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Año postulante encontrado', content: new OA\JsonContent(ref: '#/components/schemas/AnioPostulante')),
            new OA\Response(response: 404, description: 'Año postulante no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function show($id = null)
    {
        $anioPostulante = $this->anioPostulanteModel->find($id);
        if (!$anioPostulante) {
            return $this->failNotFound('Año postulante no encontrado');
        }
        return $this->respond($anioPostulante, 200);
    }

    // Mostrar formulario para agregar un año postulante (opcional)
    public function create()
    {
        return view('anioPostulante/create');
    }

    // Guardar un nuevo año postulante
    public function store()
    {
        $validation = \Config\Services::validation();

        // Reglas de validación
        if ($this->validate([
            'nombre_anio_postulante' => 'required|max_length[100]',
            'descripcion'            => 'max_length[255]',
            'estado'                 => 'required|integer',
        ])) {
            $this->anioPostulanteModel->save([
                'nombre_anio_postulante' => $this->request->getPost('nombre_anio_postulante'),
                'descripcion'            => $this->request->getPost('descripcion'),
                'estado'                 => $this->request->getPost('estado'),
                'usuario_creacion'       => 1,
                'usuario_actualizacion'  => 1,
            ]);

            return redirect()->to('/anioPostulante');
        } else {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
    }

    // Actualizar un año postulante existente
    public function update($id = null)
    {
        $validation = \Config\Services::validation();

        // Reglas de validación
        if ($this->validate([
            'nombre_anio_postulante' => 'required|max_length[100]',
            'descripcion'            => 'max_length[255]',
            'estado'                 => 'required|integer',
        ])) {
            $this->anioPostulanteModel->update($id, [
                'nombre_anio_postulante' => $this->request->getPost('nombre_anio_postulante'),
                'descripcion'            => $this->request->getPost('descripcion'),
                'estado'                 => $this->request->getPost('estado'),
                'usuario_actualizacion'  => 1,
            ]);

            return redirect()->to('/anioPostulante');
        } else {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
    }

    // Eliminar un año postulante
    #[OA\Delete(
        path: '/api/anios-postulantes/{id}',
        summary: 'Eliminar año postulante',
        tags: ['Años postulantes'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Año postulante eliminado',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string')])
            ),
            new OA\Response(response: 404, description: 'Año postulante no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function delete($id = null)
    {
        $anioPostulante = $this->anioPostulanteModel->find($id);
        if (!$anioPostulante) {
            return $this->failNotFound('Año postulante no encontrado');
        }

        $this->anioPostulanteModel->delete($id);
        return $this->respondDeleted(['message' => 'Año postulante eliminado correctamente']);
    }
}