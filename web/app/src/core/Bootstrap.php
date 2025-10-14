<?php
namespace App\core;

/**
 * The Bootstrap class is responsible for initializing core application components:
 * - Autoloading dependencies
 * - Loading environment variables
 * - Loading configuration files
 * - Initializing the logger and database (singleton-safe)
 */
class Bootstrap
{
    // Singleton instances for shared access
    private static ?Database $database = null;
    private static ?Logger $logger = null;

    /**
     * Initializes the application core:
     * - Loads Composer autoload
     * - Loads .env variables
     * - Loads configuration files
     * - Initializes logger and database
     */
    public static function init(): void
    {
        // 1️ Load Composer autoloader
        require_once __DIR__ . '/../../vendor/autoload.php';

        // 2️ Load environment variables from .env file
        $envPath = realpath(__DIR__ . '/../../../../.env');
        if ($envPath && file_exists($envPath)) {
            self::loadEnv($envPath);
        }

        // 3 Load all configuration files (e.g., database, logging)
        Config::load(__DIR__ . '/../../config');

        // 4 Initialize logger if not already set
        if (!self::$logger) {
            $logConfig = Config::get('logging');
            $logPath = $logConfig['path'] ?? (__DIR__ . '/../../../../data/logs/app.log');
            self::$logger = new Logger($logPath);
            self::$logger->info('Logger initialized.');
        }

        self::$logger->info('Bootstrap starting...');

        // 5️ Initialize database if not already set
        if (!self::$database) {
            try {
                $dbConfig = Config::get('database');
                self::$database = new Database(PDOFactory::create($dbConfig, self::$logger));
                self::$logger->info('Database initialized successfully.');
            } catch (\Throwable $e) {
                self::$logger->error('Database connection failed: ' . $e->getMessage());
                throw $e;
            }
        }

        self::$logger->info('Bootstrap completed successfully.');
    }

    /**
     * Returns the singleton database instance.
     * Initializes it if not already available.
     *
     * @return Database
     */
    public static function getDatabase(): Database
    {
        if (!self::$database) {
            self::init();
        }
        return self::$database;
    }

    /**
     * Returns the singleton logger instance.
     * Initializes it if not already available.
     *
     * @return Logger
     */
    public static function getLogger(): Logger
    {
        if (!self::$logger) {
            self::init();
        }
        return self::$logger;
    }

    /**
     * Loads environment variables from a .env file manually.
     * Each line must follow KEY=VALUE format.
     * Lines starting with # or without = are ignored.
     *
     * @param string $path Absolute path to the .env file
     */
    private static function loadEnv(string $path): void
    {
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) continue;
            if (!str_contains($trimmed, '=')) continue;

            [$key, $value] = explode('=', $trimmed, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}
