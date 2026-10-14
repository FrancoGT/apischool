#API REST para sistema de colegio

##Comando para debugear:

```
php spark serve
```

##Documentación Swagger:

- Swagger UI: `http://localhost:8080/docs`
- Especificación OpenAPI (JSON): `http://localhost:8080/docs/openapi.json`

Los endpoints se documentan con atributos `#[OA\...]` (zircote/swagger-php) en `app/Controllers/API`; la información general y los schemas están en `app/Swagger/OpenApiSpec.php`.

##Usuario superadmin:

```
php spark db:seed SuperAdminSeeder
```

Crea el tipo de usuario `Superadministrador` y el usuario `superadmin@apischool.com` / `SuperAdmin123*` (configurables con `superadmin.email` y `superadmin.password` en `.env`).

##Seguridad (autenticación JWT):

Todas las rutas del grupo `api` requieren un token JWT en la cabecera `Authorization: Bearer <token>` (filtro `jwt` en `app/Config/Routes.php`). La única ruta pública es `POST /api/auth/login`. En Swagger, usar el botón **Authorize** y pegar el token.

###Endpoints de autenticación

| Endpoint | Token | Qué hace |
|---|---|---|
| `POST /api/auth/login` | No | Recibe `correoUsuario` y `password` (JSON o form-data) y devuelve `token`, `token_type`, `expires_in` y `usuario`. Crea una sesión nueva. |
| `GET /api/auth/me` | Vigente | Devuelve el usuario autenticado. |
| `POST /api/auth/refresh` | Vigente o vencido hace menos de `jwt.refresh_ttl` | Devuelve un token nuevo de la **misma sesión**. |
| `POST /api/auth/logout` | Vigente o vencido hace menos de `jwt.refresh_ttl` | Cierra la sesión del token: ese token y todos los obtenidos con refresh dejan de funcionar. |
| `POST /api/auth/logout-all` | Vigente o vencido hace menos de `jwt.refresh_ttl` | Cierra **todas** las sesiones del usuario, en todos los dispositivos. |
| `GET /api/auth/sessions` | Vigente | Lista las sesiones activas del usuario (dispositivo, IP, inicio, última actividad) y marca cuál es la actual. |
| `DELETE /api/auth/sessions/{idSesion}` | Vigente | Cierra una sesión específica del usuario (por ejemplo, un celular perdido) sin cerrar las demás. Responde `404` si la sesión no existe, es de otro usuario o ya estaba cerrada. |
| `POST /api/auth/change-password` | Vigente | Recibe `passwordActual`, `passwordNuevo` y `passwordConfirmacion`. Cambia la contraseña, cierra todas las sesiones y devuelve un token nuevo para seguir conectado en el dispositivo actual. |

###Administración de usuarios

| Endpoint | Qué hace |
|---|---|
| `PATCH /api/usuarios/{id}/estado` | Con `{"estadoUsuario": 0}` desactiva al usuario y con `{"estadoUsuario": 1}` lo activa. |
| `POST /api/usuarios/{id}/cerrar-sesiones` | Cierra todas las sesiones del usuario en todos sus dispositivos, **sin desactivarlo**: puede volver a iniciar sesión. Útil si perdió un dispositivo o se sospecha que le robaron el token. |

Ambos tienen los mismos permisos:

- Solo pueden usarlos los tipos de usuario listados en `usuarios.tiposAdmin` (por defecto `Superadministrador` y `Administrador`). Los demás reciben `403`, sin importar si el usuario objetivo existe.
- Nadie puede usarlos sobre sí mismo: para no dejar el sistema sin administradores por error, y porque para las sesiones propias ya existe `POST /api/auth/logout-all`.
- Solo un `Superadministrador` puede usarlos sobre otro `Superadministrador`.

Al desactivar, el usuario queda bloqueado de inmediato en todas las peticiones y además se cierran todas sus sesiones (tabla `sesion`), así que si se lo reactiva sus tokens viejos no vuelven a funcionar: debe iniciar sesión de nuevo. El cambio de estado guarda quién lo hizo en `usuarioActualizacion`.

Respuestas de error: `400` datos inválidos (incluye contraseña actual incorrecta en change-password), `401` token ausente, inválido, vencido, sesión cerrada o usuario inactivo, `403` sin permiso, `404` no encontrado, `429` demasiados intentos. Todas con el formato `{"status", "error", "messages": {"error": "..."}}`.

