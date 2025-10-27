<?php declare(strict_types=1);

use App\Core\Bootstrap;
use App\Core\Logger;
use App\Core\Router;

require_once __DIR__ . '/../../vendor/autoload.php';

$earlyLogger = new Logger(__DIR__ . '/../../data/logs/app.log');

try {
    $container = Bootstrap::init($earlyLogger);
    $router = new Router($container, $earlyLogger);
    $response = $router->dispatch();

    http_response_code($response->getStatusCode());
    foreach ($response->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            header("$name: $value");
        }
    }
    echo $response->getBody();

} catch (Throwable $e) {
    $earlyLogger->error('Fatal error: ' . $e->getMessage());
    http_response_code(500);
    echo 'Internal Server Error';
}
