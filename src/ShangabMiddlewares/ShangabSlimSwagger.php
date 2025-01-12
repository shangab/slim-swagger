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
            // Detect group by analyzing the path
            $pathSegments = explode('/', trim($path, '/'));
            $groupName = isset($pathSegments[0]) && !empty($pathSegments[0])
                ? ucfirst($pathSegments[0]) . ' Api' // Group name based on the first segment
                : 'General API';

            // Add group (tag) to the spec if not already added
            if (!array_search($groupName, array_column($openApiSpec['tags'], 'name'))) {
                $openApiSpec['tags'][] = [
                    'name' => $groupName,
                    'description' => "$groupName Endpoints",
                ];
            }

            foreach ($methods as $method) {
                // Add route to OpenAPI paths
                $openApiSpec['paths'][$path][strtolower($method)] = [
                    'tags' => [$groupName],
                    'summary' => "Endpoint for $path",
                    'responses' => [
                        '200' => [
                            'description' => 'Successful response',
                        ],
                    ],
                ];

                // Add request body for POST/PUT/PATCH methods
                if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
                    $openApiSpec['paths'][$path][strtolower($method)]['requestBody'] = [
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
                if ($method === 'DEL') {
                    // Extract dynamic parameters from the route pattern
                    preg_match_all('/\{(\w+)\}/', $path, $matches);
                    $pathParams = $matches[1] ?? [];

                    // Start with default parameters
                    $operation['parameters'] = [];

                    // Add dynamic path parameters as query parameters
                    foreach ($pathParams as $param) {
                        $operation['parameters'][] = [
                            'name' => $param,
                            'in' => 'query',
                            'description' => ucfirst($param) . ' of the resource',
                            'required' => true,
                            'schema' => [
                                'type' => 'string',
                                'example' => '12345',
                            ],
                        ];
                    }

                    // Add static query parameters (e.g., "force" for delete operations)
                    $operation['parameters'][] = [
                        'name' => 'force',
                        'in' => 'query',
                        'description' => 'Force delete the resource',
                        'required' => false,
                        'schema' => [
                            'type' => 'boolean',
                            'example' => true,
                        ],
                    ];
                    $operation['responses'] = [
                        '200' => [
                            'description' => 'Successful deletion, returns json object',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'status' => [
                                                    'type' => 'boolean',
                                                    'example' => true,
                                                ],
                                                'message' => [
                                                    'type' => 'string',
                                                    'example' => 'Resource deleted successfully',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ];
                    // Add operation to OpenAPI spec
                    $openApiSpec['paths'][$path][strtolower($method)] = $operation;
                }
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
        // Check if the request path matches "/docs"
        if ($request->getUri()->getPath() === '/openapi') {

            // Create a response with Swagger JSON
            $paths = $this->getOpenApi();
            $response = new \Slim\Psr7\Response();
            // $response->getBody()->write(json_encode([
            //     'openapi' => '3.0.0',
            //     'info' => [
            //         'title' => $this->title,
            //         'version' => $this->version,
            //         'description' => $this->description,
            //     ],
            //     'paths' => $paths,
            // ]));
            $response->getBody()->write(json_encode($paths));

            return $response->withHeader('Content-Type', 'application/json');
        }
        if ($request->getUri()->getPath() === '/docs') {
            // Create a response with Swagger UI
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write($this->getSwaggerHtml());
            return $response->withHeader('Content-Type', 'text/html');
        }

        // Continue processing other middleware and the main handler
        return $handler->handle($request);
    }
}
