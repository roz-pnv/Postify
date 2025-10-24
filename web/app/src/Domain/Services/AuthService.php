<?php

namespace App\Domain\Services;

use InvalidArgumentException;

use Psr\Log\LoggerInterface;

use App\Domain\Contracts\PasswordHasherInterface;
use App\Domain\Contracts\UserRepositoryInterface;
use App\Domain\Models\User;

class AuthService
{
    private UserRepositoryInterface $users;
    private PasswordHasherInterface $hasher;
    private LoggerInterface $logger;
    public function __construct(
        UserRepositoryInterface $users,
        PasswordHasherInterface $hasher,
        LoggerInterface $logger,
    ) {
        $this->users = $users;
        $this->hasher = $hasher;
        $this->logger = $logger;
    }

    public function register(
        string $username,
        string $email,
        string $plainPassword
    ): array
    {
        $this->logger->info("Checking existing user for email: $email");

        if ($this->users->findByEmail($email)) {
            $message = "User with email $email already exists.";
            $this->logger->warning($message);
            throw new InvalidArgumentException($message);
        }

        $hashed = $this->hasher->hash($plainPassword);

        $user = new User(
            null,
            $username,
            $email,
            $hashed
        );

        $this->users->save($user);

        $this->logger->info("User created successfully: $email");

        return ['user' => $user];
    }
}
