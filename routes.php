<?php

use Http\controllers\session\SessionController;
use Http\controllers\notes\NotesController;

$router->get('/', 'index.php');
$router->get('/about', 'about.php');
$router->get('/contact', 'contact.php');

$router->get('/notes', [NotesController::class, 'index'])->only('auth');
$router->get('/note', [NotesController::class, 'show']);
$router->delete('/note', [NotesController::class, 'destroy']);

$router->get('/note/edit', [NotesController::class, 'edit']);
$router->patch('/note', [NotesController::class, 'update']);

$router->get('/notes/create', [NotesController::class, 'create']);
$router->post('/notes', [NotesController::class, 'store']);

$router->get('/register', 'registration/create.php')->only('guest');
$router->post('/register', 'registration/store.php')->only('guest');

$router->get('/login', [SessionController::class, 'get'])->only('guest');
$router->post('/session', [SessionController::class, 'post'])->only('guest');
$router->delete('/session', [SessionController::class, 'delete'])->only('auth');
