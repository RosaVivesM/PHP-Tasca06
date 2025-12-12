<?php

namespace Core;

use DateTimeImmutable;
use Exception;
use Random\RandomException;

class ApiToken
{
    private Database $db;
    private int $expirationTime;

    public function __construct(int $expirationTime = 60) // tiempo de vida del token en segundos, esto seria 1h
    {
        $this->db = App::resolve(Database::class);
        $this->expirationTime = $expirationTime;
    }

    /**
     * @throws RandomException
     */
    public function generateToken(int $userId): string
    {

        $token = bin2hex(random_bytes(32));

        $expiresAt = (new DateTimeImmutable(
            "+$this->expirationTime seconds"))
            ->format('U');

        $this->db->query(
            'INSERT INTO api_tokens (user_id, token, expires_at)
             VALUES (:user_id, :token, :expires_at)',
            [
                'user_id' => $userId,
                'token' => $token,
                'expires_at' => $expiresAt,
            ]
        );

        return $token;

    }

    public function getUserFromToken(?string $token): ?int
    {
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

    public function deleteToken(string $token): ?bool
    {

        if($token == null){
            return null;
        }

        $result = $this->db->query(
            'DELETE FROM api_tokens WHERE token = :token',
            ['token' => $token]
        );

        return $result !== false && $this->db->statement->rowCount() > 0;
    }

    public function deleteAllTokensForUser(int $userId): void {//elimina todos los tokens de un usuario

        $this->db->query(
            'DELETE FROM api_tokens WHERE user_id = :user_id',
            ['user_id' => $userId]
        );

    }

    public function verifyToken(String $token): ?bool
    {
        if($token == null){
            return null;
        }

        $row = $this->db->query(
            'SELECT expires_at FROM api_tokens WHERE token = :token LIMIT 1',
            ['token' => $token]
        )->find();


        $expiration_date = (string)$row['expires_at'];

        if(date('U') > $expiration_date){

            $this->deleteToken($token);
            return false;
        }

        return true;
    }
}