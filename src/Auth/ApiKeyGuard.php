<?php

namespace App\Auth;

use App\Core\Response;
use App\Models\ApiKey;

class ApiKeyGuard
{
  public static function getHeader($name)
  {
    $headers = getallheaders();
    if ($headers) {
      foreach ($headers as $key => $value) {
        if (strcasecmp($key, $name) === 0)
          return $value;
      }
    }
    $serverName = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    return $_SERVER[$serverName] ?? null;
  }

  public static function authenticate()
  {
    $apiKey = self::getHeader('X-API-Key');

    // Fallback: Check query parameter (useful for <img src="..." />)
    if (!$apiKey && isset($_GET['api_key'])) {
      $apiKey = $_GET['api_key'];
    }

    if (!$apiKey) {
      // Check for active session (Dashboard access)
      if (session_status() === PHP_SESSION_NONE)
        session_start();

      if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $projectId = self::getHeader('X-Project-ID');

        if ($projectId) {
          $db = \App\Core\Database::getInstance();
          $stmt = $db->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
          $stmt->execute([$projectId, $userId]);
          if (!$stmt->fetch()) {
            Response::error('Project access denied', 403);
          }
          return [
            'project_id' => (int) $projectId,
            'session_user_id' => $userId
          ];
        }
        return ['session_user_id' => $userId];
      }
      Response::error('API Key required', 401);
    }

    $hash = hash('sha256', $apiKey);
    $keyRecord = ApiKey::findByHash($hash);

    if (!$keyRecord) {
      Response::error('Invalid API Key', 401);
    }

    ApiKey::updateLastUsed($keyRecord['id']);
    return $keyRecord;
  }
}
