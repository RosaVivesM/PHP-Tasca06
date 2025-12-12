<?php
namespace Http\controllers\users;

use Core\Authenticator;
use Core\DAO\UserDaoImpl;
use Core\Response;
use Throwable;


class UserController
{
    private Authenticator $auth;
    private ?int $currentUserId;
    private UserDaoImpl $userDao;

    public function __construct()
    {
        $this->auth = new Authenticator();
        $this->currentUserId = $this->auth->getCurrentUserId();
        $this->userDao = new UserDaoImpl();
    }

    private function updateUser(int $userId, array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        try {
            $this->userDao->updateUserById($userId, $data);
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function update(): void
    {
        $this->currentUserId = $this->auth->requireAuth();

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? [];
        $userCamps = ['phone', 'date_of_birth'];

        if ($this->currentUserId === null) {
            Response::json(['error' => 'ID required'], Response::BAD_REQUEST);
            return;
        }

        $errors = [];
        $updateData = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $userCamps, true)) {
                $updateData[$key] = $value;
            }
        }

        if (!empty($errors)) {
            Response::json(['errors' => $errors], Response::BAD_REQUEST);
            return;
        }

        if (!empty($updateData)) {
            $updateSuccess = $this->updateUser($this->currentUserId, $updateData);

            if ($updateSuccess) {
                Response::json(['message' => 'User updated successfully']);
            } else {
                Response::json(['error' => 'Error updating user'], 500);
            }
        } else {
            Response::json(['message' => 'No data to update'], 400);
        }
    }
}