###Contenido del token

Los tokens se firman con HMAC-SHA256 (`HS256`) usando `jwt.secret`. La firma impide modificarlos, pero **no están cifrados**: cualquiera puede leer su contenido, así que no deben llevar datos sensibles.

| Claim | Contenido |
|---|---|
| `sub` | `idUsuario` |
| `idTipoUsuario`, `correoUsuario` | Datos del usuario al momento de emitir el token |
| `iat` | Momento en que se emitió este token |
| `exp` | Vencimiento de este token |
| `auth_time` | Momento del login original. Se conserva en cada refresh |
| `sid` | Identificador aleatorio de la sesión. Se conserva en cada refresh y es lo que permite cerrarla con logout |

###Ciclo de vida de una sesión

```
login ──► token (1 h) ──► refresh ──► token (1 h) ──► refresh ──► ... ──► fin de la sesión (7 días desde el login)
             │                           │
             └─ si vence, se puede refrescar durante 24 h más; después hay que hacer login
```

- **`jwt.ttl`** (1 hora): validez de cada token. Pasado ese tiempo las rutas responden 401 y el frontend debe llamar a `/refresh`. Lo ideal es refrescar unos minutos antes de que venza (`expires_in`).
- **`jwt.refresh_ttl`** (24 horas): margen después del vencimiento en el que el token todavía sirve para `/refresh`, `/logout` y `/logout-all`, aunque ya no sirva para las demás rutas. Permite que una pestaña que estuvo cerrada recupere la sesión sin pedir la contraseña.
- **`jwt.session_ttl`** (7 días): duración máxima de la sesión desde el login, aunque se refresque continuamente. Ningún token vence después de ese límite (si a la sesión le quedan 30 minutos, el refresh entrega un token de 30 minutos), y pasado el límite `/refresh` responde 401 "La sesión expiró".

###Validación en cada petición

El filtro `jwt` (`app/Filters/JwtFilter.php`) usa `JwtAuth::fromRequest()` (`app/Libraries/JwtAuth.php`), que comprueba en orden:

1. Que exista la cabecera `Authorization: Bearer <token>`.
2. Que la firma sea válida (token no falsificado ni modificado).
3. Que no esté vencido (`exp`), salvo en refresh/logout/logout-all, que aceptan el margen de `jwt.refresh_ttl`.
4. Que tenga `sid`.
5. Con **una sola consulta** a la base (`SesionModel::motivoInvalida()`): que el usuario exista y siga activo (`estadoUsuario = 1`) y que la sesión exista en la tabla `sesion`, sea de ese usuario y no esté cerrada.

###Sesiones (tabla `sesion`)

Un JWT es válido por sí mismo hasta que vence, así que para poder listarlo y cerrarlo antes el servidor registra cada sesión en la tabla `sesion`:

- **Login** (y change-password, que inicia una sesión nueva): crea una fila con el `sid`, la IP, el dispositivo (User-Agent), la fecha de inicio y la fecha de fin (`fechaExpiracion`, 7 días después).
- **Refresh**: actualiza la misma fila (`ultimoTokenExpira`, `ultimaActividad`, `ultimaIp`). No crea una sesión nueva.
- **Cada petición**: si pasaron 5 minutos o más desde la última actividad registrada, actualiza `ultimaActividad` y `ultimaIp`. No escribe en cada petición, solo cada 5 minutos como máximo.
- **Cerrar sesiones**: no se borra la fila, se marca `fechaCierre` y `motivoCierre`. El motivo sirve para que el `401` diga por qué se cerró:

| `motivoCierre` | Lo genera | Mensaje |
|---|---|---|
| `logout` | `POST /api/auth/logout`, o `DELETE /api/auth/sessions/{idSesion}` sobre la sesión actual | La sesión fue cerrada, inicie sesión nuevamente |
| `otro-dispositivo` | `DELETE /api/auth/sessions/{idSesion}` sobre otra sesión | La sesión se cerró desde otro dispositivo, inicie sesión nuevamente |
| `logout-all` | `POST /api/auth/logout-all` | Se cerraron todas las sesiones, inicie sesión nuevamente |
| `cambio-password` | `POST /api/auth/change-password` | La contraseña cambió, inicie sesión nuevamente |
| `admin` | `POST /api/usuarios/{id}/cerrar-sesiones` | Un administrador cerró la sesión, inicie sesión nuevamente |
| `usuario-desactivado` | `PATCH /api/usuarios/{id}/estado` con 0 | El usuario fue desactivado, inicie sesión nuevamente |

