<?php
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/Core/Bootstrap.php';


use Psr\Container\ContainerExceptionInterface;
use Psr\Log\LoggerInterface;

use App\Core\Bootstrap;
use App\Core\Logger;
use App\Core\Migrator;

try {
    $earlyLogger = new Logger(__DIR__ . '/../../data/logs/app.log');
    $container = Bootstrap::init($earlyLogger);
    $logger = $container->get(LoggerInterface::class);

    $migrationsPath = __DIR__ . '/Database/migrations';
    $migrator = new Migrator($migrationsPath);
    $migrator->run();

    $logger->info('All migrations completed.');
    echo "Migrations finished.\n";
} catch (ContainerExceptionInterface | Exception $e) {
    error_log("Migration script error: " . $e->getMessage());
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
