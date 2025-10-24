<?php

namespace App\Http\Requests;

use InvalidArgumentException;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class RegisterRequest
{
    private array $data;
    private LoggerInterface $logger;

    public function __construct(
        ServerRequestInterface $request,
        LoggerInterface $logger,
    )
    {
        $this->logger = $logger;
        $body = (string) $request->getBody();

        $logger->debug('Raw request body: ' . $body);

        $this->data = json_decode($body, true) ?? [];

        $this->validate();
    }

    private function validate(): void
    {
        $required = ['username', 'email', 'password', 'repeat_password'];
        foreach ($required as $field) {
            if (empty($this->data[$field])) {
                $this->logger->warning("Missing field: $field");
                throw new InvalidArgumentException("Field '$field' is required.");
            }
        }

        $email = strtolower(trim($this->data['email']));
        if (!preg_match('/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $email)) {
            throw new InvalidArgumentException("Invalid email format.");
        }

        if ($this->data['password'] !== $this->data['repeat_password']) {
            throw new InvalidArgumentException("Passwords do not match.");
        }

        if (strlen($this->data['password']) < 6) {
            throw new InvalidArgumentException("Password must be at least 6 characters long.");
        }

        $this->logger->info("Validation passed for {$this->data['email']}");
    }

    public function getUsername(): string
    {
        return trim($this->data['username']);
    }

    public function getEmail(): string
    {
        return strtolower(trim($this->data['email']));
    }

    public function getPassword(): string
    {
        return $this->data['password'];
    }

    public function getRepeatPassword(): string
    {
        return $this->data['repeat_password'];
    }
}