`GET /api/auth/sessions` lista las sesiones **activas** del usuario: no cerradas, dentro de los 7 días y cuyo último token sigue vigente o todavía se puede refrescar (venció hace menos de `jwt.refresh_ttl`). Para cada una devuelve `idSesion` (el que usa `DELETE /api/auth/sessions/{idSesion}`), `actual` (si es la del token enviado), `dispositivo`, `ipInicio`, `ultimaIp`, `fechaInicio`, `ultimaActividad` y `fechaExpiracion`. El `sid` no se expone.

**Limpieza:** en cada login se borran las filas que ya no sirven: sesiones que pasaron los 7 días, que no se refrescaron a tiempo o que se cerraron hace más de `jwt.refresh_ttl`. Las cerradas se conservan ese tiempo para que el `401` pueda explicar el motivo; después el mensaje pasa a ser "Sesión no encontrada".

###Contraseñas

- Se guardan con **Argon2id** (`password_hash(..., PASSWORD_ARGON2ID)`) y se comprueban con `password_verify`. Nunca se devuelven en las respuestas.
- La contraseña nueva debe tener entre 8 y 72 caracteres, ser distinta de la actual y coincidir con la confirmación.
- En el login, si el correo no existe igual se verifica la contraseña contra un hash ficticio, para que el tiempo de respuesta no revele qué correos están registrados. El mensaje de error es el mismo en ambos casos: "Correo o contraseña incorrectos".

###Límite de intentos (Throttler)

Para frenar ataques de fuerza bruta se usa el `Throttler` de CodeIgniter (`app/Libraries/LimiteIntentos.php`):

| Límite | Clave | Por defecto | Se reinicia |
|---|---|---|---|
| `throttle.login` | IP + correo | 5 intentos / 300 s | Al iniciar sesión correctamente |
| `throttle.loginIp` | IP | 20 intentos / 60 s | No (protege contra probar muchos correos desde una IP) |
| `throttle.changePassword` | Usuario | 5 intentos / 300 s | Al cambiar la contraseña correctamente |

- Funciona como un **balde de fichas**: empieza con el máximo de intentos y recupera uno cada `segundos / intentos` (con `5,300`, uno por minuto). Por eso, a un ritmo lento se pueden hacer algo más de intentos que el máximo; lo que se limita es la ráfaga.
- Al superar el límite responde `429` con la cabecera `Retry-After` (segundos hasta el próximo intento) y el mensaje "Demasiados intentos, vuelva a intentarlo en N segundos".
- Mientras una IP + correo está bloqueada, ni siquiera la contraseña correcta funciona desde esa IP. El bloqueo no afecta a otras IP ni a otros correos.
- El estado se guarda en la caché de CodeIgniter (`writable/cache/throttler_*` con el handler `file`). Para reiniciar todos los contadores basta con borrar esos archivos.

###Configuración (`.env`)

| Variable | Por defecto | Descripción |
|---|---|---|
| `jwt.secret` | (obligatoria) | Clave de firma de los tokens. Cambiarla invalida todos los tokens emitidos. |
| `jwt.ttl` | `3600` | Segundos de validez de cada token |
| `jwt.refresh_ttl` | `86400` | Segundos tras el vencimiento en que el token aún puede refrescarse |
| `jwt.session_ttl` | `604800` | Duración máxima de la sesión desde el login |
| `throttle.login` | `5,300` | Intentos de login por IP + correo, en `intentos,segundos` |
| `throttle.loginIp` | `20,60` | Intentos de login por IP |
| `throttle.changePassword` | `5,300` | Intentos de cambio de contraseña por usuario |
| `usuarios.tiposAdmin` | `Superadministrador,Administrador` | Tipos de usuario (`descripcionTipoUsuario`) que pueden activar, desactivar y cerrar las sesiones de otros usuarios |

