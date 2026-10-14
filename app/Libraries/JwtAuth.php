<?php

namespace App\Libraries;

use App\Models\SesionModel;
use CodeIgniter\HTTP\RequestInterface;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;
use Throwable;

/**
 * Generación y validación de tokens JWT (HS256).
 * Configuración en .env: jwt.secret, jwt.ttl, jwt.refresh_ttl y jwt.session_ttl (segundos).
 */
class JwtAuth
{
    // Payload del último token validado en esta petición (lo deja el filtro jwt)
    private static ?object $actual = null;

    public static function ttl(): int
    {
        return (int) env('jwt.ttl', 3600);
    }

    /**
     * Segundos después de expirar durante los que un token aún puede refrescarse.
     */
    public static function refreshTtl(): int
    {
        return (int) env('jwt.refresh_ttl', 86400);
    }

    /**
     * Duración máxima de la sesión desde el login, aunque se refresque el token.
     */
    public static function sessionTtl(): int
    {
        return (int) env('jwt.session_ttl', 604800);
    }

    /**
     * Momento del login original (claim "auth_time"; los tokens antiguos usan "iat").
     */
    public static function authTime(object $payload): int
    {
        return (int) ($payload->auth_time ?? $payload->iat);
    }

    public static function sesionVencida(object $payload): bool
    {
        return time() >= self::finSesion($payload);
    }

    // Momento en que termina la sesión (límite de jwt.session_ttl)
    public static function finSesion(object $payload): int
    {
        return self::authTime($payload) + self::sessionTtl();
    }

    /**
     * Genera un token. Al refrescar se pasan el $sid y el $authTime del token anterior,
     * así sigue siendo la misma sesión y no pasa de jwt.session_ttl.
     * En el login van en null: se genera un sid nuevo.
     *
     * @return array{token: string, expires_in: int, sid: string, auth_time: int, exp: int}
     */
    public static function encode(array $claims, ?string $sid = null, ?int $authTime = null): array
    {
        $ahora    = time();
        $sid      ??= bin2hex(random_bytes(16));
        $authTime ??= $ahora;
        // El token nunca vence después del límite de la sesión
        $exp = min($ahora + self::ttl(), $authTime + self::sessionTtl());

        $token = JWT::encode(['iat' => $ahora, 'exp' => $exp, 'auth_time' => $authTime, 'sid' => $sid] + $claims, self::secret(), 'HS256');

        return ['token' => $token, 'expires_in' => $exp - $ahora, 'sid' => $sid, 'auth_time' => $authTime, 'exp' => $exp];
    }

    /**
     * Devuelve el payload del token "Authorization: Bearer ..." de la request.
     *
     * Con $permitirExpirado se acepta un token vencido hace menos de jwt.refresh_ttl
     * (la firma se valida igual), para usarlo en /api/auth/refresh y /api/auth/logout.
     *
     * @throws RuntimeException si el token falta, es inválido, expiró o su sesión se cerró
     */
    public static function fromRequest(RequestInterface $request, bool $permitirExpirado = false): object
    {
        $header = $request->getHeaderLine('Authorization');

        if (! preg_match('/^Bearer\s+(\S+)$/i', $header, $m)) {
            throw new RuntimeException('Token no proporcionado');
        }

        try {
            $payload = JWT::decode($m[1], new Key(self::secret(), 'HS256'));
        } catch (ExpiredException $e) {
            // ExpiredException solo se lanza si la firma es válida
            $payload = $e->getPayload();
            if (! $permitirExpirado || time() - (int) $payload->exp > self::refreshTtl()) {
                throw new RuntimeException('Token expirado');
            }
        } catch (Throwable $e) {
            throw new RuntimeException('Token inválido');
        }

        // Sin "sid" no se puede saber a qué sesión pertenece el token
        if (empty($payload->sid) || ! is_string($payload->sid)) {
            throw new RuntimeException('Token inválido, inicie sesión nuevamente');
        }

        $motivo = (new SesionModel())->motivoInvalida($payload->sid, (int) $payload->sub, $request->getIPAddress());
        if ($motivo !== null) {
            throw new RuntimeException($motivo);
        }

        return self::$actual = $payload;
    }

    /**
     * Payload del token de la petición actual, sin volver a consultar la base
     * si el filtro jwt ya lo validó.
     *
     * @throws RuntimeException si el token no es válido
     */
    public static function actual(RequestInterface $request): object
    {
        return self::$actual ?? self::fromRequest($request);
    }

    private static function secret(): string
    {
        $secret = (string) env('jwt.secret', '');
        if ($secret === '') {
            throw new RuntimeException('jwt.secret no está configurado en .env');
        }

        return $secret;
    }
}
