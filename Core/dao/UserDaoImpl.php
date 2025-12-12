<?php

namespace Core\DAO;

use Core\App;
use Core\Database;

class UserDaoImpl implements UserDao
{

    private $db;

    public function __construct()
    {
        $this->db = App::resolve(Database::class);
    }

    public function updateUserById($id, array $data)
    {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $params[] = $value;
        }

        $params[] = $id;

        return $this->db->query(
            "UPDATE users SET ".implode(', ', $fields)." WHERE id = ?",
            $params
        );
    }

    public function findUserByEmail($email){
        return $this->db->query('select * from users where email = :email', [
            'email' => $email
        ])->find();
    }

    public function insertUser($email, $password)
    {
        return $this->db->query('INSERT INTO users(email, password) VALUES(:email, :password)', [
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT)
        ]);
    }
}