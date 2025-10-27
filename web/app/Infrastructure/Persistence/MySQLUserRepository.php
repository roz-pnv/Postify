<?php

namespace App\Infrastructure\Persistence;

use PDO;
use Throwable;

use Psr\Log\LoggerInterface;

use App\Domain\Contracts\UserRepositoryInterface;
use App\Domain\Models\User;

class MySQLUserRepository implements UserRepositoryInterface
{
    private PDO $pdo;
    private LoggerInterface $logger;

    public function __construct(DatabaseConnection $database, LoggerInterface $logger)
    {
        $this->pdo = $database->getConnection();
        $this->logger = $logger;
    }

    public function findByUsername(string $username): ?User
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM `users` WHERE `username` = :username");
            $stmt->execute(['username' => $username]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $this->logger->info("User found by username: '$username'");

                return new User(
                    $data['id'],
                    $data['username'],
                    $data['email'],
                    $data['password']
                );
            }

            $this->logger->info("No user found by username: '$username'");

            return null;
        } catch (Throwable $e) {
            $this->logger->error("Field to find user by username" . $e->getMessage());

            return null;
        }
    }

    public function findByEmail(string $email): ?User
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $this->logger->info("User found by email: $email");

                return new User(
                    (int) $data['id'],
                    $data['username'],
                    $data['email'],
                    $data['password']
                );
            }

            $this->logger->info("No user found with email: $email");

            return null;
        } catch (Throwable $e) {
            $this->logger->error("Failed to find user by email: " . $e->getMessage());

            return null;
        }
    }

    public function save(User $user): void
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO users (username, email, password, created_at) VALUES (:username, :email, :password, NOW())'
            );

            $stmt->execute([
                'username' => $user->username,
                'email' => $user->email,
                'password' => $user->password,
            ]);

            $this->logger->info("User saved: $user->email");

        } catch (Throwable $e) {
            $this->logger->error("Failed to save user: " . $e->getMessage());
        }
    }
}
