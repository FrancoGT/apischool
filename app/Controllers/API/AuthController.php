<?php

namespace App\Controllers\API;

use App\Libraries\JwtAuth;
use App\Libraries\LimiteIntentos;
use App\Models\SesionModel;
use App\Models\UsuarioModel;
use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;
use RuntimeException;

class AuthController extends ResourceController
{
    // Hash Argon2id de una contraseña aleatoria, solo para igualar tiempos en el login
    private const HASH_FICTICIO = '$argon2id$v=19$m=65536,t=4,p=1$OHN6Vk1BUno0eFNsVGNuSw$OzwgMH5N7l9+u1ybHUtsVyV2enrvq3XB0xARsDWWtxs';

    protected $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
    }

    // Iniciar sesión y obtener un token JWT
    #[OA\Post(
        path: '/api/auth/login',
        summary: 'Iniciar sesión',
        description: 'Valida correo y contraseña y devuelve un token JWT. Usar el token en el botón "Authorize" como Bearer.',
        tags: ['Autenticación'],
        security: [],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['correoUsuario', 'password'],
                properties: [
                    new OA\Property(property: 'correoUsuario', type: 'string', format: 'email', example: 'superadmin@apischool.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'SuperAdmin123*'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Autenticación correcta', content: new OA\JsonContent(ref: '#/components/schemas/LoginResponse')),
            new OA\Response(response: 400, description: 'Datos inválidos', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 401, description: 'Credenciales incorrectas o usuario inactivo', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 429, description: 'Demasiados intentos: 5 por IP y correo, o 20 por IP, cada pocos minutos (ver cabecera Retry-After)', headers: [new OA\Header(header: 'Retry-After', description: 'Segundos hasta el próximo intento', schema: new OA\Schema(type: 'integer'))], content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function login()
    {
        $input = $this->datosEntrada();

        if (! $this->validateData($input, [
            'correoUsuario' => 'required|valid_email',
            'password'      => 'required',
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Límite por IP (muchos correos) y por IP + correo (una cuenta)
        $ip          = $this->request->getIPAddress();
        $claveCorreo = $ip . '|' . $input['correoUsuario'];
        $espera      = LimiteIntentos::consumir('loginIp', $ip) ?? LimiteIntentos::consumir('login', $claveCorreo);
        if ($espera !== null) {
            return $this->demasiadosIntentos($espera);
        }

        $usuario = $this->usuarioModel->where('correoUsuario', $input['correoUsuario'])->first();

        // Si el correo no existe se verifica igual contra un hash ficticio, para que el
        // tiempo de respuesta no revele qué correos están registrados
        $hash = $usuario['password'] ?? self::HASH_FICTICIO;
        if (! password_verify($input['password'], $hash) || ! $usuario) {
            return $this->failUnauthorized('Correo o contraseña incorrectos');
        }

        if ((int) $usuario['estadoUsuario'] !== 1) {
            return $this->failUnauthorized('Usuario inactivo');
        }

        // Login correcto: los intentos fallidos previos de este correo no cuentan más
        LimiteIntentos::reiniciar('login', $claveCorreo);

        return $this->respond($this->emitirToken($usuario), 200);
    }

    // Datos del usuario autenticado
    #[OA\Get(
        path: '/api/auth/me',
        summary: 'Usuario autenticado',
        tags: ['Autenticación'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Usuario autenticado', content: new OA\JsonContent(ref: '#/components/schemas/Usuario')),
            new OA\Response(response: 401, description: 'Token ausente, inválido o expirado, o usuario inactivo', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function me()
    {
        try {
            $payload = JwtAuth::fromRequest($this->request);
        } catch (RuntimeException $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        $usuario = $this->usuarioModel->find($payload->sub);
        if (! $usuario) {
            return $this->failUnauthorized('Usuario no encontrado');
        }
        unset($usuario['password']);

        return $this->respond($usuario, 200);
    }

    // Renovar el token JWT (vigente o vencido hace menos de jwt.refresh_ttl)
    #[OA\Post(
        path: '/api/auth/refresh',
        summary: 'Refrescar token',
        description: 'Recibe un token vigente, o vencido hace menos de 24 horas (jwt.refresh_ttl), y devuelve uno nuevo con la expiración renovada. La sesión dura como máximo 7 días desde el login (jwt.session_ttl); después hay que iniciar sesión otra vez. Rechaza el refresh si el usuario fue desactivado.',
        tags: ['Autenticación'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Token renovado', content: new OA\JsonContent(ref: '#/components/schemas/LoginResponse')),
            new OA\Response(response: 401, description: 'Token ausente, inválido o vencido hace más de jwt.refresh_ttl, sesión de más de 7 días, o usuario inactivo', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function refresh()
    {
        try {
            $payload = JwtAuth::fromRequest($this->request, true);
        } catch (RuntimeException $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        // fromRequest() ya verificó que el usuario exista y esté activo
        $usuario = $this->usuarioModel->find($payload->sub);
        if (! $usuario) {
            return $this->failUnauthorized('Usuario no encontrado');
        }

        if (JwtAuth::sesionVencida($payload)) {
            return $this->failUnauthorized('La sesión expiró, inicie sesión nuevamente');
        }

        // Se conservan la sesión y el momento del login original
        return $this->respond($this->emitirToken($usuario, $payload->sid, JwtAuth::authTime($payload)), 200);
    }

    // Cerrar la sesión: invalida el token actual y todos los que se obtengan al refrescarlo
    #[OA\Post(
        path: '/api/auth/logout',
        summary: 'Cerrar sesión',
        description: 'Revoca la sesión del token enviado. Ese token y los que se hayan obtenido con refresh dejan de funcionar. Acepta también tokens vencidos hace menos de 24 horas (jwt.refresh_ttl).',
        tags: ['Autenticación'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sesión cerrada',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'Sesión cerrada correctamente')])
            ),
            new OA\Response(response: 401, description: 'Token ausente, inválido, vencido o sesión ya cerrada, o usuario inactivo', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function logout()
    {
        try {
            $payload = JwtAuth::fromRequest($this->request, true);
        } catch (RuntimeException $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        (new SesionModel())->cerrar($payload->sid, 'logout');

        return $this->respond(['message' => 'Sesión cerrada correctamente'], 200);
    }

    // Cerrar todas las sesiones del usuario (en todos los dispositivos)
    #[OA\Post(
        path: '/api/auth/logout-all',
        summary: 'Cerrar todas las sesiones',
        description: 'Invalida todos los tokens del usuario autenticado, incluido el enviado, en todos los dispositivos. Después hay que volver a iniciar sesión. Acepta también tokens vencidos hace menos de 24 horas (jwt.refresh_ttl).',
        tags: ['Autenticación'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sesiones cerradas',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'Se cerraron todas las sesiones')])
            ),
            new OA\Response(response: 401, description: 'Token ausente, inválido, vencido o sesión ya cerrada, o usuario inactivo', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function logoutAll()
    {
        try {
            $payload = JwtAuth::fromRequest($this->request, true);
        } catch (RuntimeException $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        (new SesionModel())->cerrarTodas((int) $payload->sub, 'logout-all');

        return $this->respond(['message' => 'Se cerraron todas las sesiones'], 200);
    }

    // Listar las sesiones activas del usuario autenticado
    #[OA\Get(
        path: '/api/auth/sessions',
        summary: 'Sesiones activas',
        description: 'Lista las sesiones del usuario autenticado que todavía se pueden usar (no cerradas, dentro de los 7 días y con un token vigente o refrescable), de la más reciente a la más antigua. "actual" marca la sesión del token enviado. ultimaActividad se actualiza cada 5 minutos como máximo.',
        tags: ['Autenticación'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sesiones activas',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'total', type: 'integer', example: 2),
                    new OA\Property(property: 'sesiones', type: 'array', items: new OA\Items(ref: '#/components/schemas/Sesion')),
                ])
            ),
            new OA\Response(response: 401, description: 'Token ausente, inválido, vencido o sesión cerrada, o usuario inactivo', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function sessions()
    {
        try {
            $payload = JwtAuth::actual($this->request);
        } catch (RuntimeException $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        $sesiones = array_map(static fn (array $s) => [
            'idSesion'        => (int) $s['idSesion'],
            'actual'          => hash_equals($s['sid'], $payload->sid),
            'dispositivo'     => $s['dispositivo'],
            'ipInicio'        => $s['ipInicio'],
            'ultimaIp'        => $s['ultimaIp'],
            'fechaInicio'     => $s['fechaInicio'],
            'ultimaActividad' => $s['ultimaActividad'],
            'fechaExpiracion' => $s['fechaExpiracion'],
        ], (new SesionModel())->activas((int) $payload->sub));

        return $this->respond(['total' => count($sesiones), 'sesiones' => $sesiones], 200);
    }

    // Cerrar una sesión específica del usuario autenticado (p. ej. un celular perdido)
    #[OA\Delete(
        path: '/api/auth/sessions/{idSesion}',
        summary: 'Cerrar una sesión',
        description: 'Cierra una de las sesiones del usuario autenticado, usando el idSesion de GET /api/auth/sessions. Los tokens de esa sesión dejan de funcionar y las demás sesiones siguen abiertas. Si es la sesión actual equivale a un logout.',
        tags: ['Autenticación'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'idSesion', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sesión cerrada',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Sesión cerrada correctamente'),
                    new OA\Property(property: 'actual', type: 'boolean', description: 'true si se cerró la sesión del token enviado (hay que volver a iniciar sesión)'),
                ])
            ),
            new OA\Response(response: 401, description: 'Token ausente, inválido, vencido o sesión cerrada, o usuario inactivo', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'La sesión no existe, es de otro usuario o ya estaba cerrada', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function closeSession($idSesion = null)
    {
        try {
            $payload = JwtAuth::actual($this->request);
        } catch (RuntimeException $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        $sesiones = new SesionModel();
        $actual   = (int) ($sesiones->where('sid', $payload->sid)->first()['idSesion'] ?? 0) === (int) $idSesion;

        // Mismo 404 para sesiones ajenas, inexistentes o ya cerradas: no revela sesiones de otros
        if (! $sesiones->cerrarDeUsuario((int) $idSesion, (int) $payload->sub, $actual ? 'logout' : 'otro-dispositivo')) {
            return $this->failNotFound('Sesión no encontrada');
        }

        return $this->respond(['message' => 'Sesión cerrada correctamente', 'actual' => $actual], 200);
    }

    // Cambiar la contraseña del usuario autenticado
    #[OA\Post(
        path: '/api/auth/change-password',
        summary: 'Cambiar contraseña',
        description: 'Verifica la contraseña actual y guarda la nueva. Cierra todas las sesiones del usuario (en todos los dispositivos) y devuelve un token nuevo para seguir conectado en el actual.',
        tags: ['Autenticación'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['passwordActual', 'passwordNuevo', 'passwordConfirmacion'],
                properties: [
                    new OA\Property(property: 'passwordActual', type: 'string', format: 'password', example: 'SuperAdmin123*'),
                    new OA\Property(property: 'passwordNuevo', type: 'string', format: 'password', minLength: 8, example: 'NuevaClave456*'),
                    new OA\Property(property: 'passwordConfirmacion', type: 'string', format: 'password', example: 'NuevaClave456*'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Contraseña actualizada; incluye el token nuevo',
                content: new OA\JsonContent(allOf: [
                    new OA\Schema(properties: [new OA\Property(property: 'message', type: 'string', example: 'Contraseña actualizada correctamente')]),
                    new OA\Schema(ref: '#/components/schemas/LoginResponse'),
                ])
            ),
            new OA\Response(response: 400, description: 'Datos inválidos o contraseña actual incorrecta', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 401, description: 'Token ausente, inválido, vencido o sesión cerrada, o usuario inactivo', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 429, description: 'Demasiados intentos: 5 por usuario cada 5 minutos (ver cabecera Retry-After)', headers: [new OA\Header(header: 'Retry-After', description: 'Segundos hasta el próximo intento', schema: new OA\Schema(type: 'integer'))], content: new OA\JsonContent(ref: '#/components/schemas/Error')),
        ],
    )]
    public function changePassword()
    {
        try {
            $payload = JwtAuth::fromRequest($this->request);
        } catch (RuntimeException $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        $input = $this->datosEntrada();

        if (! $this->validateData($input, [
            'passwordActual'       => 'required',
            'passwordNuevo'        => 'required|min_length[8]|max_length[72]|differs[passwordActual]',
            'passwordConfirmacion' => 'required|matches[passwordNuevo]',
        ], [
            'passwordActual' => [
                'required' => 'La contraseña actual es obligatoria',
            ],
            'passwordNuevo' => [
                'required'   => 'La contraseña nueva es obligatoria',
                'min_length' => 'La contraseña nueva debe tener al menos 8 caracteres',
                'max_length' => 'La contraseña nueva no puede tener más de 72 caracteres',
                'differs'    => 'La contraseña nueva debe ser distinta de la actual',
            ],
            'passwordConfirmacion' => [
                'required' => 'La confirmación de la contraseña es obligatoria',
                'matches'  => 'La confirmación no coincide con la contraseña nueva',
            ],
        ])) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $usuario = $this->usuarioModel->find($payload->sub);

        $espera = LimiteIntentos::consumir('changePassword', (string) $usuario['idUsuario']);
        if ($espera !== null) {
            return $this->demasiadosIntentos($espera);
        }

        if (! password_verify($input['passwordActual'], $usuario['password'])) {
            // 400 y no 401: el token es válido, el error está en el dato enviado
            return $this->failValidationErrors(['passwordActual' => 'La contraseña actual es incorrecta']);
        }

        LimiteIntentos::reiniciar('changePassword', (string) $usuario['idUsuario']);

        $this->usuarioModel->update($usuario['idUsuario'], [
            'password'             => password_hash($input['passwordNuevo'], PASSWORD_ARGON2ID),
            'fechaActualizacion'   => date('Y-m-d H:i:s'),
            'usuarioActualizacion' => (int) $usuario['idUsuario'],
        ]);

        // Si alguien más tenía un token, deja de servir; este dispositivo recibe uno nuevo
        (new SesionModel())->cerrarTodas((int) $usuario['idUsuario'], 'cambio-password');

        return $this->respond(
            ['message' => 'Contraseña actualizada correctamente'] + $this->emitirToken($this->usuarioModel->find($usuario['idUsuario'])),
            200
        );
    }

    // 429 con la cabecera Retry-After (segundos hasta el próximo intento)
    private function demasiadosIntentos(int $espera)
    {
        $this->response->setHeader('Retry-After', (string) $espera);

        return $this->failTooManyRequests("Demasiados intentos, vuelva a intentarlo en {$espera} segundos");
    }

    // Cuerpo de la petición: acepta JSON o form-data (un JSON mal formado se trata como vacío)
    private function datosEntrada(): array
    {
        if (str_contains($this->request->getHeaderLine('Content-Type'), 'json')) {
            try {
                return (array) $this->request->getJSON(true);
            } catch (\Throwable $e) {
                return [];
            }
        }

        return $this->request->getPost();
    }

    /**
     * Genera el token y los datos de respuesta comunes de login/refresh/change-password.
     * Sin $sid (login, change-password) registra una sesión nueva en la tabla sesion;
     * con $sid (refresh) renueva la existente.
     */
    private function emitirToken(array $usuario, ?string $sid = null, ?int $authTime = null): array
    {
        $jwt = JwtAuth::encode([
            'sub'           => (int) $usuario['idUsuario'],
            'idTipoUsuario' => (int) $usuario['idTipoUsuario'],
            'correoUsuario' => $usuario['correoUsuario'],
        ], $sid, $authTime);

        $sesiones = new SesionModel();
        $ip       = $this->request->getIPAddress();
        if ($sid === null) {
            $sesiones->crear($jwt['sid'], (int) $usuario['idUsuario'], $jwt['auth_time'], $jwt['exp'], $ip, $this->request->getUserAgent()->getAgentString());
        } else {
            $sesiones->renovar($sid, $jwt['exp'], $ip);
        }

        $this->usuarioModel->update($usuario['idUsuario'], ['ultimaConexion' => date('Y-m-d H:i:s')]);

        unset($usuario['password']);

        return [
            'token'      => $jwt['token'],
            'token_type' => 'Bearer',
            'expires_in' => $jwt['expires_in'],
            'usuario'    => $usuario,
        ];
    }
}
