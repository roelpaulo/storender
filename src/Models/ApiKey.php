<?php

namespace App\Models;

use App\Core\Database;

class ApiKey
{
  public static function create($projectId, $label)
  {
    $db = Database::getInstance();
    $key = bin2hex(random_bytes(32));
    $hash = hash('sha256', $key);

    $stmt = $db->prepare("INSERT INTO api_keys (project_id, key_hash, label) VALUES (?, ?, ?)");
    $stmt->execute([$projectId, $hash, $label]);

    return $key; // Return raw key only once
  }

  public static function findByHash($hash)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT * FROM api_keys WHERE key_hash = ?");
    $stmt->execute([$hash]);
    return $stmt->fetch();
  }

  public static function listByProject($projectId)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT id, project_id, label, last_used_at, created_at FROM api_keys WHERE project_id = ?");
    $stmt->execute([$projectId]);
    return $stmt->fetchAll();
  }

  public static function updateLastUsed($id)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare("UPDATE api_keys SET last_used_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$id]);
  }

  public static function delete($id)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare("DELETE FROM api_keys WHERE id = ?");
    return $stmt->execute([$id]);
  }
}
