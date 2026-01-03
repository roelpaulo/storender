<?php

namespace App\Core;

use Dotenv\Dotenv;

class Config
{
    private static $dotenv;

    public static function load()
    {
        if (self::$dotenv === null) {
            self::$dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
            self::$dotenv->load();
        }
    }

    public static function get($key, $default = null)
    {
        self::load();
        return $_ENV[$key] ?? $default;
    }
}
