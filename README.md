# Slim Swagger Middleware

This is a Slim PHP middleware that automatically generates and serves Swagger (OpenAPI) documentation for your Slim routes. It supports dynamic route scanning, including GET, POST, PUT, PATCH, and DELETE methods, and generates detailed documentation without requiring external annotation libraries. This project was created for a project I am working on in `php`. I used to do backend with `python FatAPI` or `.NET`, which both have swagger UI embeded in them, out of the box. However in `php` swagger is tricky.

I wanted the same in `php` in my new project but the easiest I found is `zircote/swagger-php` which requires a lot of annotations if I am got it right. I loved the way `slim-php` is light, powerful and scalable, Therefore, I started to build this middleware. The first version that you see below in the screenshot was built in a hurry in a single day, but the future roadmap will increase the middleware power.

All the best wishes, use, recommend, star, fork, contribute, spread and enjoy it.

## Features

- **Automatic Swagger generation**: Scans all Slim routes and generates OpenAPI documentation on the fly.
- **Supports multiple HTTP methods**: Handles GET, POST, PUT, PATCH, DELETE, and more.
- **No external annotations**: Does not rely on third-party annotation libraries like `zircote/swagger-php`.
- **Customizable**: Easily extendable to add custom parameters, request bodies, and responses.
- **MIT License**: Open-source and free to use under the MIT License.

<!-- image  -->

![image](swagger.png)

## Requirements

1. php 8.2
2. "slim/slim": "^4.9"
3. "slim/psr7": "^1.5"

## Installation

You can install this middleware in your Slim project via Composer.

### Step 1: Add the package to your project

```bash
composer require shangab/slim-swagger
```

### Step 2: How to use it

To use the middleware follow the code below, declare the `ShangabSlimSwagger` middleware and add it to your app:

```php
// Declare the middleware
use ShangabMiddlewares\ShangabSlimSwagger;
// Your app definition

...
$app = AppFactory::create();
...

// Add the middleware
$app->add(new ShangabSlimSwagger($app, title: 'Title your API', version: 'version your API', description: 'Describe your API'));
```

### Please avoid using route names `openapi` and `docs`

I use these two routes and serve them before the `$app` routes, `openapi` returns the OpenAPI Specs,
while `docs` route returns the swagger UI shown above.
