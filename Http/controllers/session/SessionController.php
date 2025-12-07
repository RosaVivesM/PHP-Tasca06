<?php

namespace Http\controllers\session;

ob_start();
use Core\Authenticator;
use Core\Session;
use Http\Forms\LoginForm;


class SessionController
{
    private Authenticator $auth;

    public function __construct()
    {
        $this->auth = new Authenticator();
    }

    public function get(): string|bool
    {
        view('session/create.view.php', [
            'errors' => Session::get('errors')
        ]);

        return json_encode([
            'errors' => Session::get('errors')
        ]);

    }

    public function post(): string
    {

        $restful = $this->auth->isRestfulRequest();
        // Inicializar variables
        $email = null;
        $password = null;

        // Manejar solicitudes JSON
        if ($restful) {
            $data = json_decode(file_get_contents('php://input'), true);
            $email = $data['email'] ?? null;
            $password = $data['password'] ?? null;
        } else { // Manejar solicitudes de formulario
            $email = $_POST['email'] ?? null;
            $password = $_POST['password'] ?? null;
        }

        // Validar que ambos campos son proporcionados
        if ($email === null || $password === null) {
            http_response_code(400);
            return json_encode(['error' => 'Email and password are required.']);
        }

        // Validar las credenciales
        $form = LoginForm::validate($attributes = [
            'email' => trim($email),
            'password' => trim($password)
        ]);

        $signedIn = $this->auth->attempt(
            $attributes['email'], $attributes['password']
        );

        // Manejo de la respuesta de inicio de sesión
        if (!$signedIn) {
            http_response_code(401);
            return json_encode(['error' => 'No matching account found for that email address and password.']);
        }

        if(!$restful)redirect('/');

        // Respuesta exitosa
        http_response_code(200);
        return json_encode(['message' => 'Session iniciada']);
    }


    public function delete(): bool|string
    {

        $this->auth->logout();

        if($this->auth->isRestfulRequest()){
            http_response_code(200);
            return json_encode(['message' => 'Session cerrada']);
        }

        header('location: /');
        exit();
    }
}
ob_end_flush();