<?php
// src/Core/Database.php

namespace Equipe4\Gigastage\Core;

use PDO;
use PDOException;

class Database
{
    private PDO $pdo;

    public function __construct()
    {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? '';
        $user = $_ENV['DB_USER'] ?? '';
        $password = $_ENV['DB_PASSWORD'] ?? '';

        try {
            $this->pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8",
                $user,
                $password,
				[
					// exception au lieu de juste retourner false
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					// tableau associatif
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
					// interdir l'emulation de requêtes préparées
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
			$isDev = ($_ENV['APP_ENV'] ?? 'prod') === 'dev';
			// affiche erreur si dev
            die($isDev ? 'Database connection error: ' . $e->getMessage() : 'Une erreur est survenue. Veuillez réessayer plus tard.');
        }
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
