<?php

namespace Http\controllers\notes;
use Core\App;
use Core\Authenticator;
use Core\Database;
use Core\Validator;
class NotesController
{
    private $db;
    private Authenticator $auth;
    private ?int $currentUserId;

    public function __construct()
   {
       $this->db = App::resolve(Database::class);
       $this->auth = new Authenticator();
       $this->currentUserId = $this->auth->getCurrentUserId();
   }

   function index(): void
   {
       $notes = $this->db->query('select * from notes where user_id = :id', [
           'id' => $this->currentUserId
       ])->get();

       view("notes/index.view.php", [
           'heading' => 'My Notes',
           'notes' => $notes
       ]);
   }

    function create(): void
    {
        view("notes/create.view.php", [
            'heading' => 'Create Note',
            'errors' => []
        ]);
    }

    function store(){

        $errors = [];

        if (! Validator::string($_POST['body'], 1, 1000)) {
            $errors['body'] = 'A body of no more than 1,000 characters is required.';
        }

        if (! empty($errors)) {
            return view("notes/create.view.php", [
                'heading' => 'Create Note',
                'errors' => $errors
            ]);
        }

        $this->db->query('INSERT INTO notes(body, user_id) VALUES(:body, :user_id)', [
            'body' => $_POST['body'],
            'user_id' => $this->currentUserId
        ]);

        header('location: /notes');
        die();
    }

//    function delete(): void
//    {
//
//        $note = $this->db->query('select * from notes where id = :id', [
//            'id' => $_POST['id']
//        ])->findOrFail();
//
//        authorize($note['user_id'] === $this->currentUserId);
//
//        $this->db->query('delete from notes where id = :id', [
//            'id' => $_POST['id']
//        ]);
//
//        header('location: /notes');
//        exit();
//    }
}