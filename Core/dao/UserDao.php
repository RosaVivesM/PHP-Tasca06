<?php

namespace Core\DAO;

interface UserDao
{
    public function updateUserById($id, array $data);
    public function findUserByEmail($email);
    public function insertUser($email, $password);
}