<?php

//use Core\App;
//use Core\Authenticator;
//use Core\Database;
//use Http\controllers\notes\NotesController;
//
//var_dump((new Core\Authenticator)->getCurrentUserId())

//var_dump(SESSION::get('user')['email'] ?? null)
(new Http\controllers\notes\NotesController)->index();

//$db = App::resolve(Database::class);
//$notes = $db->query('select * from notes where user_id = 6')->get();
//
//view("notes/index.view.php", [
//    'heading' => 'My Notes',
//    'notes' => $notes
//]);