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

    public function requireAuth(): ?int
    {
        $tokenService = new ApiToken();
        $token = get_bearer_token();
        $userId = $tokenService->getUserFromToken($token);

        if (!$userId) {
            Response::json(['error' => 'Invalid or not existing content'], Response::UNAUTHORIZED);
        }

        if(!(new ApiToken)->verifyToken($token)){
            Response::json(['error' => 'Invalid token'], Response::UNAUTHORIZED);
        }

        return $userId;
    }


}