<?php

namespace Http\controllers\session;

use Core\Authenticator;
use Core\Session;
use Exception;
use Http\Forms\LoginForm;
use JetBrains\PhpStorm\NoReturn;


class SessionController
{
    private Authenticator $auth;

    public function __construct()
    {
        $this->auth = new Authenticator();
    }

    public function get(): void
    {
        view('session/create.view.php', [
            'errors' => Session::get('errors')
        ]);

    }

    public function post(): void
    {
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if ($email === null || $password === null) {
            throw new Exception('Email and password are required.');
        }

        // Puedes aplicar validaciones adicionales aquí
        $form = LoginForm::validate($attributes = [
            'email' => trim($email),
            'password' => trim($password)
        ]);

        $signedIn = $this->auth->attempt(
            $attributes['email'], $attributes['password']
        );

        if (!$signedIn) {
            $form->error(
                'email', 'No matching account found for that email address and password.'
            )->throw();
        }

        // Redirección al homepage
        redirect('/');
    }

    #[NoReturn]
    public function delete(): void
    {
        $this->auth->logout();

        header('location: /');
        exit();
    }
}