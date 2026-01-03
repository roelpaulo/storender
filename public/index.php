<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Config;
use App\Core\Database;
use App\Core\Router;

// Error reporting for debugging
if (Config::get('APP_DEBUG') === 'true') {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}

// Initialize Database (runs migrations if needed)
try {
  Database::init();
} catch (Exception $e) {
  die("Database initialization failed: " . $e->getMessage());
}

// Handle Routing
$router = new Router();
$router->handle();
