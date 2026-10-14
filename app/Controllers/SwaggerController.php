<?php

namespace App\Controllers;

use OpenApi\Annotations\Server;
use OpenApi\Generator;

class SwaggerController extends BaseController
{
    // Interfaz Swagger UI
    // http://localhost:8080/docs
    public function index()
    {
        return view('swagger/index');
    }

    // Especificación OpenAPI generada a partir de los atributos #[OA\...]
    // http://localhost:8080/docs/openapi.json
    public function json()
    {
        $openapi = Generator::scan([
            APPPATH . 'Swagger',
            APPPATH . 'Controllers/API',
        ]);

        // El servidor se calcula desde la URL actual para que funcione igual con
        // "php spark serve" (localhost:8080) o bajo Laragon (localhost/apischool/public)
        $path    = (string) parse_url((string) $this->request->getServer('REQUEST_URI'), PHP_URL_PATH);
        $baseUrl = substr($path, 0, (int) strrpos($path, '/docs/openapi.json'));

        $openapi->servers = [new Server(['url' => $baseUrl === '' ? '/' : $baseUrl])];

        return $this->response
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setContentType('application/json')
            ->setBody($openapi->toJson());
    }
}
