<?php

namespace App\Core;

use ReflectionClass;
use ReflectionNamedType;
use Throwable;

use Psr\Container\ContainerInterface;

use App\Core\Exception\ContainerException;
use App\Core\Exception\NotFoundException;

class Container implements ContainerInterface
{
    private array $bindings = [];
    private array $instances = [];

    public function set(string $id, callable $factory): void
    {
        $this->bindings[$id] = $factory;
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || class_exists($id);
    }

    public function get(string $id): object
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->bindings[$id])) {
            try {
                $object = $this->bindings[$id]($this);
                $this->instances[$id] = $object;

                return $object;
            } catch (Throwable $e) {
                throw new ContainerException(
                    "Factory failed for [$id]: " . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        if (!class_exists($id)) {
            throw new NotFoundException("Class [$id] not found.");
        }

        try {
            $reflection = new ReflectionClass($id);

            if (!$reflection->isInstantiable()) {
                throw new ContainerException("Class [$id] is not instantiable.");
            }

            $constructor = $reflection->getConstructor();

            if (!$constructor) {
                $object = new $id();
                $this->instances[$id] = $object;

                return $object;
            }

            $params = [];

            foreach ($constructor->getParameters() as $param) {
                $type = $param->getType();

                if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                    throw new ContainerException(
                        "Cannot resolve parameter [{$param->getName()}] of [$id]"
                    );
                }

                $dependency = $type->getName();
                $params[] = $this->get($dependency);
            }

            $object = $reflection->newInstanceArgs($params);
            $this->instances[$id] = $object;

            return $object;
        } catch (Throwable $e) {
            throw new ContainerException(
                "Failed to auto-wire [$id]: " . $e->getMessage(),
                0,
                $e
            );
        }
    }
}
