<?php

namespace Core\DAO;

interface NoteDao
{
    public function getAllByUserId(int $userId): array;

    public function findById(int $id): ?array;

    public function create(string $body, int $userId): void;

    public function update(int $id, string $body): void;

    public function delete(int $id): void;

}