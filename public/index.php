<?php

declare(strict_types=1);
require_once __DIR__ .
    '/../vendor/autoload.php';


use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Shangab\Middleware\ShangabSlimSwagger;
use Shangab\Middleware\ShangabJWTAuth;

$app = AppFactory::create();
$container = $app->getContainer();

$users =  [
    [
        'id' => 1,
        'email' => 'email1@gmail.com',
        'name' => 'John Doe1',
        'type' => 'staff'

    ],
    [
        'id' => 2,
        'email' => 'email2@gmail.com',
        'name' => 'Jane Doe2',
        'type' => 'staff'
    ],
    [
        'id' => 3,
        'email' => 'email3@gmail.com',
        'name' => 'Jane Doe3',
        'type' => 'client'
    ],
    [
        'id' => 4,
        'email' => 'email4@gmail.com',
        'name' => 'Jane Doe4',
        'type' => 'client'
    ]
];


$container['data'] = ["users" => $users];

$app->add(new ShangabSlimSwagger($app, 'Shangab Slim Swagger', '1.0.1', 'Api for Shangab Slim Swagger.'));

$app->group('/users', function ($app) use ($container) {
    $app->post('/add', function (Request $request, Response $response, $args) use ($container) {
        $body = $request->getBody()->getContents();
        $user = json_decode($body, true);
        $container['data']['users'][] = $user;
        $users = $container['data']['users'];
        $response->getBody()->write(json_encode(['status' => true, 'message' => 'User addded', 'users' => $users]));
        return $response->withHeader('Content-Type', 'application/json');
    });
    $app->put('/update', function (Request $request, Response $response, $args) use ($container) {
        $body = $request->getBody()->getContents();
        $user = json_decode($body, true);
        $key = array_search($user['id'], array_column($container['data']['users'], 'id'));
        $container['data']['users'][$key] = $user;
        $users = $container['data']['users'];
        $response->getBody()->write(json_encode(['status' => true, 'message' => 'User updated', 'users' => $users]));
        return $response->withHeader('Content-Type', 'application/json');
    });
    $app->delete('/delete/{id}', function (Request $request, Response $response, $args) use ($container) {
        $id = $args['id'];
        $users = array_values(array_filter($container['data']['users'], function ($user) use ($id) {
            return $user['id'] != $id;
        }));
        $response->getBody()->write(json_encode(['status' => true, 'message' => 'User deleted', 'users' => $users]));
        return $response->withHeader('Content-Type', 'application/json');
    });
})->add(new ShangabJWTAuth($app));

// All routes above this middleware will apply ShangabJWTAuth middleware protected routes.
// Below routes will not be protected by ShangabJWTAuth middleware.

$app->group('/users', function ($app) use ($container) {
    $app->get('/staff', function (Request $request, Response $response, $args) use ($container) {
        $users = array_values(array_filter($container['data']['users'], function ($user) {
            return $user['type'] == 'staff';
        }));
        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    });
    $app->get('/client', function (Request $request, Response $response, $args) use ($container) {
        $users = array_values(array_filter($container['data']['users'], function ($user) {
            return $user['type'] == 'client';
        }));
        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    });
    $app->get('/all', function (Request $request, Response $response, $args) use ($container) {
        $users = $container['data']['users'];
        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    });
});


$app->run();
