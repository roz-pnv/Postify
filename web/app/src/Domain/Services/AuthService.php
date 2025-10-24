<?php

namespace App\Domain\Services;

use InvalidArgumentException;

use Psr\Log\LoggerInterface;

use App\Domain\Contracts\PasswordHasherInterface;
use App\Domain\Contracts\UserRepositoryInterface;
use App\Domain\Contracts\TokenGeneratorInterface;
use App\Domain\Models\User;

class AuthService
{
    private UserRepositoryInterface $users;
    private PasswordHasherInterface $hasher;
    private LoggerInterface $logger;
    private TokenGeneratorInterface $tokens;

    public function __construct(
        UserRepositoryInterface $users,
        PasswordHasherInterface $hasher,
        TokenGeneratorInterface $tokens,
        LoggerInterface         $logger,
    )
    {
        $this->users = $users;
        $this->hasher = $hasher;
        $this->tokens = $tokens;
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

    public function login(
        string $username,
        string $email,
        string $plainPassword
    ): array
    {
        $this->logger->info("Attempting login for email: $email and username: $username");

        $user = null;
        if (!empty($email)) {
            $user = $this->users->findByEmail($email);
        } elseif (!empty($username)) {
            $user = $this->users->findByUsername($username);
        }

        if (!$user) {
            $message = "No user found with email: $email and username: $username";
            $this->logger->warning($message);
            throw new InvalidArgumentException($message);
        }

        if (!$this->hasher->verify($plainPassword, $user->password)) {
            $message = "Invalid password for email: $email";
            $this->logger->warning($message);
            throw new InvalidArgumentException($message);
        }

        $token = $this->tokens->generateToken([
            'sub' => $user->id,
            'email' => $user->email,
            'username' => $user->username
        ]);

        $this->logger->info("User logged in successfully: $email");

        return ['user' => $user, 'token' => $token];
    }
}