Para generar un `jwt.secret` nuevo: `php -r "echo bin2hex(random_bytes(32));"`

###Tablas y archivos involucrados

- Tabla: `sesion` (crear con `php spark migrate`).
- `app/Controllers/API/AuthController.php`: endpoints de autenticación.
- `app/Libraries/JwtAuth.php`: generación y validación de tokens.
- `app/Libraries/LimiteIntentos.php`: límite de intentos.
- `app/Filters/JwtFilter.php`: filtro `jwt` (registrado en `app/Config/Filters.php`).
- `app/Models/SesionModel.php`: registro, validación, listado y cierre de sesiones.

###Para producción

- **HTTPS obligatorio**: el token viaja en cada petición; sin HTTPS puede ser interceptado. Se puede forzar con `forceGlobalSecureRequests` en `app/Config/App.php`.
- **`jwt.secret` propio y privado**: generar uno distinto por entorno y no subir `.env` al repositorio.
- **CORS**: hoy `app/Config/Cors.php` permite cualquier origen (`allowedOrigins = ['*']`); conviene limitarlo al dominio del frontend.
- **Proxy o balanceador**: si la API está detrás de uno, configurar `proxyIPs` en `app/Config/App.php`; si no, todas las peticiones parecen venir de la misma IP y el límite por IP bloquearía a todos a la vez.
- **Varios servidores**: el Throttler usa la caché `file`, que es local a cada servidor. Con más de un servidor, usar una caché compartida (por ejemplo Redis) para que los límites sean globales.
- **Cortar el acceso de otro usuario**: usar `PATCH /api/usuarios/{id}/estado` (lo bloquea y cierra sus sesiones) o `POST /api/usuarios/{id}/cerrar-sesiones` (solo cierra sus sesiones). Evitar cambiar `estadoUsuario` directamente en la base: lo bloquea igual, pero al reactivarlo sus tokens que aún no vencieron vuelven a funcionar.
- **Guardar el token en el frontend**: preferir memoria o `sessionStorage` antes que `localStorage`, y evitar insertar HTML sin escapar (XSS), porque un script inyectado podría leer el token.

##Esquema de base de datos:

