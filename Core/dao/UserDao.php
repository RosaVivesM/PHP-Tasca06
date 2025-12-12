<?php

namespace Core\DAO;

interface UserDao
{
    public function updateUserById(int $id, array $data);
    public function findUserByEmail(string $email);
    public function insertUser(string $email, string $password);
}