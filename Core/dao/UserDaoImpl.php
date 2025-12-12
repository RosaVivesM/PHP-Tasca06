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

    public function updateUserById(int $id, array $data)
    {

        if(empty($data)){
            return null;
        }

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

    public function findUserByEmail(string $email){
        if($email == null){
            return null;
        }

        return $this->db->query('select * from users where email = :email', [
            'email' => $email
        ])->find();
    }

    public function insertUser(string $email, string $password)
    {
        if($email == null || $password == null){
            return null;
        }

        return $this->db->query('INSERT INTO users(email, password) VALUES(:email, :password)', [
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT)
        ]);
    }
}