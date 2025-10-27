<?php

namespace App\Core;

use Dotenv\Dotenv;
use Exception;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use App\Core\Exception\NotFoundException;
use App\Domain\Contracts\PasswordHasherInterface;
use App\Domain\Contracts\TokenGeneratorInterface;
use App\Domain\Contracts\UserRepositoryInterface;
use App\Http\Requests\Stream;
use App\Http\Requests\Uri;
use App\Infrastructure\Persistence\DatabaseConnection;
use App\Infrastructure\Persistence\DatabaseConnectionFactory;
use App\Infrastructure\Persistence\MySQLUserRepository;
use App\Infrastructure\Security\BcryptPasswordHasher;
use App\Infrastructure\Security\JwtTokenGenerator;

final class Bootstrap
{
    private static ?ContainerInterface $container = null;

    /**
     * Initializes the application container and its dependencies.
     *
     * @param LoggerInterface $earlyLogger Logger used for early-stage error reporting
     * @return ContainerInterface Initialized container instance
     *
     * @throws Exception If .env loading, Config loading, or container build fails
     * @throws ContainerExceptionInterface If container fails to resolve dependencies
     * @throws NotFoundException If Logger service is not found
     */
    public static function init(LoggerInterface $earlyLogger): ContainerInterface
    {
        if (self::$container !== null) {
            return self::$container;
        }

        require_once __DIR__ . '/../../../vendor/autoload.php';

        try {
            $envPath = realpath(__DIR__ . '/../../../../.env');
            if ($envPath && file_exists($envPath)) {
                Dotenv::createImmutable(dirname($envPath))->load();
            }
        } catch (Exception $e) {
            $earlyLogger->error('Failed to load .env: ' . $e->getMessage());
            throw $e;
        }

        try {
            Config::load(__DIR__ . '/../../Config');
        } catch (Exception $e) {
            $earlyLogger->error('Failed to load Config: ' . $e->getMessage());
            throw $e;
        }

        $container = new Container();

        $container->set(Config::class, fn () => new Config());

        $container->set(LoggerInterface::class, fn ($c) =>
            new Logger($c->get(Config::class)->get('logging.path'))
        );

        $container->set(Logger::class, fn ($c) =>
            $c->get(LoggerInterface::class)
        );

        $container->set(DatabaseConnectionFactory::class, fn($c) =>
            new DatabaseConnectionFactory(
                $c->get(Config::class)->get('database'),
                $c->get(LoggerInterface::class)
            )
        );

        $container->set(DatabaseConnection::class, fn($c) =>
            new DatabaseConnection(
                $c->get(DatabaseConnectionFactory::class)->create()
            )
        );

        $container->set(UserRepositoryInterface::class, fn ($c) =>
            new MySQLUserRepository(
                $c->get(DatabaseConnection::class),
                $c->get(LoggerInterface::class)
            )
        );

        $container->set(Uri::class, fn () =>
            new Uri($_SERVER['REQUEST_URI'] ?? '/')
        );

        $container->set(Stream::class, fn () =>
            new Stream(fopen('php://input', 'r'))
        );

        $container->set(PasswordHasherInterface::class, fn($c) =>
            new BcryptPasswordHasher($c->get(LoggerInterface::class))
        );

        $container->set(TokenGeneratorInterface::class, fn($c) =>
            new JwtTokenGenerator(
                $c->get(Config::class)->get('security.jwt_secret'),
                $c->get(Config::class)->get('security.jwt_expiry')
            )
        );

        self::$container = $container;

        try {
            self::$container->get(LoggerInterface::class)->info('Bootstrap initialized successfully.');
        } catch (NotFoundException | ContainerExceptionInterface $e) {
            $earlyLogger->error('Failed to log bootstrap success: ' . $e->getMessage());
        }

        return self::$container;
    }

    /**
     * Returns the initialized container.
     *
     * @return ContainerInterface
     * @throws Exception If initialization fails
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     */
    public static function getContainer(): ContainerInterface
    {
        if (self::$container === null) {
            $earlyLogger = new Logger(__DIR__ . '/../../../../data/logs/App.log');
            return self::init($earlyLogger);
        }

        return self::$container;
    }
}