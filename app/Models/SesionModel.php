<?php

namespace App\Models;

use App\Libraries\JwtAuth;
use CodeIgniter\Model;

/**
 * Sesiones JWT (tabla sesion). Ver la migración CreateSesionTable.
 */
class SesionModel extends Model
{
    protected $table         = 'sesion';
    protected $primaryKey    = 'idSesion';
    protected $allowedFields = [
        'sid',
        'idUsuario',
        'ipInicio',
        'ultimaIp',
        'dispositivo',
        'fechaInicio',
        'ultimaActividad',
        'ultimoTokenExpira',
        'fechaExpiracion',
        'fechaCierre',
        'motivoCierre',
    ];
    protected $useTimestamps = false;

    // Cada cuánto se actualiza ultimaActividad (no en cada petición, para no escribir siempre)
    private const INTERVALO_ACTIVIDAD = 300;

    // Mensaje de error (401) según el motivo con que se cerró la sesión
    private const MENSAJES_CIERRE = [
        'logout'              => 'La sesión fue cerrada, inicie sesión nuevamente',
        'otro-dispositivo'    => 'La sesión se cerró desde otro dispositivo, inicie sesión nuevamente',
        'logout-all'          => 'Se cerraron todas las sesiones, inicie sesión nuevamente',
        'cambio-password'     => 'La contraseña cambió, inicie sesión nuevamente',
        'usuario-desactivado' => 'El usuario fue desactivado, inicie sesión nuevamente',
        'admin'               => 'Un administrador cerró la sesión, inicie sesión nuevamente',
    ];

    // Registra la sesión de un login y aprovecha para borrar las que ya no sirven
    public function crear(string $sid, int $idUsuario, int $authTime, int $exp, string $ip, string $dispositivo): void
    {
        $this->borrarTerminadas();

        $this->insert([
            'sid'               => $sid,
            'idUsuario'         => $idUsuario,
            'ipInicio'          => $ip,
            'ultimaIp'          => $ip,
            'dispositivo'       => mb_substr($dispositivo, 0, 255) ?: null,
            'fechaInicio'       => date('Y-m-d H:i:s', $authTime),
            'ultimaActividad'   => date('Y-m-d H:i:s'),
            'ultimoTokenExpira' => date('Y-m-d H:i:s', $exp),
            'fechaExpiracion'   => date('Y-m-d H:i:s', $authTime + JwtAuth::sessionTtl()),
        ]);
    }

    // Al refrescar: el token nuevo extiende el plazo para volver a refrescar
    public function renovar(string $sid, int $exp, string $ip): void
    {
        $this->where('sid', $sid)->set([
            'ultimoTokenExpira' => date('Y-m-d H:i:s', $exp),
            'ultimaActividad'   => date('Y-m-d H:i:s'),
            'ultimaIp'          => $ip,
        ])->update();
    }

    /**
     * Motivo por el que el token ya no es válido, o null si sigue activo.
     * En una sola consulta revisa que el usuario exista y esté activo y que la
     * sesión exista, sea suya y no se haya cerrado. Si la sesión es válida,
     * actualiza ultimaActividad cada INTERVALO_ACTIVIDAD segundos.
     */
    public function motivoInvalida(string $sid, int $idUsuario, string $ip): ?string
    {
        $fila = $this->db->table('usuario u')
            ->select('u.estadoUsuario, s.idSesion, s.fechaCierre, s.motivoCierre, s.ultimaActividad')
            ->join('sesion s', 's.idUsuario = u.idUsuario AND s.sid = ' . $this->db->escape($sid), 'left')
            ->where('u.idUsuario', $idUsuario)
            ->get()
            ->getRowArray();

        if (! $fila) {
            return 'Usuario no encontrado';
        }

        if ((int) $fila['estadoUsuario'] !== 1) {
            return 'Usuario inactivo';
        }

        if ($fila['idSesion'] === null) {
            return 'Sesión no encontrada, inicie sesión nuevamente';
        }

        if ($fila['fechaCierre'] !== null) {
            return self::MENSAJES_CIERRE[$fila['motivoCierre']] ?? self::MENSAJES_CIERRE['logout'];
        }

        if (time() - strtotime($fila['ultimaActividad']) >= self::INTERVALO_ACTIVIDAD) {
            $this->update($fila['idSesion'], ['ultimaActividad' => date('Y-m-d H:i:s'), 'ultimaIp' => $ip]);
        }

        return null;
    }

    public function cerrar(string $sid, string $motivo): void
    {
        $this->where('sid', $sid)
            ->where('fechaCierre', null)
            ->set(['fechaCierre' => date('Y-m-d H:i:s'), 'motivoCierre' => $motivo])
            ->update();
    }

    /**
     * Cierra una sesión por su idSesion, solo si es del usuario y sigue abierta.
     * Devuelve false si no existe, es de otro usuario o ya estaba cerrada.
     */
    public function cerrarDeUsuario(int $idSesion, int $idUsuario, string $motivo): bool
    {
        $this->where('idSesion', $idSesion)
            ->where('idUsuario', $idUsuario)
            ->where('fechaCierre', null)
            ->set(['fechaCierre' => date('Y-m-d H:i:s'), 'motivoCierre' => $motivo])
            ->update();

        return $this->db->affectedRows() > 0;
    }

    // Cierra todas las sesiones abiertas del usuario
    public function cerrarTodas(int $idUsuario, string $motivo): void
    {
        $this->where('idUsuario', $idUsuario)
            ->where('fechaCierre', null)
            ->set(['fechaCierre' => date('Y-m-d H:i:s'), 'motivoCierre' => $motivo])
            ->update();
    }

    /**
     * Sesiones que todavía se pueden usar: no cerradas, dentro del límite de
     * jwt.session_ttl y con un token vigente o que aún se puede refrescar.
     */
    public function activas(int $idUsuario): array
    {
        $ahora = date('Y-m-d H:i:s');

        return $this->where('idUsuario', $idUsuario)
            ->where('fechaCierre', null)
            ->where('fechaExpiracion >', $ahora)
            ->where('ultimoTokenExpira >', date('Y-m-d H:i:s', time() - JwtAuth::refreshTtl()))
            ->orderBy('ultimaActividad', 'DESC')
            ->findAll();
    }

    // Borra las sesiones que ya no se pueden usar (vencidas o cerradas hace tiempo)
    private function borrarTerminadas(): void
    {
        $limite = date('Y-m-d H:i:s', time() - JwtAuth::refreshTtl());

        $this->groupStart()
            ->where('fechaExpiracion <', date('Y-m-d H:i:s'))
            ->orWhere('ultimoTokenExpira <', $limite)
            ->orWhere('fechaCierre <', $limite)
            ->groupEnd()
            ->delete();
    }
}
