<?php

use Core\Response;

function dd($value)
{
    echo "<pre>";
    var_dump($value);
    echo "</pre>";

    die();
}

function urlIs($value)
{
    return $_SERVER['REQUEST_URI'] === $value;
}

function abort($code = 404)
{
    if(isRestfulRequest()){
        $message = 'Error';

        switch ($code){
            case Response::NOT_FOUND: $message = 'Resource Not Found'; break;
            case Response::FORBIDDEN: $message = 'Access denied'; break;
            case Response::UNAUTHORIZED: $message = 'Not authenticated'; break;
        }

        Response::json([
            'error' => $message,
            'error' => $code,
        ], $code);
    }

    http_response_code($code);

    require base_path("views/{$code}.php");

    die();
}

function authorize($condition, $status = Response::FORBIDDEN)
{
    if (! $condition) {
        abort($status);
    }

    return true;
}

function base_path($path)
{
    return BASE_PATH . $path;
}

function view($path, $attributes = [])
{
    extract($attributes);

    require base_path('views/' . $path);
}

function redirect($path)
{
    header("location: {$path}");
    exit();
}

function old($key, $default = '')
{
    return Core\Session::get('old')[$key] ?? $default;
}

function isRestfulRequest(): bool
{
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return substr($path, 0, 5) === '/api/';
}