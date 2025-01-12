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
$container['data'] = $users;

$app->add(new ShangabSlimSwagger($app, 'IFastRemitt API', '1.0.0', 'API for IFastRemittance'));

$app->get('/users', function (Request $request, Response $response, $args) use ($container) {
    $body = json_encode($container['data']);
    $response->getBody()->write($body);
    return $response->withHeader('Content-Type', 'application/json');
});


$app->post('/users', function (Request $request, Response $response, $args) use ($container) {
    $body = $request->getBody()->getContents();
    $user = json_decode($body, true);
    $container['data'][] = $user;
    $response->getBody()->write(json_encode($container['data']));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->put('/users', function (Request $request, Response $response, $args) use ($container) {
    $body = $request->getBody()->getContents();
    $user = json_decode($body, true);
    $key = array_search($user['email'], array_column($container['data'], 'email'));
    $container['data'][$key] = $user;
    $response->getBody()->write(json_encode($container['data']));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->delete('/users/{id}', function (Request $request, Response $response, $args) use ($container) {
    $id = $args['id'];
    $container['data'] = array_values(array_filter($container['data'], function ($user) use ($id) {
        return $user['id'] != $id;
    }));
    $response->getBody()->write(json_encode(['status' => true, 'message' => 'User deleted']));
    return $response->withHeader('Content-Type', 'application/json');
});
$app->run();
