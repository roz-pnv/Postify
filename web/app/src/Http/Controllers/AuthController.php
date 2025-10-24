<?php

namespace App\Http\Controllers;

use InvalidArgumentException;
use Throwable;

use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use App\Domain\Services\AuthService;
use App\Http\Requests\RegisterRequest;
use App\Http\Responses\JsonResponse;

class AuthController
{
    private AuthService $authService;
    private LoggerInterface $logger;

    public function __construct(AuthService $authService , LoggerInterface $logger)
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
}
