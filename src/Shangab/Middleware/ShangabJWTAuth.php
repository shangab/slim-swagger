<?php

namespace Shangab\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Response;
use Shangab\Util\ShangabJWTUtil;
use Slim\App;

class ShangabJWTAuth implements MiddlewareInterface
{
    private ShangabJWTUtil $jwt;

    public function __construct(
        private App $app,
    ) {
        $this->jwt = new ShangabJWTUtil();
        $routes = $this->app->getRouteCollector()->getRoutes();
        foreach ($routes as $route) {
            $route->setArgument('auth', '1');
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (in_array($request->getUri()->getPath(), ['/docs', '/docs/{path:.*}', '/openapi'])) {
            return $handler->handle($request);
        }
        $authorizationHeader = $request->getHeaderLine('Authorization');
        if (empty($authorizationHeader)) {
            return $this->unauthorizedResponse('Authorization header is missing');
        }


        if (!$this->jwt->verifyToken()) {
            return $this->unauthorizedResponse('Invalid JWT token');
        }

        return $handler->handle($request);
    }

    private function unauthorizedResponse($message): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}
