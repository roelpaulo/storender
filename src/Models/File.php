<?php

namespace App\Models;

use App\Core\Database;
use Ramsey\Uuid\Uuid;

class File
{
  public static function create($projectId, $originalName, $mimeType, $size, $hash, $path, $isPublic = 1)
  {
    $db = Database::getInstance();
    $id = Uuid::uuid7()->toString();

    $stmt = $db->prepare("INSERT INTO files (id, project_id, original_name, mime_type, size, hash, path, is_public) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $projectId, $originalName, $mimeType, $size, $hash, $path, $isPublic]);

    return $id;
  }

  public static function findById($id)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT * FROM files WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  public static function delete($id)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare("DELETE FROM files WHERE id = ?");
    return $stmt->execute([$id]);
  }

  public static function updateVisibility($id, $isPublic)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare("UPDATE files SET is_public = ? WHERE id = ?");
    return $stmt->execute([(int) $isPublic, $id]);
  }
}
