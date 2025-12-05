<?php

namespace Core;

use Exception;
use Firebase\JWT\JWT;
class Authenticator
{
    private static array $revokedTokens = [];
    private static string $signing_key = "18bcfaba79f47927dd54f7facc221b79f3e7212824e1bb5ef89fc927980ee8a6"; //secret del token

    public function attempt($email, $password)
    {
        $user = App::resolve(Database::class)
            ->query('select * from users where email = :email', [
            'email' => $email
        ])->find();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $this->login($user);

                return true;
            }
        }

        return false;
    }

    public function login($user)
    {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email']
        ];
        session_regenerate_id(true);

        $token = $this->generateToken($user['id'], $user['email']); //creació del token

        setcookie('token', $token, time() + (60 * 60 * 24), '/'); //guardar el token amb les cookies amb un timeout

    }

    public function logout()
    {

        setcookie('token', '', time() - 3600, '/'); //eliminar el token de les cookies del navegador

        $currentToken = $_SESSION['token'] ?? null;

        if ($currentToken) {
            self::$revokedTokens[] = $currentToken; //si el token existeix, guardarlo a revokedTokens al fer log out
        }

        Session::destroy();
        header('location: /');
        exit();
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
            "exp" => 0,
            "user_id" => $user_id,
            "user_email" => $user_email
        ];

        $payload = $this->base64_url_encode(json_encode($payload));

        $signature = $this->base64_url_encode(hash_hmac('sha256', "$header.$payload", self::$signing_key, true));

        return "$header.$payload.$signature";
    }

    function base64_url_encode($text):String{
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
    }

    // Funció para verificar un token JWT
    function verifyToken($token): bool
    {
        if (in_array($token, self::$revokedTokens)) {
            return false; // El token está revocat
        }

        try {
            $headers = ['HS256'];
            $decoded = JWT::decode($token, self::$key, $headers);
            $user_id = $decoded->sub;
            return array('success' => true, 'user_id' => $user_id);
        } catch (Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }
    }
}