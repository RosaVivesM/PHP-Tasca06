<?php

namespace Core;

class ApiToken
{
    private Database $db;

    public function __construct()
    {
        $this->db = App::resolve(Database::class);
    }

    public function generateToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));

        $this->db->query(
            'INSERT INTO api_tokens (user_id, token) VALUES (:user_id, :token)',
            [
                'user_id' => $userId,
                'token' => $token,
            ]
        );
        return $token;
    }

    public function getUserFromToken(?string $token){
        if(!$token){
            return null;
        }

        $row = $this->db
            ->query('SELECT user_id FROM api_tokens WHERE token = :token LIMIT 1', [
                'token' => $token
            ])
            ->find();

        if(!$row){
            return null;
        }

        return (int)$row['user_id'];
    }

    public function deleteToken(string $token)
    {
        $this->db->query(
            'DELETE FROM api_tokens WHERE token = :token',
            ['token' => $token]
        );
    }
}