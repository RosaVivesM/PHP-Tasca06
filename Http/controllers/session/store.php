<?php

use Core\Authenticator;
use Http\Forms\LoginForm;

// Validar y asegurar que los datos han sido enviados
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

$signedIn = (new Authenticator)->attempt(
    $attributes['email'], $attributes['password']
);

if (!$signedIn) {
    $form->error(
        'email', 'No matching account found for that email address and password.'
    )->throw();
}

// Redirección al homepage
redirect('/');
