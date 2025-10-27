<?php

namespace App\Infrastructure\Persistence;

use PDO;

class DatabaseConnection
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