```sql

-- =====================================================================
-- Esquema completo: api_school
-- Ordenado en cascada: cada tabla se crea DESPUÉS de las tablas que
-- referencia, así que el script corre sin desactivar FOREIGN_KEY_CHECKS.
--
-- Árbol de dependencias:
--   NIVEL 0 (sin dependencias)
--     nivel, tipo_usuario, area, anio_escolar, postulante, alumno, migrations
--   NIVEL 1
--     usuario          -> tipo_usuario
--     grado            -> nivel
--     curso            -> area
--     anio_postulante  -> anio_escolar, postulante   (lógica, sin FK declarada)
--   NIVEL 2
--     apoderado        -> usuario
--     curso_grado      -> curso, grado
--   NIVEL 3
--     bimestre         -> curso_grado        (ON DELETE/UPDATE CASCADE)
--   NIVEL 4
--     unidad           -> bimestre           (ON DELETE/UPDATE CASCADE)
--   NIVEL 5
--     competencias     -> unidad
-- =====================================================================

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `api_school`
  DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `api_school`;

-- =====================================================================
-- NIVEL 0 — Tablas base (sin claves foráneas)
-- =====================================================================

CREATE TABLE IF NOT EXISTS `nivel` (
  `idNivel`            int(5) unsigned NOT NULL AUTO_INCREMENT,
  `descripcionNivel`   varchar(100) NOT NULL,
  `fechaCreacion`      datetime NOT NULL,
  `fechaActualizacion` datetime NOT NULL,
  `usuarioCreacion`    int(11) NOT NULL,
  `usuarioActualizacion` int(11) NOT NULL,
  PRIMARY KEY (`idNivel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tipo_usuario` (
  `idTipoUsuario`          int(11) unsigned NOT NULL AUTO_INCREMENT,
  `descripcionTipoUsuario` varchar(100) NOT NULL,
  `fechaCreacion`          datetime NOT NULL,
  `fechaActualizacion`     datetime NOT NULL,
  `usuarioCreacion`        int(11) NOT NULL,
  `usuarioActualizacion`   int(11) NOT NULL,
  PRIMARY KEY (`idTipoUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `area` (
  `idArea`               int(10) unsigned NOT NULL AUTO_INCREMENT,
  `descripcionArea`      varchar(100) NOT NULL,
  `fechaCreacion`        datetime NOT NULL,
  `fechaActualizacion`   datetime NOT NULL,
  `usuarioCreacion`      int(10) unsigned NOT NULL,
  `usuarioActualizacion` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idArea`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `anio_escolar` (
  `idAnioEscolar`        int(11) unsigned NOT NULL AUTO_INCREMENT,
  `descripcionAnio`      varchar(25) NOT NULL,
  `estadoAnio`           int(11) NOT NULL,
  `cuotaInicial`         decimal(10,2) NOT NULL,
  `matriculaInicial`     decimal(10,2) DEFAULT NULL,
  `pensionInicial`       decimal(10,2) DEFAULT NULL,
  `matriculaPrimaria`    decimal(10,2) DEFAULT NULL,
  `pensionPrimaria`      decimal(10,2) DEFAULT NULL,
  `matriculaSecundaria`  decimal(10,2) DEFAULT NULL,
  `pensionSecundaria`    decimal(10,2) DEFAULT NULL,
  `fechaCreacion`        datetime NOT NULL,
  `fechaActualizacion`   datetime NOT NULL,
  `usuarioCreacion`      int(11) NOT NULL,
  `usuarioActualizacion` int(11) NOT NULL,
  PRIMARY KEY (`idAnioEscolar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `postulante` (
  `idPostulante`             int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nombrePostulante`         varchar(200) NOT NULL,
  `apellidoPostulante`       varchar(200) NOT NULL,
  `sexoPostulante`           varchar(25) DEFAULT NULL,
  `dniPostulante`            varchar(15) DEFAULT NULL,
  `fechaNacimiento`          date DEFAULT NULL,
  `lugarNacimiento`          varchar(200) DEFAULT NULL,
  `gradoPostulacion`         varchar(50) NOT NULL,          -- guarda el idGrado como texto
  `domicilioPostulante`      varchar(200) DEFAULT NULL,
  `colegioProcedencia`       varchar(100) DEFAULT NULL,
  `dificultadPostulante`     varchar(50) DEFAULT NULL,
  `dificultadObservacion`    varchar(255) DEFAULT NULL,
  `tipoAtencionPostulante`   varchar(255) DEFAULT NULL,
  `tratamientoPostulante`    varchar(255) DEFAULT NULL,
  `fechaPostulacion`         date NOT NULL,
  `listaApoderados`          varchar(100) NOT NULL,         -- JSON {"idPadre":..,"idMadre":..}
  `fichaPostulante`          varchar(300) DEFAULT NULL,
  `fechaFichaPost`           date DEFAULT NULL,
  `estadoFichaPostulante`    int(11) DEFAULT NULL,
  `fechaEntrevista`          date DEFAULT NULL,
  `estadoEntrevista`         int(11) DEFAULT NULL,
  `informePsicologico`       varchar(300) DEFAULT NULL,
  `fechaInformePsicologico`  date DEFAULT NULL,
  `estadoInformePsicologico` int(11) DEFAULT NULL,
  `constanciaAdeudo`         bit(1) DEFAULT NULL,
  `fechaConstanciaAdeudo`    date DEFAULT NULL,
  `cartaAdmision`            bit(1) DEFAULT NULL,
  `fechaCartaAdmision`       date DEFAULT NULL,
  `pagoMatricula`            int(11) DEFAULT NULL,
  `fechaPagoMatricula`       date DEFAULT NULL,
  `pagoCuotaIngreso`         int(11) DEFAULT NULL,
  `fechaCuotaIngreso`        date DEFAULT NULL,
  `contrato`                 bit(1) DEFAULT NULL,
  `fechaContrato`            date DEFAULT NULL,
  `documentoTraslado`        bit(1) DEFAULT NULL,
  `fechaDocumentoTraslado`   date DEFAULT NULL,
  `constanciaVacante`        bit(1) DEFAULT NULL,
  `fechaConstanciaVacante`   date DEFAULT NULL,
  `estadoPostulante`         int(11) NOT NULL,
  `fechaCreacion`            datetime DEFAULT NULL,
  `fechaActualizacion`       datetime DEFAULT NULL,
  `usuarioCreacion`          int(11) NOT NULL,
  `usuarioActualizacion`     int(11) NOT NULL,
  PRIMARY KEY (`idPostulante`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `alumno` (
  `idAlumno`             int(11) unsigned NOT NULL AUTO_INCREMENT,
  `estadoSiagie`         int(11) DEFAULT NULL,
  `codAlumnoCaja`        varchar(25) DEFAULT NULL,
  `nombresAlumno`        varchar(150) NOT NULL,
  `apellidosAlumno`      varchar(150) NOT NULL,
  `sexoAlumno`           varchar(25) DEFAULT NULL,
  `dniAlumno`            varchar(25) NOT NULL,
  `fechaNacimiento`      date NOT NULL,
  `direccionAlumno`      varchar(200) DEFAULT NULL,
  `distritoAlumno`       varchar(100) DEFAULT NULL,
  `IEPProcedencia`       varchar(200) DEFAULT NULL,
  `seguroSalud`          varchar(150) DEFAULT NULL,
  `fechaIngresoVolta`    date DEFAULT NULL,
  `nuevoAlumno`          bit(1) DEFAULT NULL,
  `numeroEmergencia`     varchar(25) DEFAULT NULL,
  `enfermedades`         varchar(200) DEFAULT NULL,
  `fechaCreacion`        datetime NOT NULL,
  `fechaActualizacion`   datetime NOT NULL,
  `usuarioCreacion`      int(11) unsigned NOT NULL,
  `usuarioActualizacion` int(11) unsigned NOT NULL,
  PRIMARY KEY (`idAlumno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `migrations` (
  `id`        bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version`   varchar(255) NOT NULL,
  `class`     varchar(255) NOT NULL,
  `group`     varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time`      int(11) NOT NULL,
  `batch`     int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- NIVEL 1
-- =====================================================================

-- usuario -> tipo_usuario
CREATE TABLE IF NOT EXISTS `usuario` (
  `idUsuario`            int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idTipoUsuario`        int(11) unsigned NOT NULL,
  `correoUsuario`        varchar(150) NOT NULL,
  `password`             varchar(200) NOT NULL,
  `nombreUsuario`        varchar(150) NOT NULL,
  `apellidoUsuario`      varchar(150) NOT NULL,
  `dniUsuario`           varchar(20) NOT NULL,
  `estadoUsuario`        int(11) NOT NULL,
  `ultimaConexion`       datetime NOT NULL,
  `fechaCreacion`        datetime NOT NULL,
  `fechaActualizacion`   datetime NOT NULL,
  `usuarioCreacion`      int(11) NOT NULL,
  `usuarioActualizacion` int(11) NOT NULL,
  PRIMARY KEY (`idUsuario`),
  KEY `usuario_idTipoUsuario_foreign` (`idTipoUsuario`),
  CONSTRAINT `usuario_idTipoUsuario_foreign`
    FOREIGN KEY (`idTipoUsuario`) REFERENCES `tipo_usuario` (`idTipoUsuario`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- grado -> nivel
CREATE TABLE IF NOT EXISTS `grado` (
  `idGrado`              int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idNivel`              int(11) unsigned NOT NULL,
  `descripcionGrado`     varchar(150) NOT NULL,
  `fechaCreacion`        datetime NOT NULL,
  `fechaActualizacion`   datetime NOT NULL,
  `usuarioCreacion`      int(11) unsigned NOT NULL,
  `usuarioActualizacion` int(11) unsigned NOT NULL,
  PRIMARY KEY (`idGrado`),
  KEY `idNivel` (`idNivel`),
  CONSTRAINT `grado_idNivel_foreign`
    FOREIGN KEY (`idNivel`) REFERENCES `nivel` (`idNivel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- curso -> area
CREATE TABLE IF NOT EXISTS `curso` (
  `idCurso`              int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idArea`               int(11) unsigned NOT NULL,
  `descripcionCurso`     varchar(100) NOT NULL,
  `fechaCreacion`        datetime NOT NULL,
  `fechaActualizacion`   datetime NOT NULL,
  `usuarioCreacion`      int(11) NOT NULL,
  `usuarioActualizacion` int(11) NOT NULL,
  `estadoCurso`          int(1) DEFAULT NULL,
  PRIMARY KEY (`idCurso`),
  KEY `curso_idArea_foreign` (`idArea`),
  CONSTRAINT `curso_idArea_foreign`
    FOREIGN KEY (`idArea`) REFERENCES `area` (`idArea`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- anio_postulante -> anio_escolar, postulante
-- (En el backup original NO tiene FKs declaradas; la relación es solo lógica.)
CREATE TABLE IF NOT EXISTS `anio_postulante` (
  `idAnioPostulante`     int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idAnioEscolar`        int(11) unsigned NOT NULL,
  `idPostulante`         int(11) unsigned NOT NULL,
  `fechaCreacion`        datetime NOT NULL,
  `fechaActualizacion`   datetime NOT NULL,
  `usuarioCreacion`      int(11) unsigned NOT NULL,
  `usuarioActualizacion` int(11) unsigned NOT NULL,
  PRIMARY KEY (`idAnioPostulante`)
  -- Sugerido (opcional):
  -- ,KEY `anio_postulante_idAnioEscolar_foreign` (`idAnioEscolar`)
  -- ,KEY `anio_postulante_idPostulante_foreign` (`idPostulante`)
  -- ,CONSTRAINT `anio_postulante_idAnioEscolar_foreign`
  --    FOREIGN KEY (`idAnioEscolar`) REFERENCES `anio_escolar` (`idAnioEscolar`)
  -- ,CONSTRAINT `anio_postulante_idPostulante_foreign`
  --    FOREIGN KEY (`idPostulante`) REFERENCES `postulante` (`idPostulante`)
  --    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- NIVEL 2
-- =====================================================================

-- apoderado -> usuario
CREATE TABLE IF NOT EXISTS `apoderado` (
  `idApoderado`          int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idUsuario`            int(11) unsigned DEFAULT NULL,
  `nombreApoderado`      varchar(150) NOT NULL,
  `apellidoApoderado`    varchar(150) NOT NULL,
  `dniApoderado`         varchar(255) DEFAULT NULL,
  `convivenciaAlumno`    varchar(25) DEFAULT NULL,
  `fechaNacimiento`      date DEFAULT NULL,
  `tipoApoderado`        varchar(25) NOT NULL,
  `correoApoderado`      varchar(150) NOT NULL,
  `celularApoderado`     varchar(15) DEFAULT NULL,
  `dependenciaApoderado` varchar(50) DEFAULT NULL,
  `centroLaboral`        varchar(150) DEFAULT NULL,
  `telefonoTrabajo`      varchar(15) DEFAULT NULL,
  `ingresoMensual`       varchar(15) DEFAULT NULL,
  `gradoInstruccion`     varchar(50) DEFAULT NULL,
  `profesionApoderado`   varchar(50) DEFAULT NULL,
  `listaAlumnos`         varchar(100) DEFAULT NULL,
  `cuentaCreada`         int(11) NOT NULL,
  `fechaCreacion`        datetime NOT NULL,
  `fechaActualizacion`   datetime NOT NULL,
  `usuarioCreacion`      int(11) NOT NULL,
  `usuarioActualizacion` int(11) NOT NULL,
  PRIMARY KEY (`idApoderado`),
  KEY `idUsuario` (`idUsuario`),
  CONSTRAINT `apoderado_idUsuario_foreign`
    FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- curso_grado -> curso, grado
CREATE TABLE IF NOT EXISTS `curso_grado` (
  `idCursoGrado`         int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idCurso`              int(11) unsigned NOT NULL,
  `idGrado`              int(11) unsigned NOT NULL,
  `fechaCreacion`        datetime NOT NULL,
  `fechaActualizacion`   datetime NOT NULL,
  `usuarioCreacion`      int(11) unsigned NOT NULL,
  `usuarioActualizacion` int(11) unsigned NOT NULL,
  PRIMARY KEY (`idCursoGrado`),
  KEY `curso_grado_idCurso_foreign` (`idCurso`),
  KEY `curso_grado_idGrado_foreign` (`idGrado`),
  CONSTRAINT `curso_grado_idCurso_foreign`
    FOREIGN KEY (`idCurso`) REFERENCES `curso` (`idCurso`),
  CONSTRAINT `curso_grado_idGrado_foreign`
    FOREIGN KEY (`idGrado`) REFERENCES `grado` (`idGrado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- NIVEL 3
-- =====================================================================

-- bimestre -> curso_grado
CREATE TABLE IF NOT EXISTS `bimestre` (
  `idBimestre`           int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idCursoGrado`         int(11) unsigned NOT NULL,
  `descripcionBimestre`  varchar(25) NOT NULL,
  `estadoBimestre`       bit(1) NOT NULL,
  `fechaCreacion`        datetime NOT NULL,
  `fechaActualizacion`   datetime NOT NULL,
  `usuarioCreacion`      int(11) NOT NULL,
  `usuarioActualizacion` int(11) NOT NULL,
  PRIMARY KEY (`idBimestre`),
  KEY `bimestre_idCursoGrado_foreign` (`idCursoGrado`),
  CONSTRAINT `bimestre_idCursoGrado_foreign`
    FOREIGN KEY (`idCursoGrado`) REFERENCES `curso_grado` (`idCursoGrado`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- NIVEL 4
-- =====================================================================

-- unidad -> bimestre
CREATE TABLE IF NOT EXISTS `unidad` (
  `idUnidad`             int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idBimestre`           int(11) unsigned DEFAULT NULL,
  `descripcionUnidad`    varchar(100) NOT NULL,
  `estadoUnidad`         bit(1) NOT NULL,
  `fechaCreacion`        datetime NOT NULL,
  `fechaActualizacion`   datetime NOT NULL,
  `usuarioCreacion`      int(11) NOT NULL,
  `usuarioActualizacion` int(11) NOT NULL,
  PRIMARY KEY (`idUnidad`),
  KEY `idBimestre` (`idBimestre`),
  CONSTRAINT `unidad_idBimestre_foreign`
    FOREIGN KEY (`idBimestre`) REFERENCES `bimestre` (`idBimestre`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- NIVEL 5
-- =====================================================================

-- competencias -> unidad
CREATE TABLE IF NOT EXISTS `competencias` (
  `idCompetencia`          int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idUnidad`               int(11) unsigned NOT NULL,
  `descripcionCompetencia` varchar(255) NOT NULL,
  `capacidadesCompetencia` text,
  `estandarCompetencia`    text,
  `fechaCreacion`          datetime NOT NULL,
  `fechaActualizacion`     datetime NOT NULL,
  `usuarioCreacion`        int(11) NOT NULL,
  `usuarioActualizacion`   int(11) NOT NULL,
  PRIMARY KEY (`idCompetencia`),
  KEY `idUnidad` (`idUnidad`),
  CONSTRAINT `competencias_idUnidad_foreign`
    FOREIGN KEY (`idUnidad`) REFERENCES `unidad` (`idUnidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- NIVEL 3 (sesiones JWT)
-- =====================================================================

-- sesion -> usuario
CREATE TABLE IF NOT EXISTS `sesion` (
  `idSesion`          int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sid`               varchar(64) NOT NULL,
  `idUsuario`         int(11) unsigned NOT NULL,
  `ipInicio`          varchar(45) NOT NULL,
  `ultimaIp`          varchar(45) NOT NULL,
  `dispositivo`       varchar(255) DEFAULT NULL,
  `fechaInicio`       datetime NOT NULL,
  `ultimaActividad`   datetime NOT NULL,
  `ultimoTokenExpira` datetime NOT NULL,
  `fechaExpiracion`   datetime NOT NULL,
  `fechaCierre`       datetime DEFAULT NULL,
  `motivoCierre`      varchar(30) DEFAULT NULL,
  PRIMARY KEY (`idSesion`),
  UNIQUE KEY `sid` (`sid`),
  KEY `idUsuario` (`idUsuario`),
  KEY `fechaExpiracion` (`fechaExpiracion`),
  CONSTRAINT `sesion_idUsuario_foreign`
    FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- Orden de borrado (inverso), por si necesitas hacer DROP limpio:
--   sesion, competencias, unidad, bimestre, curso_grado, apoderado,
--   anio_postulante, curso, grado, usuario,
--   migrations, alumno, postulante, anio_escolar, area, tipo_usuario, nivel
-- =====================================================================

```