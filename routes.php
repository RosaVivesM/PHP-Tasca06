<?php

use Http\controllers\notes\NotesApiController;
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

$router->get('/login', 'session/create.php')->only('guest');
$router->post('/session', 'session/store.php')->only('guest');
$router->delete('/session', 'session/destroy.php')->only('auth');

// Rest
$router->post('/api/session/login', [SessionController::class, 'apiLogin']);
$router->post('/api/session/logout', [SessionController::class, 'apiLogout']);

//notes with rest
$router->get('/api/notes', [NotesApiController::class, 'index']);
$router->get('/api/note', [NotesApiController::class, 'show']);
$router->post('/api/notes', [NotesApiController::class, 'store']);
$router->put('/api/note', [NotesApiController::class, 'update']);
$router->delete('/api/note', [NotesApiController::class, 'destroy']);