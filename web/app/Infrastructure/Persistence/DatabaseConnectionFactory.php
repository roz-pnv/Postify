<?php

namespace App\Infrastructure\Persistence;

use PDO;
use PDOException;

use Psr\Log\LoggerInterface;

class DatabaseConnectionFactory
{
    private array $config;
    private LoggerInterface $logger;

    public function __construct(array $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function create(): PDO
    {
        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=%s',
            $this->config['driver'],
            $this->config['host'],
            $this->config['port'],
            $this->config['Database'],
            $this->config['charset']
        );

        try {
            $pdo = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            $this->logger->info(sprintf(
                "DatabaseConnection connected to '%s' on %s:%s as user '%s'.",
                $this->config['Database'],
                $this->config['host'],
                $this->config['port'],
                $this->config['username']
            ));

            return $pdo;
        } catch (PDOException $e) {
            $this->logger->error('DatabaseConnection connection failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
