<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index');

// Documentación Swagger
// http://localhost:8080/docs              ---> Swagger UI
// http://localhost:8080/docs/openapi.json ---> Especificación OpenAPI
$routes->get('docs', 'SwaggerController::index');
$routes->get('docs/openapi.json', 'SwaggerController::json');

// Autenticación (JWT)
// http://localhost:8080/api/auth/login ---> POST (correoUsuario, password) devuelve el token
// http://localhost:8080/api/auth/me    ---> GET  (Authorization: Bearer <token>)
// http://localhost:8080/api/auth/refresh ---> POST (Authorization: Bearer <token>) devuelve un token nuevo
// http://localhost:8080/api/auth/logout  ---> POST (Authorization: Bearer <token>) cierra la sesión
// http://localhost:8080/api/auth/logout-all ---> POST (Authorization: Bearer <token>) cierra todas las sesiones del usuario
// http://localhost:8080/api/auth/sessions   ---> GET  (Authorization: Bearer <token>) lista las sesiones activas del usuario
// http://localhost:8080/api/auth/sessions/{idSesion} ---> DELETE cierra una sesión específica del usuario
// http://localhost:8080/api/auth/change-password ---> POST (passwordActual, passwordNuevo, passwordConfirmacion) cambia la contraseña
$routes->group('api/auth', ['namespace' => 'App\Controllers\API'], function ($routes) {
    $routes->post('login', 'AuthController::login');
    $routes->get('me', 'AuthController::me', ['filter' => 'jwt']);
    // Sin filtro jwt: el controlador valida el token y acepta vencidos dentro de jwt.refresh_ttl
    $routes->post('refresh', 'AuthController::refresh');
    // Igual que refresh: acepta tokens vencidos dentro de jwt.refresh_ttl
    $routes->post('logout', 'AuthController::logout');
    $routes->post('logout-all', 'AuthController::logoutAll');
    $routes->get('sessions', 'AuthController::sessions', ['filter' => 'jwt']);
    $routes->delete('sessions/(:num)', 'AuthController::closeSession/$1', ['filter' => 'jwt']);
    $routes->post('change-password', 'AuthController::changePassword', ['filter' => 'jwt']);
});

$routes->group('api', ['namespace' => 'App\Controllers\API', 'filter' => 'jwt'], function ($routes) {
    //http://localhost:8080/api/postulantes ---> GET
    $routes->get('postulantes', 'PostulanteController::index');
});

$routes->group('api', ['namespace' => 'App\Controllers\API', 'filter' => 'jwt'], function ($routes) {
    // Ruta para obtener todas las competencias
    // http://localhost:8080/api/competencias
    $routes->get('competencias', 'CompetenciaController::index');
    
    // Ruta para obtener una competencia por ID
    // http://localhost:8080/api/competencias/{id}
    $routes->get('competencias/(:num)', 'CompetenciaController::show/$1');
    
    // Ruta para crear una nueva competencia
    // http://localhost:8080/api/competencias (POST)
    $routes->post('competencias', 'CompetenciaController::create');
});

$routes->group('api', ['namespace' => 'App\Controllers\API', 'filter' => 'jwt'], function ($routes) {
    // http://localhost:8080/api/unidades ---> GET (Listado de unidades)
    $routes->get('unidades', 'UnidadController::index');
    
    // http://localhost:8080/api/unidades/(:num) ---> GET (Unidad específica por ID)
    $routes->get('unidades/(:num)', 'UnidadController::show/$1');

    // http://localhost:8080/api/unidades/reporte ---> GET (Reporte completo de unidades)
    $routes->get('unidades/reporte', 'UnidadController::reporte');

    // http://localhost:8080/api/unidades ---> POST (Crear nueva unidad)
    $routes->post('unidades', 'UnidadController::create');
});

$routes->group('api', ['namespace' => 'App\Controllers\API', 'filter' => 'jwt'], function ($routes) {
    $routes->get('alumnos', 'AlumnoController::index');
    $routes->get('alumnos/(:num)', 'AlumnoController::show/$1');
    $routes->delete('alumnos/(:num)', 'AlumnoController::delete/$1');

    $routes->get('anios-escolares', 'AnioEscolarController::index');
    $routes->get('anios-escolares/(:num)', 'AnioEscolarController::show/$1');
    $routes->delete('anios-escolares/(:num)', 'AnioEscolarController::delete/$1');

    $routes->get('anios-postulantes', 'AnioPostulanteController::index');
    $routes->get('anios-postulantes/(:num)', 'AnioPostulanteController::show/$1');
    $routes->delete('anios-postulantes/(:num)', 'AnioPostulanteController::delete/$1');

    $routes->get('apoderados', 'ApoderadoController::index');
    $routes->get('apoderados/(:num)', 'ApoderadoController::show/$1');
    $routes->delete('apoderados/(:num)', 'ApoderadoController::delete/$1');

    $routes->get('areas', 'AreaController::index');
    $routes->get('areas/(:num)', 'AreaController::show/$1');
    $routes->put('areas/(:num)', 'AreaController::update/$1');
    $routes->delete('areas/(:num)', 'AreaController::delete/$1');

    $routes->get('cursos', 'CursoController::index');

    $routes->get('cursos-grados', 'CursoGradoController::index');
    $routes->post('cursos-grados', 'CursoGradoController::create');

    $routes->get('grados', 'GradoController::index');
    $routes->get('grados/(:num)', 'GradoController::show/$1');
    $routes->delete('grados/(:num)', 'GradoController::delete/$1');

    $routes->get('niveles', 'NivelController::index');
    $routes->get('niveles/(:num)', 'NivelController::show/$1');
    $routes->delete('niveles/(:num)', 'NivelController::delete/$1');

    $routes->get('tipos-usuario', 'TipoUsuarioController::index');
    $routes->get('tipos-usuario/(:num)', 'TipoUsuarioController::show/$1');
    $routes->delete('tipos-usuario/(:num)', 'TipoUsuarioController::delete/$1');

    $routes->get('usuarios', 'UsuarioController::index');
    $routes->get('usuarios/(:num)', 'UsuarioController::show/$1');
    $routes->delete('usuarios/(:num)', 'UsuarioController::delete/$1');
    // http://localhost:8080/api/usuarios/{id}/estado ---> PATCH {"estadoUsuario": 0|1} (solo administradores)
    $routes->patch('usuarios/(:num)/estado', 'UsuarioController::cambiarEstado/$1');
    // http://localhost:8080/api/usuarios/{id}/cerrar-sesiones ---> POST (solo administradores)
    $routes->post('usuarios/(:num)/cerrar-sesiones', 'UsuarioController::cerrarSesiones/$1');
});
