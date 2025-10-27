<?php

namespace App\Http\Requests;

use InvalidArgumentException;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class LoginRequest
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

        $this->logger->debug('Raw login request body: ' . $body);

        $this->data = json_decode($body, true) ?? [];

        $this->validate();
    }


    public function validate(): void
    {
        if (!isset($this->data['password'])) {
            throw new InvalidArgumentException('password is required.');
        }

        if (!isset($this->data['email'], $this->data['username'])) {
            throw new InvalidArgumentException('username or email is required.');
        }

        if (!isset($this->data['email'])) {
            if (!filter_var($this->data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Invalid email format.');
            }
        }

        $this->logger->info("Validation passed for {$this->data['username']}");
    }

    public function getUsername(): string
    {
        return $this->data['username'];
    }

    public function getEmail(): string
    {
        return strtolower(trim($this->data['email']));
    }

    public function getPassword(): string
    {
        return $this->data['password'];
    }
}
