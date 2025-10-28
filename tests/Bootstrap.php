<?php

declare(strict_types=1);

use Dotenv\Dotenv;

error_reporting(E_ALL);
ini_set('display_errors', '1');

$projectRoot = realpath(__DIR__ . '/../web/');
$root = realpath($projectRoot . '/../');

if ($root === false) {
    fwrite(STDERR, "Cannot determine project root\n");
    exit(1);
}

$autoload = $root . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    fwrite(STDERR, "Autoload not found. Run composer install\n");
    exit(1);
}
require $autoload;

$envFile = file_exists($root . '/.env.test') ? '.env.test' : '.env';
if (file_exists($root . '/' . $envFile)) {
    $dotenv = Dotenv::createImmutable($root, $envFile);
    $dotenv->safeLoad();
}

putenv('APP_ENV=test');
$_ENV['APP_ENV'] = 'test';
$_SERVER['APP_ENV'] = 'test';

if (!isset($_ENV['DB_CONNECTION'])) {
    putenv('DB_CONNECTION=mysql');
    $_ENV['DB_CONNECTION'] = 'mysql';
}

$logDir = $root . '/data/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
if (!is_writable($logDir)) {
    @chmod($logDir, 0777);
}

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

fwrite(STDOUT, "Bootstrap for PHPUnit loaded with env: {$envFile}\n");
