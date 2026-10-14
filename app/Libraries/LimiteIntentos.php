<?php

namespace App\Libraries;

/**
 * Límite de intentos con el Throttler de CodeIgniter (token bucket guardado en caché).
 *
 * Cada límite es "máximo de intentos" en "segundos": el balde arranca lleno con
 * $max intentos y recupera uno cada $segundos / $max segundos.
 *
 * Configuración en .env (formato "intentos,segundos"):
 *   throttle.login          = 5,300   por IP + correo (se reinicia al iniciar sesión)
 *   throttle.loginIp        = 20,60   por IP, sin importar el correo
 *   throttle.changePassword = 5,300   por usuario (se reinicia al cambiarla)
 */
class LimiteIntentos
{
    private const POR_DEFECTO = [
        'login'          => '5,300',
        'loginIp'        => '20,60',
        'changePassword' => '5,300',
    ];

    /**
     * Consume un intento. Devuelve null si está permitido, o los segundos
     * que hay que esperar si se superó el límite.
     *
     * @param string $limite nombre del límite (ver POR_DEFECTO)
     * @param string $clave  quién intenta (IP, correo, usuario...)
     */
    public static function consumir(string $limite, string $clave): ?int
    {
        [$max, $segundos] = self::config($limite);
        $throttler        = service('throttler');

        if ($throttler->check(self::nombre($limite, $clave), $max, $segundos)) {
            return null;
        }

        return $throttler->getTokenTime();
    }

    // Vuelve a llenar el balde (después de un intento correcto)
    public static function reiniciar(string $limite, string $clave): void
    {
        service('throttler')->remove(self::nombre($limite, $clave));
    }

    /**
     * @return array{0: int, 1: int}
     */
    private static function config(string $limite): array
    {
        [$max, $segundos] = array_map('intval', explode(',', (string) env('throttle.' . $limite, self::POR_DEFECTO[$limite])) + [1 => 0]);

        return [max(1, $max), max(1, $segundos)];
    }

    // La caché no admite caracteres como "@" o ":" (correos, IPv6): se usa un hash
    private static function nombre(string $limite, string $clave): string
    {
        return $limite . '_' . sha1(strtolower($clave));
    }
}
