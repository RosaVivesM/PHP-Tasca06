<?php

namespace Http\controllers\notes;
ob_start();
use Core\Authenticator;
use Core\DAO\NoteDao;
use Core\DAO\NoteDaoFactory;
use Core\Validator;

class NotesController
{
    private NoteDao $noteDao;
    private Authenticator $auth;
    private ?int $currentUserId;

    public function __construct()
   {
       $this->auth = new Authenticator();
       $this->currentUserId = $this->auth->getCurrentUserId();
       $this->noteDao = NoteDaoFactory::create();
   }

    function index(): ?array
    {

        if ($this->auth->isRestfulRequest()) {
            $this->currentUserId = $this->auth->verifyToken($_COOKIE['token']);

            if($this->currentUserId != null){
                $notes = $this->noteDao->getAllByUserId($this->currentUserId);;

                return (['id' => $notes]);

            }
        } else {
            $notes = $this->noteDao->getAllByUserId($this->currentUserId);

            view("notes/index.view.php", [
                'heading' => 'My Notes',
                'notes' => $notes
            ]);
        }

        return null;
    }

    function create(): ?array
    {
        if($this->auth->isRestfulRequest() && $this->auth->verifyToken($_COOKIE['token'])){
            return ["message" => "Not disponible as a Rest request"];
        }

        view("notes/create.view.php", [
            'heading' => 'Create Note',
            'errors' => []
        ]);

        return null;
    }

    function store()
    {

        $errors = [];

        $auth = $this->auth->isRestfulRequest() && $this->auth->verifyToken($_COOKIE['token']);

        if ($auth) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?? [];
            $body = $data['body'] ?? '';
        } else {
            $body = $_POST['body'];
        }

        if (! Validator::string($body, 1, 1000)) {
            $errors['body'] = 'A body of no more than 1,000 characters is required.';
        }

        if (! empty($errors)) {
            if($auth){
                return json_decode([
                    "status" => 422,
                    "error" => $errors]);
            }
            return view("notes/create.view.php", [
                'heading' => 'Create Note',
                'errors' => $errors
            ]);
        }

        $this->noteDao->create($body, $this->currentUserId);

        header('location: /notes');
        die();
    }

    function show(){

        $note = $this->noteDao->findById( $_GET['id']);

        authorize($note['user_id'] === $this->currentUserId);

        if ($this->auth->isRestfulRequest()) {
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode($note);
            exit();
        } else {
            view("notes/show.view.php", [
                'heading' => 'Note',
                'note' => $note
            ]);
        }
    }

    function edit(): ?array
    {

        if($this->auth->isRestfulRequest() && $this->auth->verifyToken($_COOKIE['token'])){
            return ["message" => "Not disponible as a Rest request"];
        }

        $note = $this->noteDao->findById($_GET['id']);

        authorize($note['user_id'] === $this->currentUserId);

        view("notes/edit.view.php", [
            'heading' => 'Edit Note',
            'errors' => [],
            'note' => $note
        ]);

        return null;
    }

    function destroy(): void
    {

        $note = $this->noteDao->findById($_POST['id']);

        authorize($note['user_id'] === $this->currentUserId);

        $this->noteDao->delete($_POST['id']);

        header('location: /notes');
        exit();
    }


    //TODO: arreglar que si cerc un id inexistend no doni 404
    function update()
    {

        $auth = $this->auth->isRestfulRequest() && ($this->auth->verifyToken($_COOKIE['token']) != null);


        if ($auth) {
            $note = $this->noteDao->findById($_GET['id']);
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?? [];
            $body = $data['body'] ?? '';
        } else {
            $body = $_POST['body'];
            $note = $this->noteDao->findById($_POST['id']);
        }
// find the corresponding note


        if($note == null){
            if($auth){
                return ['message' => 'Note not found'];
            }
        }

// authorize that the current user can edit the note
        authorize($note['user_id'] === $this->currentUserId);

// validate the form
        $errors = [];

        if (! Validator::string($body, 1, 10)) {
            $errors['body'] = 'A body of no more than 1,000 characters is required.';
        }

// if no validation errors, update the record in the notes database table.
        if (count($errors)) {
            if($auth){
                return json_decode([
                    "status" => 422,
                    "error" => $errors]);
            }
            return view('notes/edit.view.php', [
                'heading' => 'Edit Note',
                'errors' => $errors,
                'note' => $note
            ]);

        }

        $this->noteDao->update($note['id'], $body);

        if($auth){
            return [
                'message' => 'Note actualized',
                'note' => $note,
            ];
        } else {
            // redirect the user
            header('location: /notes');
            die();
        }
    }
}
ob_end_flush();