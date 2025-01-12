<?php

declare(strict_types=1);
require_once __DIR__ .
    '/../vendor/autoload.php';


use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use ShangabMiddlewares\ShangabSlimSwagger;

$app = AppFactory::create();
$container = $app->getContainer();

$users =  [
    [
        'id' => 1,
        'email' => 'email1@gmail.com',
        'name' => 'John Doe1',
    ],
    [
        'id' => 2,
        'email' => 'email2@gmail.com',
        'name' => 'Jane Doe2',
    ]

];
$cars =  [
    [
        'id' => 1,
        'make' => 'Toyota',
        'model' => 'Corolla',
    ],
    [
        'id' => 2,
        'make' => 'Honda',
        'model' => 'Civic',
    ]
];

$container['data'] = ["users" => $users, "cars" => $cars];

$app->add(new ShangabSlimSwagger($app, 'IFastRemitt API', '1.0.0', 'API for IFastRemittance'));

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write('Welcome to IFastRemittance API');
    return $response;
});
$app->group('/users', function ($app) use ($container) {
    $app->get('/', function (Request $request, Response $response, $args) use ($container) {
        $body = json_encode($container['data']['users']);
        $response->getBody()->write($body);
        return $response->withHeader('Content-Type', 'application/json');
    });


    $app->post('/', function (Request $request, Response $response, $args) use ($container) {
        $body = $request->getBody()->getContents();
        $user = json_decode($body, true);
        $container['data']['users'][] = $user;
        $response->getBody()->write(json_encode($container['data']['users']));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->put('/', function (Request $request, Response $response, $args) use ($container) {
        $body = $request->getBody()->getContents();
        $user = json_decode($body, true);
        $key = array_search($user['email'], array_column($container['data']['users'], 'email'));
        $container['data']['users'][$key] = $user;
        $response->getBody()->write(json_encode($container['data']['users']));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->delete('/{id}', function (Request $request, Response $response, $args) use ($container) {
        $id = $args['id'];
        $container['data']['users'] = array_values(array_filter($container['data']['users'], function ($user) use ($id) {
            return $user['id'] != $id;
        }));
        $response->getBody()->write(json_encode(['status' => true, 'message' => 'User deleted']));
        return $response->withHeader('Content-Type', 'application/json');
    });
});

$app->group('/cars', function ($app) use ($container) {
    $app->get('/', function (Request $request, Response $response, $args) use ($container) {
        $response->getBody()->write(json_encode($container['data']['cars']));
        return $response->withHeader('Content-Type', 'application/json');
    });
    $app->get('/{id}', function (Request $request, Response $response, $args) use ($container) {
        $id = $args['id'];
        $user = array_values(array_filter($container['data']['cars'], function ($user) use ($id) {
            return $user['id'] == $id;
        }));
        $response->getBody()->write(json_encode($user));
        return $response->withHeader('Content-Type', 'application/json');
    });
});
$app->run();
