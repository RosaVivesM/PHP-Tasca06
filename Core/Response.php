<?php

namespace Core;

class Response {
    const NOT_FOUND = 404;
    const FORBIDDEN = 403;
    const UNAUTHORIZED = 401;
    const BAD_REQUEST = 400;

    public static function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit();
    }
}