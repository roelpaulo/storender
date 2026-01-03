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
          if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
            $user = posix_getpwuid(posix_geteuid())['name'] ?? 'unknown';
            throw new Exception("Failed to create database directory: $dir (Running as: $user)");
          }
          chmod($dir, 0777);
        }

        if (file_exists($path) && !is_writable($path)) {
          $user = posix_getpwuid(posix_geteuid())['name'] ?? 'unknown';
          throw new Exception("Database file is not writable: $path (Running as: $user)");
        }

        if (!is_writable($dir)) {
          $user = posix_getpwuid(posix_geteuid())['name'] ?? 'unknown';
          throw new Exception("Database directory is not writable: $dir (Running as: $user)");
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
    $schemaPath = dirname(__DIR__, 2) . '/database/schema.sql';
    
    if (!file_exists($schemaPath)) {
      error_log("Database::init - Schema file not found at: $schemaPath");
      throw new Exception("Database schema file not found at $schemaPath");
    }

    $schema = file_get_contents($schemaPath);
    if ($schema === false) {
      throw new Exception("Failed to read database schema file");
    }

    $db->exec($schema);

    // Migration: Add allowed_domains to projects if it doesn't exist
    if (Config::get('DB_DRIVER', 'sqlite') === 'sqlite') {
      $stmt = $db->query("PRAGMA table_info(projects)");
      $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
      if (!in_array('allowed_domains', $columns)) {
        error_log("Database::init - Adding allowed_domains column to projects table");
        $db->exec("ALTER TABLE projects ADD COLUMN allowed_domains TEXT");
      }
    }
  }
}
