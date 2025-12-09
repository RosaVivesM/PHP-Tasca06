<?php

use Core\Authenticator;
use Core\Response;
use Core\Session;
use Views\vistas\VistaJson;
use Views\vistas\VistaHtml;

session_start();

const BASE_PATH = __DIR__.'/../';

require BASE_PATH.'Core/functions.php';

spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    require base_path("{$class}.php");
});

require base_path('bootstrap.php');

$router = new \Core\Router();
$routes = require base_path('routes.php');

$uri = parse_url($_SERVER['REQUEST_URI'])['path'];
$method = $_POST['_method'] ?? $_SERVER['REQUEST_METHOD'];

try {
    $router->route($uri, $method);
} catch (\Core\ValidationException $exception) {

    if ((new Authenticator())->isRestfulRequest()) {
        Response::json([
            'errors' => $exception->errors,
            'old' => $exception->old,
        ], 422);
    }

    Session::flash('error', $exception->errors);
    Session::flash('old', $exception->old);

    return redirect($router->previousUrl());
}

Session::unflash();
