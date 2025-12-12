<?php

namespace Core;
require __DIR__ . '/../vendor/autoload.php';

use Core\DAO\UserDaoImpl;

class Authenticator
{
    //private static string $signing_key = "18bcfaba79f47927dd54f7facc221b79f3e7212824e1bb5ef89fc927980ee8a6"; //secret del token

    public function attempt($email, $password): bool
    {

        $user = App::resolve(Database::class)
            ->query('select * from users where email = :email', [
                'email' => $email
            ])->find();

//        $user = UserDaoImpl::class->findUserByEmail($email);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $this->login($user);

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
        Session::destroy();
        header('location: /');
    }

    public function getCurrentUserId(): ?int
    {
        return Session::get('user')['id'] ?? null;
    }

    // Funció per generar un token JWT.
//    function generateToken($user_id, $user_email): string
//    {
//        $header = [
//            "alg" => "HS256",
//            "typ" => "JWT",
//        ];
//        $header = $this->base64_url_encode(json_encode($header));
//        $payload =  [
//            "exp" => time() + 3600,
//            "sub" => $user_id,
//            "user_email" => $user_email
//        ];
//
//        $payload = $this->base64_url_encode(json_encode($payload));
//
//        $signature = $this->base64_url_encode(hash_hmac('sha256', "$header.$payload", self::$signing_key, true));
//
//        return "$header.$payload.$signature";
//    }
//
//    function base64_url_encode($text):String{
//        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
//    }
//
//
//    function verifyToken($token): ?string
//    {
//
//        if (in_array($token, self::$revokedTokens)) {
//            return null;
//        }
//
//        try {
//            $key = new Key(self::$signing_key, 'HS256');
//
//            $decoded = JWT::decode($token, $key);
//
//            return $decoded->sub;
//        } catch (Exception $e) {
//            echo "Error: " . $e->getMessage();
//            return null;
//        }
//    }
//
//
//


}