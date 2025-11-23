<?php

namespace Http\controllers\notes;
use Core\App;
use Core\Authenticator;
use Core\DAO\NoteDaoImpl;
use Core\Database;
use Core\Validator;
class NotesController
{
    private $db;

    private $noteDao;
    private Authenticator $auth;
    private ?int $currentUserId;

    public function __construct()
   {
       $this->db = App::resolve(Database::class);
       $this->auth = new Authenticator();
       $this->currentUserId = $this->auth->getCurrentUserId();
       $this->noteDao = new NoteDaoImpl();
   }

   function index(): void
   {
       $notes = $this->noteDao->getAllByUserId($this->currentUserId);

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

        $this->noteDao->create($_POST['body'], $this->currentUserId);

        header('location: /notes');
        die();
    }

    function show(){

        $note = $this->noteDao->findById( $_GET['id']);

        authorize($note['user_id'] === $this->currentUserId);

        view("notes/show.view.php", [
            'heading' => 'Note',
            'note' => $note
        ]);
    }

    function edit(){

        $note = $this->noteDao->findById($_GET['id']);

        authorize($note['user_id'] === $this->currentUserId);

        view("notes/edit.view.php", [
            'heading' => 'Edit Note',
            'errors' => [],
            'note' => $note
        ]);
    }

    function destroy(): void
    {

        $note = $this->noteDao->findById($_POST['id']);

        authorize($note['user_id'] === $this->currentUserId);

        $this->noteDao->delete($_POST['id']);

        header('location: /notes');
        exit();
    }

    function update()
    {
// find the corresponding note
        $note = $this->noteDao->findById($_POST['id']);

// authorize that the current user can edit the note
        authorize($note['user_id'] === $this->currentUserId);

// validate the form
        $errors = [];

        if (! Validator::string($_POST['body'], 1, 10)) {
            $errors['body'] = 'A body of no more than 1,000 characters is required.';
        }

// if no validation errors, update the record in the notes database table.
        if (count($errors)) {
            return view('notes/edit.view.php', [
                'heading' => 'Edit Note',
                'errors' => $errors,
                'note' => $note
            ]);
        }

        $this->noteDao->update($_POST['id'], $_POST['body']);

// redirect the user
        header('location: /notes');
        die();

    }
}