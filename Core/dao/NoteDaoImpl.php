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
        return $this->db->query('select * from notes where id = :id', [
            'id' => $id
        ])->findOrFail();

    }

    public function create(string $body, int $userId): void
    {
        $this->db->query('INSERT INTO notes(body, user_id) VALUES(:body, :user_id)', [
            'body' => $body,
            'user_id' => $userId
        ]);
    }

    public function update(int $id, string $body): void
    {
        // TODO: Implement update() method.
    }

    public function delete(int $id): void
    {
        $this->db->query('delete from notes where id = :id', [
            'id' => $id
        ]);
    }
}
