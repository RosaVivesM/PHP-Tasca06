<?php

use Core\Authenticator;
use Core\Session;
use Http\controllers\session\SessionController;
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

$peticion = "";

if (isset($_GET['PATH_INFO'])) {
    $peticion = explode('/', $_GET['PATH_INFO']);
}

// Verificar si la petición es RESTful
$auth = new Authenticator();
$isRestfulRequest = $auth->isRestfulRequest();

//if (!$isRestfulRequest) {
    try {
        if($isRestfulRequest){
            (new VistaJson())->imprimir((new SessionController())->get());
        } else {
            $router->route($uri, $method);
        }
    } catch (Exception $exception) {
        Session::flash('errors', $exception->errors);
        Session::flash('old', $exception->old);
        return redirect($router->previousUrl());
    }
//} else {
////    try {
////        $vista = new VistaJson();
////        $respuesta = call_user_func($router->post($uri, returnRoute($uri)));
////        $vista->imprimir($respuesta);
////    } catch (Exception $exception) {
////        $vista = new VistaJson(); // Usar la vista JSON para errores también
////        $errorResponse = [
////            'error' => $exception->getMessage(),
////            'errors' => $exception->errors ?? null,
////            'old' => $exception->old ?? null,
////        ];
////        $vista->imprimir($errorResponse);
////    }
//}

Session::unflash();
