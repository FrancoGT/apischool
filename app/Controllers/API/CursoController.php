<?php

namespace App\Controllers\API;

use App\Models\CursoModel;
use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

class CursoController extends ResourceController
{
    protected $cursoModel;

    public function __construct()
    {
        $this->cursoModel = new CursoModel();
    }

    // Listar todos los cursos
    #[OA\Get(
        path: '/api/cursos',
        summary: 'Listar cursos',
        tags: ['Cursos'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de cursos',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'cursos', type: 'array', items: new OA\Items(ref: '#/components/schemas/Curso')),
                ])
            ),
        ],
    )]
    public function index()
    {
        $data['cursos'] = $this->cursoModel->findAll();
        return $this->respond($data, 200);
    }

    // Crear un nuevo curso
    public function create()
    {
        return view('curso/create');
    }

    // Guardar un curso nuevo
    public function store()
    {
        $validation = \Config\Services::validation();

        if ($this->validate([
            'idArea' => 'required|integer',
            'descripcionCurso' => 'required|max_length[100]',
            'estadoCurso' => 'permit_empty|integer',
        ])) {
            $this->cursoModel->save([
                'idArea' => $this->request->getPost('idArea'),
                'descripcionCurso' => $this->request->getPost('descripcionCurso'),
                'fechaCreacion' => date('Y-m-d H:i:s'),
                'fechaActualizacion' => date('Y-m-d H:i:s'),
                'usuarioCreacion' => 1,
                'usuarioActualizacion' => 1,
                'estadoCurso' => $this->request->getPost('estadoCurso'),
            ]);

            return redirect()->to('/curso');
        } else {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
    }
}