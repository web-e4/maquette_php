<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(array $config): PDO
    {
        if (self::$instance === null) {
            self::$instance = new PDO(
                $config['dsn'],
                $config['user'],
                $config['password'],
                $config['options']
            );
        }
        return self::$instance;
    }
}
