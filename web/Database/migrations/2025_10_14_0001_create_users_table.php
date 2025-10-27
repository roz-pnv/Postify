<?php

use Psr\Log\LoggerInterface;

return function (PDO $db, LoggerInterface $logger) {
    return new class($db, $logger) {
        private PDO $db;
        private LoggerInterface $logger;

        public function __construct(PDO $db, LoggerInterface $logger) {
            $this->db = $db;
            $this->logger = $logger;
        }

        public function up(): void {
            try {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS users (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        username VARCHAR(100) NOT NULL,
                        email VARCHAR(150) UNIQUE NOT NULL,
                        password VARCHAR(255) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    );
                ");
                $this->logger->info('Migration Created users table successfully.');
            } catch (Exception $e) {
                $this->logger->error('Migration Failed to create users table: ' . $e->getMessage());
            }
        }

        public function down(): void {
            try {
                $this->db->exec("DROP TABLE IF EXISTS users;");
                $this->logger->info('Migration Dropped users table successfully.');
            } catch (Exception $e) {
                $this->logger->error('Migration Failed to drop users table: ' . $e->getMessage());
            }
        }
    };
};
