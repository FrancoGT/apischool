<?php

namespace App\Controllers\API;

use App\Libraries\JwtAuth;
use App\Models\SesionModel;
use App\Models\UsuarioModel;
use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;
use RuntimeException;

class UsuarioController extends ResourceController
{
    protected $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
    }

    // Listar todos los usuarios
    #[OA\Get(
        path: '/api/usuarios',
        summary: 'Listar usuarios',
        tags: ['Usuarios'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de usuarios',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'usuarios', type: 'array', items: new OA\Items(ref: '#/components/schemas/Usuario')),
                ])
            ),
        ],
    )]
    public function index()
    {
        // No exponer el hash de la contraseña
        $data['usuarios'] = array_map(static function ($usuario) {
            unset($usuario['password']);

            return $usuario;
        }, $this->usuarioModel->findAll());
        return $this->respond($data, 200);
    }

    // Mostrar un usuario específico
    #[OA\Get(
        path: '/api/usuarios/{id}',
        summary: 'Obtener usuario por ID',
        tags: ['Usuarios'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Usuario encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Usuario')),
            new OA\Response(response: 404, description: 'Usuario no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function show($id = null)
    {
        $usuario = $this->usuarioModel->find($id);
        if (!$usuario) {
            return $this->failNotFound('Usuario no encontrado');
        }
        unset($usuario['password']);

        return $this->respond($usuario, 200);
    }

    // Mostrar formulario para agregar un usuario (opcional)
    public function create()
    {
        return view('usuario/create');
    }

    // Guardar un nuevo usuario
    public function store()
    {
        $validation = \Config\Services::validation();

        // Reglas de validación
        if ($this->validate([
            'nombre' => 'required|max_length[100]',
            'email' => 'required|valid_email|is_unique[usuarios.email]',
            'password' => 'required|min_length[8]',
        ])) {
            $this->usuarioModel->save([
                'nombre' => $this->request->getPost('nombre'),
                'email' => $this->request->getPost('email'),
                'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'estado' => $this->request->getPost('estado'),
                'usuario_creacion' => 1,
                'usuario_actualizacion' => 1,
            ]);

            return redirect()->to('/usuario');
        } else {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
    }

    // Actualizar un usuario existente
    public function update($id = null)
    {
        $validation = \Config\Services::validation();

        // Reglas de validación
        if ($this->validate([
            'nombre' => 'required|max_length[100]',
            'email' => "required|valid_email|is_unique[usuarios.email,id,{$id}]",
            'password' => 'permit_empty|min_length[8]',
        ])) {
            $data = [
                'nombre' => $this->request->getPost('nombre'),
                'email' => $this->request->getPost('email'),
                'estado' => $this->request->getPost('estado'),
                'usuario_actualizacion' => 1,
            ];

            if ($this->request->getPost('password')) {
                $data['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
            }

            $this->usuarioModel->update($id, $data);

            return redirect()->to('/usuario');
        } else {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
    }

    // Eliminar un usuario
    #[OA\Delete(
        path: '/api/usuarios/{id}',
        summary: 'Eliminar usuario',
        tags: ['Usuarios'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuario eliminado',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string')])
            ),
            new OA\Response(response: 404, description: 'Usuario no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function delete($id = null)
    {
        $usuario = $this->usuarioModel->find($id);
        if (!$usuario) {
            return $this->failNotFound('Usuario no encontrado');
        }

        $this->usuarioModel->delete($id);
        return $this->respondDeleted(['message' => 'Usuario eliminado correctamente']);
    }

    // Activar o desactivar un usuario
    #[OA\Patch(
        path: '/api/usuarios/{id}/estado',
        summary: 'Activar o desactivar usuario',
        description: 'Solo para los tipos de usuario de usuarios.tiposAdmin (por defecto Superadministrador y Administrador). '
            . 'Un usuario no puede cambiar su propio estado y solo un Superadministrador puede cambiar el de otro Superadministrador. '
            . 'Al desactivar se cierran todas las sesiones del usuario, así que si se reactiva debe iniciar sesión de nuevo.',
        tags: ['Usuarios'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['estadoUsuario'],
                properties: [
                    new OA\Property(property: 'estadoUsuario', type: 'integer', enum: [0, 1], description: '1 = activo, 0 = inactivo', example: 0),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Estado actualizado',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Usuario desactivado correctamente'),
                    new OA\Property(property: 'usuario', ref: '#/components/schemas/Usuario'),
                ])
            ),
            new OA\Response(response: 400, description: 'estadoUsuario inválido', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 401, description: 'Token ausente, inválido o vencido', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 403, description: 'Sin permiso para cambiar el estado de este usuario', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Usuario no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function cambiarEstado($id = null)
    {
        try {
            $idActor = (int) JwtAuth::actual($this->request)->sub;
        } catch (RuntimeException $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        $input = $this->request->getJSON(true);
        if (! is_array($input) || ! $this->validateData($input, ['estadoUsuario' => 'required|in_list[0,1]'], [
            'estadoUsuario' => [
                'required' => 'estadoUsuario es obligatorio',
                'in_list'  => 'estadoUsuario debe ser 0 (inactivo) o 1 (activo)',
            ],
        ])) {
            return $this->failValidationErrors(is_array($input) ? $this->validator->getErrors() : ['estadoUsuario' => 'Enviar un JSON con estadoUsuario']);
        }
        $estado = (int) $input['estadoUsuario'];

        $objetivo = $this->autorizarSobre($idActor, (int) $id, 'cambiar el estado', 'No puede cambiar el estado de su propio usuario');
        if (! is_array($objetivo)) {
            return $objetivo;
        }

        $this->usuarioModel->update($objetivo['idUsuario'], [
            'estadoUsuario'        => $estado,
            'fechaActualizacion'   => date('Y-m-d H:i:s'),
            'usuarioActualizacion' => $idActor,
        ]);

        // Al desactivar se cierran sus sesiones: si se reactiva, sus tokens viejos no vuelven a servir
        if ($estado === 0) {
            (new SesionModel())->cerrarTodas((int) $objetivo['idUsuario'], 'usuario-desactivado');
        }

        $usuario = $this->usuarioModel->find($objetivo['idUsuario']);
        unset($usuario['password']);

        return $this->respond([
            'message' => $estado === 1 ? 'Usuario activado correctamente' : 'Usuario desactivado correctamente',
            'usuario' => $usuario,
        ], 200);
    }

    // Cerrar todas las sesiones de otro usuario
    #[OA\Post(
        path: '/api/usuarios/{id}/cerrar-sesiones',
        summary: 'Cerrar las sesiones de un usuario',
        description: 'Invalida todos los tokens del usuario en todos sus dispositivos, sin desactivarlo: puede volver a iniciar sesión. '
            . 'Mismos permisos que activar/desactivar: solo los tipos de usuarios.tiposAdmin, y solo un Superadministrador puede hacerlo con otro Superadministrador. '
            . 'Para cerrar las propias sesiones usar POST /api/auth/logout-all.',
        tags: ['Usuarios'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sesiones cerradas',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'Se cerraron todas las sesiones del usuario')])
            ),
            new OA\Response(response: 401, description: 'Token ausente, inválido o vencido', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 403, description: 'Sin permiso para cerrar las sesiones de este usuario', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Usuario no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function cerrarSesiones($id = null)
    {
        try {
            $idActor = (int) JwtAuth::actual($this->request)->sub;
        } catch (RuntimeException $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        $objetivo = $this->autorizarSobre($idActor, (int) $id, 'cerrar las sesiones', 'Para cerrar sus propias sesiones use POST /api/auth/logout-all');
        if (! is_array($objetivo)) {
            return $objetivo;
        }

        (new SesionModel())->cerrarTodas((int) $objetivo['idUsuario'], 'admin');

        return $this->respond(['message' => 'Se cerraron todas las sesiones del usuario'], 200);
    }

    /**
     * Verifica que el actor pueda administrar al usuario $id. Devuelve el usuario
     * objetivo, o la respuesta de error (403/404) si no está permitido.
     *
     * @param string $accion       para los mensajes, p. ej. "cambiar el estado"
     * @param string $mensajePropio mensaje si el actor intenta hacerlo sobre sí mismo
     *
     * @return array|\CodeIgniter\HTTP\ResponseInterface
     */
    private function autorizarSobre(int $idActor, int $id, string $accion, string $mensajePropio)
    {
        $actor = $this->usuarioModel->conTipo($idActor);

        // El permiso se revisa antes de buscar al usuario, para no revelar qué ids existen
        if (! in_array($actor['descripcionTipoUsuario'] ?? null, self::tiposAdmin(), true)) {
            return $this->failForbidden("No tiene permiso para {$accion} de otros usuarios");
        }

        $objetivo = $this->usuarioModel->conTipo($id);
        if (! $objetivo) {
            return $this->failNotFound('Usuario no encontrado');
        }

        if ((int) $objetivo['idUsuario'] === $idActor) {
            return $this->failForbidden($mensajePropio);
        }

        if ($objetivo['descripcionTipoUsuario'] === 'Superadministrador' && $actor['descripcionTipoUsuario'] !== 'Superadministrador') {
            return $this->failForbidden("Solo un Superadministrador puede {$accion} de otro Superadministrador");
        }

        return $objetivo;
    }

    // Tipos de usuario que pueden administrar a otros usuarios (descripcionTipoUsuario)
    private static function tiposAdmin(): array
    {
        return array_map('trim', explode(',', (string) env('usuarios.tiposAdmin', 'Superadministrador,Administrador')));
    }
}