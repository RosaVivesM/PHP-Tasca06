<?php

use Core\Response;
use JetBrains\PhpStorm\NoReturn;

#[NoReturn]
function dd($value): void
{
    echo "<pre>";
    var_dump($value);
    echo "</pre>";

    die();
}

function urlIs($value): bool
{
    return $_SERVER['REQUEST_URI'] === $value;
}

#[NoReturn]
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
            'code' => $code,
        ], $code);
    }

    http_response_code($code);

    require base_path("views/$code.php");

    die();
}

function authorize($condition, $status = Response::FORBIDDEN): bool
{
    if (! $condition) {
        abort($status);
    }

    return true;
}

function base_path($path): string
{
    return BASE_PATH . $path;
}

function view($path, $attributes = []): void
{
    extract($attributes);

    require base_path('views/' . $path);
}

#[NoReturn]
function redirect($path): void
{
    header("location: $path");
    exit();
}

function old($key, $default = '')
{
    return Core\Session::get('old')[$key] ?? $default;
}

function isRestfulRequest(): bool
{
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return str_starts_with($path, '/api/');
}

function get_bearer_token(): ?string
{
    if (!function_exists('getallheaders')) {
        return null;
    }

    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;

    if (!$auth) {
        return null;
    }

    if (stripos($auth, 'Bearer ') === 0) {
        return substr($auth, 7);
    }

    return null;
}
