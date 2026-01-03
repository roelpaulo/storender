<?php

namespace App\Core;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Router
{
  private $dispatcher;

  public function __construct()
  {
    $this->dispatcher = simpleDispatcher(function (RouteCollector $r) {
      // Auth Routes
      $r->addRoute('POST', '/api/auth/register', ['App\Controllers\AuthController', 'register']);
      $r->addRoute('POST', '/api/auth/login', ['App\Controllers\AuthController', 'login']);
      $r->addRoute('GET', '/api/auth/verify-email', ['App\Controllers\AuthController', 'verifyEmail']);
      $r->addRoute('POST', '/api/auth/forgot-password', ['App\Controllers\AuthController', 'forgotPassword']);
      $r->addRoute('POST', '/api/auth/reset-password', ['App\Controllers\AuthController', 'resetPassword']);

      // Project Routes
      $r->addRoute('GET', '/api/projects', ['App\Controllers\ProjectController', 'index']);
      $r->addRoute('POST', '/api/projects', ['App\Controllers\ProjectController', 'create']);
      $r->addRoute('PATCH', '/api/projects/{id:\d+}', ['App\Controllers\ProjectController', 'update']);
      $r->addRoute('DELETE', '/api/projects/{id:\d+}', ['App\Controllers\ProjectController', 'delete']);
      $r->addRoute('POST', '/api/projects/{id:\d+}/keys', ['App\Controllers\ProjectController', 'createKey']);
      $r->addRoute('GET', '/api/projects/{id:\d+}/keys', ['App\Controllers\ProjectController', 'listKeys']);
      $r->addRoute('GET', '/api/projects/{id:\d+}/files', ['App\Controllers\ProjectController', 'listFiles']);
      $r->addRoute('DELETE', '/api/keys/{id:\d+}', ['App\Controllers\ProjectController', 'deleteKey']);

      // File Routes
      $r->addRoute('POST', '/api/files', ['App\Controllers\FileController', 'upload']);
      $r->addRoute('GET', '/api/files/{id}', ['App\Controllers\FileController', 'download']);
      $r->addRoute('DELETE', '/api/files/{id}', ['App\Controllers\FileController', 'delete']);
      $r->addRoute('GET', '/api/files/{id}/meta', ['App\Controllers\FileController', 'meta']);
      $r->addRoute('PATCH', '/api/files/{id}', ['App\Controllers\FileController', 'updateVisibility']);

      // Dashboard Routes
      $r->addRoute('GET', '/', ['App\Controllers\DashboardController', 'index']);
      $r->addRoute('GET', '/login', ['App\Controllers\DashboardController', 'login']);
      $r->addRoute('GET', '/register', ['App\Controllers\DashboardController', 'register']);
      $r->addRoute('GET', '/logout', ['App\Controllers\DashboardController', 'logout']);
      $r->addRoute('GET', '/reset-password', ['App\Controllers\DashboardController', 'resetPassword']);
    });
  }

  public function handle()
  {
    $httpMethod = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];

    if (false !== $pos = strpos($uri, '?')) {
      $uri = substr($uri, 0, $pos);
    }
    $uri = rawurldecode($uri);

    error_log("Dispatching: $httpMethod $uri");
    $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);
    error_log("Route Info: " . json_encode($routeInfo));

    switch ($routeInfo[0]) {
      case Dispatcher::NOT_FOUND:
        Response::error('Not Found', 404);
        break;
      case Dispatcher::METHOD_NOT_ALLOWED:
        Response::error('Method Not Allowed', 405);
        break;
      case Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        $this->execute($handler, $vars);
        break;
    }
  }

  private function execute($handler, $vars)
  {
    [$class, $method] = $handler;
    $controller = new $class();
    call_user_func_array([$controller, $method], array_values($vars));
  }
}
