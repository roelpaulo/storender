<?php

namespace App\Core;

use Dotenv\Dotenv;

class Config
{
    private static $dotenv;

    public static function load()
    {
        if (self::$dotenv === null) {
            $root = dirname(__DIR__, 2);
            $envFile = $root . '/.env';

            // Only attempt to load dotenv if a .env file exists in project root.
            // In production we expect env vars to be provided by the host (no file).
            if (is_file($envFile)) {
                self::$dotenv = Dotenv::createImmutable($root);
                self::$dotenv->load();
            } else {
                // Mark as loaded (no file) to avoid repeated checks
                self::$dotenv = false;
            }
        }
    }

    public static function get($key, $default = null)
    {
        self::load();
        return $_ENV[$key] ?? $default;
    }
}
