<?php

namespace App\Infrastructure\Security;

use InvalidArgumentException;

use Psr\Log\LoggerInterface;

use App\Domain\Contracts\PasswordHasherInterface;

class BcryptPasswordHasher implements PasswordHasherInterface
{
    private LoggerInterface $logger;
    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    public function hash(string $plain): string
    {
        if (strlen($plain) < 6) {
            $this->logger->warning('Password too short.');
            throw new InvalidArgumentException('Password must be at least 6 characters.');
        }

        $hashed = password_hash($plain, PASSWORD_BCRYPT);
        $this->logger->debug('Password hashed successfully.');
        return $hashed;
    }

    public function verify(string $plain, string $hashed): bool
    {
        return password_verify($plain, $hashed);
    }
}
