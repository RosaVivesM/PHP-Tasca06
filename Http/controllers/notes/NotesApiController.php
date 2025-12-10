<?php

namespace Http\controllers\notes;

use Core\ApiToken;
use Core\Authenticator;
use Core\DAO\NoteDao;
use Core\DAO\NoteDaoFactory;
use Core\Response;
use Core\Validator;

class NotesApiController
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

    private function requireAuth(): void
    {
        $tokenService = new ApiToken();
        $token = get_bearer_token();
        $userId = $tokenService->getUserFromToken($token);

        if (!$userId) {
            Response::json(['error' => 'Invalid or not existing content'], Response::UNAUTHORIZED);
        }

        $this->currentUserId = $userId;
    }

    private function authorizeNoteOwner(array $note): void
    {
        if ($note['user_id'] != $this->currentUserId) {
            Response::json(['error' => 'No authorized'], Response::FORBIDDEN);
        }
    }

    function index(): void
    {

        $this->requireAuth();

        $notes = $this->noteDao->getAllByUserId($this->currentUserId);

        Response::json(['notes' => $notes]);

    }

    function create(): void
    {
        Response::json(["message" => "Not disponible as a Rest request"]);
    }

    function store(): void
    {

        $errors = [];

        $this->requireAuth();


        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? [];
        $body = $data['body'] ?? '';

        if (! Validator::string($body, 1, 1000)) {
            $errors['body'] = 'A body of no more than 1,000 characters is required.';
        }

        if (! empty($errors)) {

            Response::json(['errors' => $errors], 422);

            return;
        }

        $this->noteDao->create($body, $this->currentUserId);


        Response::json(['message' => 'Note correctly created']);

    }

    function show()
    {
        $this->requireAuth();

        $id = (int)($_GET['id'] ?? 0);

        if ($id === 0) {

            Response::json(['error' => 'ID required'], Response::BAD_REQUEST);

            return;
        }

        $note = $this->noteDao->findById($id);

        if (!$note) {
            Response::json(['error' => 'Note not found'], Response::NOT_FOUND);

            return;
        }

        $this->authorizeNoteOwner($note);

        Response::json(['note' => $note]);

    }

    function edit():void
    {

        Response::json(['error' => 'Not disponible as a Rest request'], 405);
    }

    function destroy(): void
    {

        $this->requireAuth();

        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? [];
        $id = isset($data['id']) ? (int)$data['id'] : 0;

        if ($id === 0) {
            $id = (int)($_GET['id'] ?? 0);
        }

        if ($id === 0) {
                Response::json(['error' => 'ID requerido'], Response::BAD_REQUEST);

            return;
        }

        $note = $this->noteDao->findById($id);
        if (!$note) {
            if (isRestfulRequest()) {
                Response::json(['error' => 'Nota no encontrada'], Response::NOT_FOUND);
            } else {
                abort(Response::NOT_FOUND);
            }
            return;
        }

        $this->authorizeNoteOwner($note);

        $this->noteDao->delete($id);

        if (isRestfulRequest()) {
            Response::json(['message' => 'Nota eliminada']);
        } else {
            redirect('/notes');
        }
    }

    function update(): void
    {
        $this->requireAuth();

        if (isRestfulRequest()) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?? [];
            $id = (int)($_GET['id'] ?? 0);
            $body = $data['body'] ?? '';
        } else {
            $id = (int)($_POST['id'] ?? 0);
            $body = $_POST['body'] ?? '';
        }

        if ($id === null) {
            if (isRestfulRequest()) {
                Response::json(['error' => 'ID required'], Response::BAD_REQUEST);
            } else {
                abort(Response::NOT_FOUND);
            }
            return;
        }

        $note = $this->noteDao->findById($id);
        if (!$note) {
            if (isRestfulRequest()) {
                Response::json(['error' => 'Note not found'], Response::NOT_FOUND);
            } else {
                abort(Response::NOT_FOUND);
            }
            return;
        }

        $this->authorizeNoteOwner($note);

        $errors = [];

        if (!Validator::string($body, 1, 1000)) {
            $errors['body'] = 'El texto debe tener máximo 1000 caracteres';
        }

        if (!empty($errors)) {
            if (isRestfulRequest()) {
                Response::json(['errors' => $errors], 422);
            } else {
                view('notes/edit.view.php', [
                    'heading' => 'Editar nota',
                    'errors' => $errors,
                    'note' => $note,
                ]);
            }
            return;
        }

        $this->noteDao->update($id, $body);

        if (isRestfulRequest()) {
            Response::json(['message' => 'Nota actualizada']);
        } else {
            redirect('/notes');
        }
    }
}

