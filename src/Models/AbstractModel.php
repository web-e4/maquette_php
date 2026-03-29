<?php
// src/Models/AbstractModel.php

namespace Equipe4\Gigastage\Models;

use Equipe4\Gigastage\Core\Database;
use PDO;

abstract class AbstractModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = (new Database())->getConnection();
    }

    protected function getConnection(): PDO
    {
        return $this->pdo;
    }
}