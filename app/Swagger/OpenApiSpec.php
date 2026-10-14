<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

/**
 * Definición general de la documentación OpenAPI (Swagger) de la API.
 * Los endpoints se documentan con atributos en cada controlador de app/Controllers/API.
 */
// Todas las rutas requieren token JWT, salvo las que definan "security: []" (p. ej. login)
#[OA\OpenApi(openapi: '3.0.3', security: [['bearerAuth' => []]])]
#[OA\Info(
    version: '1.0.0',
    title: 'API School',
    description: 'API REST para sistema de colegio'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Token obtenido en POST /api/auth/login'
)]
#[OA\Tag(name: 'Autenticación', description: 'Inicio de sesión con JWT')]
#[OA\Tag(name: 'Postulantes', description: 'Gestión de postulantes')]
#[OA\Tag(name: 'Competencias', description: 'Gestión de competencias')]
#[OA\Tag(name: 'Unidades', description: 'Gestión de unidades')]
#[OA\Tag(name: 'Alumnos')]
#[OA\Tag(name: 'Años escolares')]
#[OA\Tag(name: 'Años postulantes')]
#[OA\Tag(name: 'Apoderados')]
#[OA\Tag(name: 'Áreas')]
#[OA\Tag(name: 'Cursos')]
#[OA\Tag(name: 'Cursos por grado')]
#[OA\Tag(name: 'Grados')]
#[OA\Tag(name: 'Niveles')]
#[OA\Tag(name: 'Tipos de usuario')]
#[OA\Tag(name: 'Usuarios')]
#[OA\Schema(
    schema: 'Error',
    properties: [
        new OA\Property(property: 'status', type: 'integer', example: 404),
        new OA\Property(property: 'error', type: 'integer', example: 404),
        new OA\Property(property: 'messages', type: 'object', example: ['error' => 'Recurso no encontrado']),
    ]
)]
#[OA\Schema(
    schema: 'LoginResponse',
    properties: [
        new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'),
        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
        new OA\Property(property: 'expires_in', type: 'integer', example: 3600, description: 'Segundos de validez del token'),
        new OA\Property(property: 'usuario', ref: '#/components/schemas/Usuario'),
    ]
)]
#[OA\Schema(
    schema: 'Sesion',
    properties: [
        new OA\Property(property: 'idSesion', type: 'integer', example: 12),
        new OA\Property(property: 'actual', type: 'boolean', description: 'true si es la sesión del token enviado'),
        new OA\Property(property: 'dispositivo', type: 'string', nullable: true, description: 'User-Agent del navegador o app que inició la sesión'),
        new OA\Property(property: 'ipInicio', type: 'string', example: '192.168.1.10'),
        new OA\Property(property: 'ultimaIp', type: 'string', example: '192.168.1.10'),
        new OA\Property(property: 'fechaInicio', type: 'string', format: 'date-time', description: 'Momento del login'),
        new OA\Property(property: 'ultimaActividad', type: 'string', format: 'date-time', description: 'Se actualiza cada 5 minutos como máximo'),
        new OA\Property(property: 'fechaExpiracion', type: 'string', format: 'date-time', description: 'Fin de la sesión (7 días desde el login)'),
    ]
)]
#[OA\Schema(
    schema: 'Postulante',
    properties: [
        new OA\Property(property: 'idPostulante', type: 'integer', example: 1),
        new OA\Property(property: 'nombrePostulante', type: 'string', maxLength: 200),
        new OA\Property(property: 'apellidoPostulante', type: 'string', maxLength: 200),
        new OA\Property(property: 'sexoPostulante', type: 'string', maxLength: 25, nullable: true),
        new OA\Property(property: 'dniPostulante', type: 'string', maxLength: 15, nullable: true),
        new OA\Property(property: 'fechaNacimiento', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'lugarNacimiento', type: 'string', maxLength: 200, nullable: true),
        new OA\Property(property: 'gradoPostulacion', type: 'string', maxLength: 50, description: 'idGrado como texto'),
        new OA\Property(property: 'domicilioPostulante', type: 'string', maxLength: 200, nullable: true),
        new OA\Property(property: 'colegioProcedencia', type: 'string', maxLength: 100, nullable: true),
        new OA\Property(property: 'fechaPostulacion', type: 'string', format: 'date'),
        new OA\Property(property: 'listaApoderados', type: 'string', maxLength: 100, example: '{"idPadre":1,"idMadre":2}'),
        new OA\Property(property: 'estadoPostulante', type: 'integer'),
        new OA\Property(property: 'fechaCreacion', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'fechaActualizacion', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'usuarioCreacion', type: 'integer'),
        new OA\Property(property: 'usuarioActualizacion', type: 'integer'),
    ],
    additionalProperties: true
)]
#[OA\Schema(
    schema: 'Competencia',
    properties: [
        new OA\Property(property: 'idCompetencia', type: 'integer', example: 1),
        new OA\Property(property: 'idUnidad', type: 'integer', example: 1),
        new OA\Property(property: 'descripcionCompetencia', type: 'string', maxLength: 255),
        new OA\Property(property: 'capacidadesCompetencia', type: 'string', nullable: true),
        new OA\Property(property: 'estandarCompetencia', type: 'string', nullable: true),
        new OA\Property(property: 'fechaCreacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'fechaActualizacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'usuarioCreacion', type: 'integer'),
        new OA\Property(property: 'usuarioActualizacion', type: 'integer'),
    ]
)]
#[OA\Schema(
    schema: 'Unidad',
    properties: [
        new OA\Property(property: 'idUnidad', type: 'integer', example: 1),
        new OA\Property(property: 'idBimestre', type: 'integer', nullable: true),
        new OA\Property(property: 'descripcionUnidad', type: 'string', maxLength: 100),
        new OA\Property(property: 'estadoUnidad', type: 'integer', description: 'bit(1)'),
        new OA\Property(property: 'fechaCreacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'fechaActualizacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'usuarioCreacion', type: 'integer'),
        new OA\Property(property: 'usuarioActualizacion', type: 'integer'),
    ]
)]
#[OA\Schema(
    schema: 'Nivel',
    properties: [
        new OA\Property(property: 'idNivel', type: 'integer'),
        new OA\Property(property: 'descripcionNivel', type: 'string', maxLength: 100),
        new OA\Property(property: 'fechaCreacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'fechaActualizacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'usuarioCreacion', type: 'integer'),
        new OA\Property(property: 'usuarioActualizacion', type: 'integer'),
    ]
)]
#[OA\Schema(
    schema: 'TipoUsuario',
    properties: [
        new OA\Property(property: 'idTipoUsuario', type: 'integer'),
        new OA\Property(property: 'descripcionTipoUsuario', type: 'string', maxLength: 100),
        new OA\Property(property: 'fechaCreacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'fechaActualizacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'usuarioCreacion', type: 'integer'),
        new OA\Property(property: 'usuarioActualizacion', type: 'integer'),
    ]
)]
#[OA\Schema(
    schema: 'Area',
    properties: [
        new OA\Property(property: 'idArea', type: 'integer'),
        new OA\Property(property: 'descripcionArea', type: 'string', maxLength: 100),
        new OA\Property(property: 'fechaCreacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'fechaActualizacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'usuarioCreacion', type: 'integer'),
        new OA\Property(property: 'usuarioActualizacion', type: 'integer'),
    ]
)]
#[OA\Schema(
    schema: 'AnioEscolar',
    properties: [
        new OA\Property(property: 'idAnioEscolar', type: 'integer'),
        new OA\Property(property: 'descripcionAnio', type: 'string', maxLength: 25),
        new OA\Property(property: 'estadoAnio', type: 'integer'),
        new OA\Property(property: 'cuotaInicial', type: 'number', format: 'float'),
        new OA\Property(property: 'matriculaInicial', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'pensionInicial', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'matriculaPrimaria', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'pensionPrimaria', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'matriculaSecundaria', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'pensionSecundaria', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'fechaCreacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'fechaActualizacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'usuarioCreacion', type: 'integer'),
        new OA\Property(property: 'usuarioActualizacion', type: 'integer'),
    ]
)]
#[OA\Schema(
    schema: 'Alumno',
    properties: [
        new OA\Property(property: 'idAlumno', type: 'integer'),
        new OA\Property(property: 'estadoSiagie', type: 'integer', nullable: true),
        new OA\Property(property: 'codAlumnoCaja', type: 'string', maxLength: 25, nullable: true),
        new OA\Property(property: 'nombresAlumno', type: 'string', maxLength: 150),
        new OA\Property(property: 'apellidosAlumno', type: 'string', maxLength: 150),
        new OA\Property(property: 'sexoAlumno', type: 'string', maxLength: 25, nullable: true),
        new OA\Property(property: 'dniAlumno', type: 'string', maxLength: 25),
        new OA\Property(property: 'fechaNacimiento', type: 'string', format: 'date'),
        new OA\Property(property: 'direccionAlumno', type: 'string', maxLength: 200, nullable: true),
        new OA\Property(property: 'distritoAlumno', type: 'string', maxLength: 100, nullable: true),
        new OA\Property(property: 'IEPProcedencia', type: 'string', maxLength: 200, nullable: true),
        new OA\Property(property: 'seguroSalud', type: 'string', maxLength: 150, nullable: true),
        new OA\Property(property: 'fechaIngresoVolta', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'nuevoAlumno', type: 'integer', description: 'bit(1)', nullable: true),
        new OA\Property(property: 'numeroEmergencia', type: 'string', maxLength: 25, nullable: true),
        new OA\Property(property: 'enfermedades', type: 'string', maxLength: 200, nullable: true),
        new OA\Property(property: 'fechaCreacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'fechaActualizacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'usuarioCreacion', type: 'integer'),
        new OA\Property(property: 'usuarioActualizacion', type: 'integer'),
    ]
)]
#[OA\Schema(
    schema: 'Usuario',
    properties: [
        new OA\Property(property: 'idUsuario', type: 'integer'),
        new OA\Property(property: 'idTipoUsuario', type: 'integer'),
        new OA\Property(property: 'correoUsuario', type: 'string', maxLength: 150),
        new OA\Property(property: 'nombreUsuario', type: 'string', maxLength: 150),
        new OA\Property(property: 'apellidoUsuario', type: 'string', maxLength: 150),
        new OA\Property(property: 'dniUsuario', type: 'string', maxLength: 20),
        new OA\Property(property: 'estadoUsuario', type: 'integer'),
        new OA\Property(property: 'ultimaConexion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'fechaCreacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'fechaActualizacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'usuarioCreacion', type: 'integer'),
        new OA\Property(property: 'usuarioActualizacion', type: 'integer'),
    ]
)]
#[OA\Schema(
    schema: 'Grado',
    properties: [
        new OA\Property(property: 'idGrado', type: 'integer'),
        new OA\Property(property: 'idNivel', type: 'integer'),
        new OA\Property(property: 'descripcionGrado', type: 'string', maxLength: 150),
        new OA\Property(property: 'fechaCreacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'fechaActualizacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'usuarioCreacion', type: 'integer'),
        new OA\Property(property: 'usuarioActualizacion', type: 'integer'),
    ]
)]
#[OA\Schema(
    schema: 'Curso',
    properties: [
        new OA\Property(property: 'idCurso', type: 'integer'),
        new OA\Property(property: 'idArea', type: 'integer'),
        new OA\Property(property: 'descripcionCurso', type: 'string', maxLength: 100),
        new OA\Property(property: 'estadoCurso', type: 'integer', nullable: true),
        new OA\Property(property: 'fechaCreacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'fechaActualizacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'usuarioCreacion', type: 'integer'),
        new OA\Property(property: 'usuarioActualizacion', type: 'integer'),
    ]
)]
#[OA\Schema(
    schema: 'AnioPostulante',
    properties: [
        new OA\Property(property: 'idAnioPostulante', type: 'integer'),
        new OA\Property(property: 'idAnioEscolar', type: 'integer'),
        new OA\Property(property: 'idPostulante', type: 'integer'),
        new OA\Property(property: 'fechaCreacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'fechaActualizacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'usuarioCreacion', type: 'integer'),
        new OA\Property(property: 'usuarioActualizacion', type: 'integer'),
    ]
)]
#[OA\Schema(
    schema: 'Apoderado',
    properties: [
        new OA\Property(property: 'idApoderado', type: 'integer'),
        new OA\Property(property: 'idUsuario', type: 'integer', nullable: true),
        new OA\Property(property: 'nombreApoderado', type: 'string', maxLength: 150),
        new OA\Property(property: 'apellidoApoderado', type: 'string', maxLength: 150),
        new OA\Property(property: 'dniApoderado', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'convivenciaAlumno', type: 'string', maxLength: 25, nullable: true),
        new OA\Property(property: 'fechaNacimiento', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'tipoApoderado', type: 'string', maxLength: 25),
        new OA\Property(property: 'correoApoderado', type: 'string', maxLength: 150),
        new OA\Property(property: 'celularApoderado', type: 'string', maxLength: 15, nullable: true),
        new OA\Property(property: 'dependenciaApoderado', type: 'string', maxLength: 50, nullable: true),
        new OA\Property(property: 'centroLaboral', type: 'string', maxLength: 150, nullable: true),
        new OA\Property(property: 'telefonoTrabajo', type: 'string', maxLength: 15, nullable: true),
        new OA\Property(property: 'ingresoMensual', type: 'string', maxLength: 15, nullable: true),
        new OA\Property(property: 'gradoInstruccion', type: 'string', maxLength: 50, nullable: true),
        new OA\Property(property: 'profesionApoderado', type: 'string', maxLength: 50, nullable: true),
        new OA\Property(property: 'listaAlumnos', type: 'string', maxLength: 100, nullable: true),
        new OA\Property(property: 'cuentaCreada', type: 'integer'),
        new OA\Property(property: 'fechaCreacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'fechaActualizacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'usuarioCreacion', type: 'integer'),
        new OA\Property(property: 'usuarioActualizacion', type: 'integer'),
    ]
)]
#[OA\Schema(
    schema: 'CursoGrado',
    properties: [
        new OA\Property(property: 'idCursoGrado', type: 'integer'),
        new OA\Property(property: 'idCurso', type: 'integer'),
        new OA\Property(property: 'idGrado', type: 'integer'),
        new OA\Property(property: 'fechaCreacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'fechaActualizacion', type: 'string', format: 'date-time'),
        new OA\Property(property: 'usuarioCreacion', type: 'integer'),
        new OA\Property(property: 'usuarioActualizacion', type: 'integer'),
    ]
)]
class OpenApiSpec
{
}
