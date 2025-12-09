<?php

namespace Core;
require __DIR__ . '/../vendor/autoload.php';

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Authenticator
{
    private static array $revokedTokens = [];
    private static string $signing_key = "18bcfaba79f47927dd54f7facc221b79f3e7212824e1bb5ef89fc927980ee8a6"; //secret del token

    public function attempt($email, $password): array|bool
    {

        $user = App::resolve(Database::class)
            ->query('select * from users where email = :email', [
                'email' => $email
            ])->find();



        if ($user) {
            if (password_verify($password, $user['password'])) {
                $this->login($user);

                if($this->isRestfulRequest()){
                    $token = $this->generateToken($user['id'], $user['email']); //creació del token

                    setcookie('token', $token, time() + (60 * 60 * 24), '/'); //guardar el token amb les cookies amb un timeout
                }

                return true;
            }
        }

        return false;
    }

    public function login($user): void
    {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email']
        ];
        session_regenerate_id(true);

    }


    public function logout(): void
    {

        setcookie('token', '', time() - 3600, '/'); //eliminar el token de les cookies del navegador

        $currentToken = $_SESSION['token'] ?? null;

        if ($currentToken) {
            self::$revokedTokens[] = $currentToken; //si el token existeix, guardarlo a revokedTokens al fer log out
        }

        Session::destroy();
        header('location: /');
    }

    public function getCurrentUserId(): ?int
    {
        return Session::get('user')['id'] ?? null;
    }

    // Funció per generar un token JWT.
    function generateToken($user_id, $user_email): string
    {
        $header = [
            "alg" => "HS256",
            "typ" => "JWT",
        ];
        $header = $this->base64_url_encode(json_encode($header));
        $payload =  [
            "exp" => time() + 3600,
            "sub" => $user_id,
            "user_email" => $user_email
        ];

        $payload = $this->base64_url_encode(json_encode($payload));

        $signature = $this->base64_url_encode(hash_hmac('sha256', "$header.$payload", self::$signing_key, true));

        return "$header.$payload.$signature";
    }

    function base64_url_encode($text):String{
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
    }


    function verifyToken($token): ?string
    {

        if (in_array($token, self::$revokedTokens)) {
            return null;
        }

        try {
            $key = new Key(self::$signing_key, 'HS256');

            $decoded = JWT::decode($token, $key);

            return $decoded->sub;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }



    public function isRestfulRequest(): bool
    {

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (str_contains($accept, 'application/json')) {
            return true;
        }

        if (str_contains($contentType, 'application/json')) {
            return true;
        }

        if (str_starts_with($authHeader, 'Bearer ')) {
            return true;
        }

        return false;
    }

}