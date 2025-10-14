<?php
namespace App\core;

class Config
{
    private static array $config = [];

    public static function load(string $path): void
    {
        foreach (glob($path . '/*.php') as $file) {
            $key = basename($file, '.php');
            self::$config[$key] = require $file;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key, 2);
        $file = $parts[0] ?? null;
        $param = $parts[1] ?? null;

        if (!isset(self::$config[$file])) {
            return $default;
        }

        if ($param === null) {
            return self::$config[$file];
        }

        return self::$config[$file][$param] ?? $default;
    }
}
