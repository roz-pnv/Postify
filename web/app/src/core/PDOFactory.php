<?php
namespace App\core;

use PDO;
use PDOException;

class PDOFactory
{
    public static function create(array $config): PDO
    {
        $dsn = sprintf(
            "%s:host=%s;port=%s;dbname=%s;charset=%s",
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        $logger = Bootstrap::getLogger();

        try {
            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $logger->info("Database connected successfully to {$config['database']} on {$config['host']}:{$config['port']} as {$config['username']}");
            return $pdo;

        } catch (PDOException $e) {
            $logger->error("Database connection failed: " . $e->getMessage());
            throw $e;
        }
    }
}
