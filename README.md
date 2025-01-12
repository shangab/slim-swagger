# Slim Swagger Middleware

This is a Slim PHP middleware that automatically generates and serves Swagger (OpenAPI) documentation for your Slim routes. It supports dynamic route scanning, including GET, POST, PUT, PATCH, and DELETE methods, and generates detailed documentation without requiring external annotation libraries. This project was created on need for a project I am working on, I used to do backend with `python FatAPI`, which has swagger embeded in it out of the box, this is the awesomeness of `python`.

I wanted the same in `php` in my new project but the easist I found is `zircote/swagger-php` which requires a lot of annotations it I am get it right.
Therfore, I fell into `Slim-PHP` and started to build this middleware. The first version that you see below in the screenshot was built in a hurry in a single day, but the roadmap will increase the middleware power.

All the best, use and spread it.

## Features

- **Automatic Swagger generation**: Scans all Slim routes and generates OpenAPI documentation on the fly.
- **Supports multiple HTTP methods**: Handles GET, POST, PUT, PATCH, DELETE, and more.
- **No external annotations**: Does not rely on third-party annotation libraries like `zircote/swagger-php`.
- **Customizable**: Easily extendable to add custom parameters, request bodies, and responses.
- **MIT License**: Open-source and free to use under the MIT License.

<!-- image  -->

![image](swagger.png)

## Installation

You can install this middleware in your Slim project via Composer.

### Step 1: Add the package to your project

```bash
composer require shangab/slim-swagger
```

## Usage

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

### Please avoid using routes names `openapi` and 'docs'

I use these two routes and serve them before the `$app` routes, `openapi` returns the OpenAPI Specs,
while `docs` route returns the swagger UI shown above.
