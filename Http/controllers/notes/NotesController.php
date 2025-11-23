<?php

namespace Http\controllers\notes;
use Core\App;
use Core\Authenticator;
use Core\Database;
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