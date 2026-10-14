<?php

namespace App\Filters;

use App\Libraries\JwtAuth;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;

/**
 * Exige un token JWT válido en la cabecera "Authorization: Bearer <token>".
 */
class JwtFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        try {
            JwtAuth::fromRequest($request);
        } catch (RuntimeException $e) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status'   => 401,
                    'error'    => 401,
                    'messages' => ['error' => $e->getMessage()],
                ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
