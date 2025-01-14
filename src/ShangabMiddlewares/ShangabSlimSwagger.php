<?php

namespace ShangabMiddlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;

class ShangabSlimSwagger implements MiddlewareInterface
{

    public function __construct(
        private App $app,
        private String $title = 'Shangab Slim Swagger',
        private String $version = '1.0.0',
        private String $description = 'Dynamically generated OpenAPI documentation for Slim Framework',
    ) {}
    // This function will generate the OpenAPI schema

    private function getOpenApi()
    {
        $routes = $this->app->getRouteCollector()->getRoutes();
        $openApiSpec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => $this->title,
                'version' => $this->version,
                'description' => $this->description,
            ],
            'paths' => [],
            'tags' => [],
        ];

        foreach ($routes as $route) {
            $path = $route->getPattern();
            $methods = $route->getMethods();
            $pathSegments = explode('/', trim($path, '/'));
            $groupName = isset($pathSegments[0]) && !empty($pathSegments[0])
                ? ucfirst($pathSegments[0]) . ' API'
                : 'General API';

            if (!array_search($groupName, array_column($openApiSpec['tags'], 'name'))) {
                $openApiSpec['tags'][] = [
                    'name' => $groupName,
                    'description' => "$groupName Endpoints",
                ];
            }

            foreach ($methods as $method) {
                $operation = [
                    'tags' => [$groupName],
                    'summary' => "Endpoint for $path",
                    'responses' => [
                        '200' => [
                            'description' => 'Successful response',
                        ],
                    ],
                ];

                // Extract dynamic parameters from the route pattern
                preg_match_all('/\{(\w+)\}/', $path, $matches);
                $pathParams = $matches[1] ?? [];

                // Add dynamic path parameters to OpenAPI spec
                foreach ($pathParams as $param) {
                    $operation['parameters'][] = [
                        'name' => $param,
                        'in' => 'path',
                        'description' => ucfirst($param) . ' of the resource',
                        'required' => true,
                        'schema' => [
                            'type' => 'string',
                            'example' => '1234',
                        ],
                    ];
                }

                // Add request body for POST/PUT/PATCH methods
                if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
                    $operation['requestBody'] = [
                        'description' => 'JSON payload',
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'exampleField' => [
                                            'type' => 'string',
                                            'example' => 'exampleValue',
                                        ],
                                    ],
                                    'required' => ['exampleField'],
                                ],
                            ],
                        ],
                    ];
                }

                $openApiSpec['paths'][$path][strtolower($method)] = $operation;
            }
        }

        return $openApiSpec;
    }


    private function getSwaggerHtml()
    {
        $html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <title>Shangab Swagger UI</title>
            <link href="https://cdn.jsdelivr.net/npm/swagger-ui-dist/swagger-ui.css" rel="stylesheet">
        </head>
        <body>
            <div id="swagger-ui"></div>
            <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist/swagger-ui-bundle.js"></script>
            <script>
                SwaggerUIBundle({
                    url: '/openapi',
                    dom_id: '#swagger-ui',
                });
            </script>
        </body>
        </html>
        HTML;
        return $html;
    }
    public function process(ServerRequest $request, RequestHandlerInterface $handler): Response
    {
        if ($request->getUri()->getPath() === '/openapi') {

            $paths = $this->getOpenApi();
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode($paths));

            return $response->withHeader('Content-Type', 'application/json');
        }
        if ($request->getUri()->getPath() === '/docs') {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write($this->getSwaggerHtml());
            return $response->withHeader('Content-Type', 'text/html');
        }

        // Continue processing other middleware and the main handler
        return $handler->handle($request);
    }
}
