<?php

namespace Core\DAO;

use Core\App;
use Core\Database;
class NoteDaoImpl implements NoteDao
{
    private $db;

    public function __construct()
    {
        $this->db = App::resolve(Database::class);
    }
    public function getAllByUserId(int $userId): array
    {
        return $this->db->query('select * from notes where user_id = :id', [
            'id' => $userId
        ])->get();
    }

    public function findById(int $id): ?array
    {
        // TODO: Implement findById() method.
        return [];
    }

    public function create(string $body, int $userId): void
    {
        // TODO: Implement create() method.
    }

    public function update(int $id, string $body): void
    {
        // TODO: Implement update() method.
    }

    public function delete(int $id): void
    {
        // TODO: Implement delete() method.
    }
}
