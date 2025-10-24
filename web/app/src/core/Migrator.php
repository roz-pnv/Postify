<?php

namespace App\Core;

use Exception;
use PDO;

use Psr\Container\ContainerExceptionInterface;
use Psr\Log\LoggerInterface;

use App\Infrastructure\Persistence\DatabaseConnection;

class Migrator
{
    private PDO $pdo;
    private string $migrationsPath;
    private LoggerInterface $logger;

    /**
     * Migrator constructor.
     *
     * @param string $migrationsPath Path to migration files
     *
     * @throws ContainerExceptionInterface
     * @throws Exception
     */
    public function __construct(string $migrationsPath)
    {
        try {
            $container = Bootstrap::getContainer();

            $dbService = $container->get(DatabaseConnection::class);
            $this->pdo = $dbService->getConnection();

            $this->logger = $container->get(LoggerInterface::class);
        } catch (ContainerExceptionInterface | Exception $e) {
            error_log('Migrator::__construct error: ' . $e->getMessage());
            throw $e;
        }

        $this->migrationsPath = $migrationsPath;
    }

    public function run(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $executed = $this->pdo
            ->query("SELECT name FROM migrations")
            ->fetchAll(PDO::FETCH_COLUMN);

        $files = glob($this->migrationsPath . '/*.php');

        foreach ($files as $file) {
            $name = basename($file);

            try {
                if (!in_array($name, $executed)) {
                    $migration = require $file;

                    if (is_callable($migration)) {
                        $migration = $migration($this->pdo, $this->logger);
                    }

                    if (!method_exists($migration, 'up')) {
                        $this->logger->error("Invalid migration: $name");
                        continue;
                    }

                    $migration->up();

                    $stmt = $this->pdo->prepare(
                        "INSERT INTO migrations (name) VALUES (:name)"
                    );
                    $stmt->execute(['name' => $name]);

                    $this->logger->info("Migration executed: $name");
                }
            } catch (Exception $e) {
                $this->logger->error("Migration failed for $name: " . $e->getMessage());
            }
        }
    }
}
