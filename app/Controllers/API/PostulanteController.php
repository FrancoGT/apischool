<?php

namespace App\Controllers\API;

use App\Models\PostulanteModel;
use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

class PostulanteController extends ResourceController
{
    protected $postulanteModel;

    public function __construct()
    {
        $this->postulanteModel = new PostulanteModel();
    }

    // Mostrar todos los postulantes
    #[OA\Get(
        path: '/api/postulantes',
        summary: 'Listar postulantes',
        tags: ['Postulantes'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de postulantes',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'postulantes', type: 'array', items: new OA\Items(ref: '#/components/schemas/Postulante')),
                ])
            ),
        ]
    )]
    public function index()
    {
        $data['postulantes'] = $this->postulanteModel->findAll();
        return $this->respond($data, 200);
    }

    // Mostrar formulario para agregar un postulante
    public function create()
    {
        return view('postulante/create');
    }

    // Guardar nuevo postulante
    public function store()
    {
        $validation = \Config\Services::validation();

        if ($this->validate([
            'nombrePostulante'      => 'required|max_length[200]',
            'apellidoPostulante'    => 'required|max_length[200]',
            'gradoPostulacion'      => 'required|max_length[50]',
            'fechaPostulacion'      => 'required|valid_date',
            'estadoPostulante'      => 'required|integer',
        ])) {
            $this->postulanteModel->save([
                'nombrePostulante'      => $this->request->getPost('nombrePostulante'),
                'apellidoPostulante'    => $this->request->getPost('apellidoPostulante'),
                'sexoPostulante'        => $this->request->getPost('sexoPostulante'),
                'dniPostulante'         => $this->request->getPost('dniPostulante'),
                'fechaNacimiento'       => $this->request->getPost('fechaNacimiento'),
                'lugarNacimiento'       => $this->request->getPost('lugarNacimiento'),
                'gradoPostulacion'      => $this->request->getPost('gradoPostulacion'),
                'domicilioPostulante'   => $this->request->getPost('domicilioPostulante'),
                'colegioProcedencia'    => $this->request->getPost('colegioProcedencia'),
                'dificultadPostulante'  => $this->request->getPost('dificultadPostulante'),
                'fechaPostulacion'      => $this->request->getPost('fechaPostulacion'),
                'listaApoderados'       => $this->request->getPost('listaApoderados'),
                'estadoPostulante'      => $this->request->getPost('estadoPostulante'),
                'usuarioCreacion'       => 1,
                'usuarioActualizacion'  => 1,
            ]);

            return redirect()->to('/postulante');
        } else {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
    }
}