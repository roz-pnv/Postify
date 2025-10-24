<?php

namespace App\Core;

use Throwable;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

use App\Http\Requests\ServerRequest;
use App\Http\Requests\Stream;
use App\Http\Requests\Uri;
use App\Http\Responses\JsonResponse;

final class Router
{
    private ContainerInterface $container;
    private LoggerInterface $logger;
    private array $routes;

    public function __construct(
        ContainerInterface $container,
        LoggerInterface $logger,
    )
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->routes = require __DIR__ . '/../routes/api.php';
    }

    public function dispatch(): ResponseInterface
    {
        $request = $this->createServerRequestFromGlobals();

        [$handler, $params] = $this->matchRoute(
            $request->getMethod(),
            $request->getUri()->getPath()
        );

        if (!$handler) {
            $this->logger->warning("No route matched for {$request->getMethod()} {$request->getUri()->getPath()}");

            return $this->json(['error' => 'Not Found'], 404);
        }

        [$class, $method] = $handler;

        try {
            $controller = $this->container->get($class);
        } catch (ContainerExceptionInterface $e) {
            $this->logger->error("Failed to resolve controller '$class': " . $e->getMessage());

            return $this->json(['error' => 'Controller not found'], 500);
        }

        try {
            $response = $controller->$method($request, $params);

            if ($response instanceof ResponseInterface) {
                $this->logResponse($response);

                return $response;
            }

            return $this->json($response);
        } catch (Throwable $e) {
            $this->logger->error("Unhandled exception in route handler: " . $e->getMessage());

            return $this->json(['error' => 'Internal Server Error'], 500);
        }
    }


    private function matchRoute(
        string $method,
        string $path,
    ): array
    {
        foreach ($this->routes as [$routeMethod, $routePath, $handler]) {
            if ($method !== $routeMethod) {
                continue;
            }

            $pattern = preg_replace('#{([^}]+)}#', '(?P<$1>[^/]+)', $routePath);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return [$handler, $params];
            }
        }

        return [null, []];
    }

    private function createServerRequestFromGlobals(): ServerRequestInterface
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $uri = $this->container->get(Uri::class);
            $headers = getallheaders();
            $body = $this->container->get(Stream::class);

            return new ServerRequest(
                $method,
                $uri,
                $headers,
                $body,
                $_GET,
                $_POST,
                $_COOKIE,
                $_FILES
            );
        } catch (ContainerExceptionInterface $e) {
            $this->logger->error('Failed to create ServerRequest: ' . $e->getMessage());
            $this->logJson(['error' => 'Invalid request'], 500);
            exit;
        } catch (Throwable $e) {
            $this->logger->error('Unexpected error during request creation: ' . $e->getMessage());
            $this->logJson(['error' => 'Internal Server Error'], 500);
            exit;
        }
    }

    private function logResponse(ResponseInterface $response): void
    {
        $status = $response->getStatusCode();
        $headers = $response->getHeaders();
        $body = (string) $response->getBody();

        $this->logger->info('Response emitted', [
            'status' => $status,
            'headers' => $headers,
            'body' => $body,
        ]);
    }

    private function logJson(array $data, int $status = 200): void
    {
        $this->logger->info('JSON response emitted', [
            'status' => $status,
            'payload' => $data,
        ]);
    }

    private function json(array $data, int $status = 200): ResponseInterface
    {
        $this->logJson($data, $status);

        return JsonResponse::create($data, $status);
    }
}
