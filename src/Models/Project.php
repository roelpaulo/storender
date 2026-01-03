<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Project
{
  public static function create($userId, $name, $allowedDomains = null)
  {
    $db = Database::getInstance();
    $baseSlug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    $slug = $baseSlug;
    
    // Ensure slug is unique by appending a short random string if necessary
    $stmt = $db->prepare("SELECT id FROM projects WHERE slug = ?");
    $stmt->execute([$slug]);
    if ($stmt->fetch()) {
      $slug = $baseSlug . '-' . substr(bin2hex(random_bytes(4)), 0, 4);
    }

    $stmt = $db->prepare("INSERT INTO projects (user_id, name, slug, allowed_domains) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $name, $slug, $allowedDomains]);

    return $db->lastInsertId();
  }

  public static function update($id, $data)
  {
    $db = Database::getInstance();
    $fields = [];
    $values = [];

    foreach ($data as $key => $value) {
      $fields[] = "$key = ?";
      $values[] = $value;
    }
    $values[] = $id;

    $sql = "UPDATE projects SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    return $stmt->execute($values);
  }

  public static function allByUserId($userId)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT * FROM projects WHERE user_id = ?");
    $stmt->execute([$userId]);
    $projects = $stmt->fetchAll();
    error_log("Project::allByUserId - User ID: $userId, Found: " . count($projects) . " projects");
    return $projects;
  }

  public static function findById($id)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  public static function delete($id)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
    return $stmt->execute([$id]);
  }
}
