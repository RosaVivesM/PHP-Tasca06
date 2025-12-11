<?php

namespace Core;

class ApiToken
{
    private Database $db;
    private static string $signing_key = "18bcfaba79f47927dd54f7facc221b79f3e7212824e1bb5ef89fc927980ee8a6"; //secret del token

    public function __construct()
    {
        $this->db = App::resolve(Database::class);
    }

    public function generateToken(int $userId): string
    {
        $header = [
            "alg" => "HS256",
            "typ" => "JWT",
        ];
        $header = $this->base64_url_encode(json_encode($header));
        $payload =  [
            "exp" => time() + 3600,
            "sub" => $userId,
        ];

        $payload = $this->base64_url_encode(json_encode($payload));

        $signature = $this->base64_url_encode(hash_hmac('sha256', "$header.$payload", self::$signing_key, true));

        $token = "$header.$payload.$signature";


        $this->db->query(
            'INSERT INTO api_tokens (user_id, token) VALUES (:user_id, :token)',
            [
                'user_id' => $userId,
                'token' => $token,
            ]
        );
        return $token;
    }

    function base64_url_encode($text):String{
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
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

    public function deleteToken(string $token): bool
    {

        $result = $this->db->query(
            'DELETE FROM api_tokens WHERE token = :token',
            ['token' => $token]
        );

        return $result !== false && $this->db->statement->rowCount() > 0;
    }
}