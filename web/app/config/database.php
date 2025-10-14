<?php

use App\core\Logger;

/**
 * Detects whether the application is running inside a Docker container.
 * It checks for Docker-specific indicators such as:
 * - The existence of /.dockerenv file
 * - Keywords in /proc/1/cgroup (for both Docker and Kubernetes)
 * - Minimal cgroup v2 format (0::/) as a fallback
 *
 * Logs detection results for debugging and traceability.
 *
 * @return bool True if running inside Docker, false otherwise
 */
function isRunningInDocker(): bool
{
    $logger = new Logger(__DIR__ . '/../../../data/logs/app.log');

    // Check for Docker environment file
    if (file_exists('/.dockerenv')) {
        $logger->info('Detected /.dockerenv — running in Docker.');
        return true;
    }

    // Check for Docker/Kubernetes indicators in cgroup info
    if (file_exists('/proc/1/cgroup')) {
        $content = file_get_contents('/proc/1/cgroup');
        $logger->info("🔍 /proc/1/cgroup content:\n" . $content);

        if (str_contains($content, 'docker') || str_contains($content, 'kubepods')) {
            $logger->info('Detected docker/kubepods in cgroup — running in Docker.');
            return true;
        }

        // Fallback for cgroups v2 format
        if (trim($content) === '0::/') {
            $logger->info('Detected cgroups v2 (0::/) — assuming Docker.');
            return true;
        }
    }

    // No Docker indicators found
    $logger->info('No Docker indicators found — assuming local.');
    return false;
}

// Initialize logger
$logger = new Logger(__DIR__ . '/../../../data/logs/app.log');

// Detect environment and database mode
$isDocker = isRunningInDocker();
$useRootRaw = strtolower(getenv('USE_ROOT_DB'));
$useRoot = in_array($useRootRaw, ['1', 'true', 'yes', 'root'], true);

/**
 * Validate environment and database access mode.
 * Prevent invalid combinations:
 * - Docker with root access (USE_ROOT_DB=1) → blocked
 * - Local with restricted access (USE_ROOT_DB=0) → blocked
 */
if ($isDocker && $useRoot) {
    echo 'Running inside Docker but USE_ROOT_DB=1 — database initialization blocked.';
    $logger->error('Running inside Docker but USE_ROOT_DB=1 — database initialization blocked.');
    exit('Application stopped: invalid DB configuration for Docker environment.');
}

if (!$isDocker && !$useRoot) {
    $logger->error('Running in local environment but USE_ROOT_DB=0 — database initialization blocked.');
    exit('Application stopped: invalid DB configuration for local environment.');
}

/**
 * Configure database connection parameters based on environment.
 * - Docker: use non-root user credentials
 * - Local: use root credentials
 */
if ($isDocker) {
    $host = getenv('MYSQL_HOST') ?: 'db';
    $username = getenv('MYSQL_USER') ?: 'postify_user';
    $password = getenv('MYSQL_PASSWORD') ?: 'postify_pass';
} else {
    $host = getenv('MYSQL_ROOT_HOST') ?: '127.0.0.1';
    $username = getenv('MYSQL_ROOT_USER') ?: 'root';
    $password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
}

// Return final database configuration array
return [
    'driver'   => 'mysql',
    'host'     => $host,
    'port'     => getenv('MYSQL_PORT_INTERNAL') ?: '3306',
    'database' => getenv('MYSQL_DATABASE') ?: 'postify_db',
    'username' => $username,
    'password' => $password,
    'charset'  => 'utf8mb4',
];
