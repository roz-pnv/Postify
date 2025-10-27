<?php

namespace App\Http\Controllers;

use InvalidArgumentException;
use Throwable;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

use App\Domain\Services\AuthService;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Responses\JsonResponse;

class AuthController
{
    private AuthService $authService;
    private LoggerInterface $logger;

    public function __construct(AuthService $authService, LoggerInterface $logger)
    {
        $this->authService = $authService;
        $this->logger = $logger;
    }

    public function register(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->info('Received register request.');

        try {
            $registerRequest = new RegisterRequest($request, $this->logger);

            $registerResult = $this->authService->register(
                $registerRequest->getUsername(),
                $registerRequest->getEmail(),
                $registerRequest->getPassword()
            );

            $user = $registerResult['user'];

            $this->logger->info("User registered: $user->email");

            return JsonResponse::create([
                'success' => true,
                'user' => [
                    'username' => $user->username,
                    'email' => $user->email
                ]
            ], 201);

        } catch (InvalidArgumentException $e) {
            $this->logger->warning("Validation failed: " . $e->getMessage());

            return JsonResponse::create(['error' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            $this->logger->error("Unexpected error: " . $e->getMessage());

            return JsonResponse::create(['error' => 'Internal Server Error'], 500);
        }
    }

    public function login(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->info('Received login request.');

        try {
            $loginRequest = new LoginRequest($request, $this->logger);

            $loginResult = $this->authService->login(
                $loginRequest->getUsername(),
                $loginRequest->getEmail(),
                $loginRequest->getPassword()
            );

            $user = $loginResult['user'];
            $token = $loginResult['token'];

            $this->logger->info("User logged in: $user->email");

            return JsonResponse::create([
                'success' => true,
                'token' => $token,
                'user' => [
                    'username' => $user->username,
                    'email' => $user->email
                ]
            ], 201);

        } catch (Throwable $e) {
            $this->logger->error("Unexpected error: " . $e->getMessage());

            return JsonResponse::create(['error' => 'Internal Server Error'], 500);
        }
    }
}
