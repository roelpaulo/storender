<?php

namespace App\Core;

use PDO;
use Exception;

class Database
{
  private static $instance = null;

  public static function getInstance(): PDO
  {
    if (self::$instance === null) {
      $driver = Config::get('DB_DRIVER', 'sqlite');

      if ($driver === 'sqlite') {
        $path = Config::get('DB_FILE');

        // Ensure directory exists
        $dir = dirname($path);
        if (!is_dir($dir)) {
          mkdir($dir, 0777, true);
        }

        self::$instance = new PDO("sqlite:$path");
      } else {
        throw new Exception("Database driver $driver not supported yet.");
      }

      self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    return self::$instance;
  }

  public static function init()
  {
    $db = self::getInstance();
    $schema = file_get_contents(dirname(__DIR__, 2) . '/database/schema.sql');
    $db->exec($schema);
  }
}
