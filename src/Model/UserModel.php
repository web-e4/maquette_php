<?php
declare(strict_types=1);

namespace App\Model;

use PDO;

class UserModel
{
    public function __construct(private PDO $pdo) {}

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function register(string $name, string $email, string $password): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, password, created_at)
             VALUES (:name, :email, :password, NOW())'
        );
        $stmt->execute([
            ':name'     => $name,
            ':email'    => $email,
            ':password' => password_hash($password, PASSWORD_BCRYPT),
        ]);
        return (int) $this->pdo->lastInsertId();
    }
}
