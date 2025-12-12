<?php

namespace Http\controllers\session;

ob_start();

use Core\ApiToken;
use Core\Authenticator;
use Core\Response;

class SessionController
{
    private Authenticator $auth;
    private ApiToken $tokens;

    public function __construct()
    {
        $this->auth = new Authenticator();
        $this->tokens = new ApiToken();
    }

    public function apiLogin(): void
    {
        if(!isRestfulRequest()){
            abort(Response::NOT_FOUND);
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if(!is_array($data)){
            $data = $_POST;
        }

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if(!$email || !$password){
            Response::json(
                ['error' => 'required email and password'],
                Response::BAD_REQUEST
            );
        }

        $signedIn = $this->auth->attempt($email, $password);

        if(!$signedIn){
            Response::json(
                ['error' => 'Incorrect credentials'],
                Response::UNAUTHORIZED
            );
        }

        $userId = $this->auth->getCurrentUserId();

        $token = $this->tokens->generateToken($userId);

        Response::json([
            'token' => $token,
            'user'  => [
                'id'    => $userId,
                'email' => $email,
            ],
        ]);
    }

    public function apiLogout(): void
    {
        if (!isRestfulRequest()) {
            abort(Response::NOT_FOUND);
        }

        $token = get_bearer_token();

        if (!$token) {
            Response::json(
                ['error' => 'Token not recived'],
                Response::BAD_REQUEST
            );
        }

        $this->tokens->deleteToken($token);

        Response::json(['message' => 'REST Session correctly closed']);
    }

    public function apiLogoutAll(): void
    {
        if (!isRestfulRequest()) {
            abort(Response::NOT_FOUND);
        }

        $token = get_bearer_token();

        if (!$token) {
            Response::json(
                ['error' => 'Token not recived'],
                Response::BAD_REQUEST
            );
        }

        $this->tokens->deleteAllTokensForUser($this->tokens->getUserFromToken($token));

        Response::json(['message' => 'REST Session correctly closed']);
    }
}