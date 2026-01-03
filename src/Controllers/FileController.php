<?php

namespace App\Controllers;

use App\Core\Response;
use App\Core\Config;
use App\Models\File;
use App\Models\Project;
use App\Storage\StorageFactory;
use App\Auth\ApiKeyGuard;

class FileController
{
  public function upload()
  {
    $auth = ApiKeyGuard::authenticate();
    $projectId = $auth['project_id'] ?? null;

    if (!$projectId) {
      return Response::error('Project ID required. Set X-Project-ID header for session-based uploads.', 400);
    }

    $fileName = ApiKeyGuard::getHeader('X-File-Name') ?? 'unnamed_file';
    $mimeType = $_SERVER['CONTENT_TYPE'] ?? ApiKeyGuard::getHeader('Content-Type') ?? 'application/octet-stream';

    $storage = StorageFactory::create();

    // Use a temporary file to calculate hash while streaming
    $tempPath = tempnam(sys_get_temp_dir(), 'storender_');
    $tempHandle = fopen($tempPath, 'wb');

    $input = fopen('php://input', 'rb');
    $hashCtx = hash_init('sha256');
    $size = 0;

    while (!feof($input)) {
      $chunk = fread($input, 8192);
      $size += strlen($chunk);

      if ($size > (int) Config::get('UPLOAD_MAX_SIZE', 134217728)) {
        fclose($input);
        fclose($tempHandle);
        unlink($tempPath);
        return Response::error('File too large', 413);
      }

      hash_update($hashCtx, $chunk);
      fwrite($tempHandle, $chunk);
    }

    fclose($input);
    fclose($tempHandle);
    $hash = hash_final($hashCtx);

    // Move to final storage
    $finalPath = "projects/{$projectId}/" . substr($hash, 0, 2) . "/" . $hash;
    $tempRead = fopen($tempPath, 'rb');
    $stored = $storage->putStream($tempRead, $finalPath);
    fclose($tempRead);
    unlink($tempPath);

    if (!$stored) {
      return Response::error('Failed to store file', 500);
    }

    $fileId = File::create($projectId, $fileName, $mimeType, $size, $hash, $finalPath);

    return Response::json([
      'id' => $fileId,
      'name' => $fileName,
      'size' => $size,
      'hash' => $hash,
      'url' => Config::get('APP_URL') . '/api/files/' . $fileId
    ], 201);
  }

  public function download($id)
  {
    $file = File::findById($id);
    if (!$file) {
      return Response::error('File not found', 404);
    }

    // Check auth if not public
    if (!$file['is_public']) {
      ApiKeyGuard::authenticate();
    } else {
      // Public file: Check Allowed Domains (Referer Protection)
      $project = Project::findById($file['project_id']);
      if (!empty($project['allowed_domains'])) {
        // Bypass for Owner (Dashboard)
        if (session_status() === PHP_SESSION_NONE)
          session_start();
        $isOwner = isset($_SESSION['user_id']) && $project['user_id'] == $_SESSION['user_id'];

        if (!$isOwner) {
          $allowedDomains = array_map('trim', explode(',', $project['allowed_domains']));
          $referer = $_SERVER['HTTP_REFERER'] ?? '';
          $refererHost = parse_url($referer, PHP_URL_HOST);

          $allowed = false;
          foreach ($allowedDomains as $domain) {
            if ($domain && strpos((string) $refererHost, $domain) !== false) {
              $allowed = true;
              break;
            }
          }

          if (!$allowed) {
            // Allow failover to API Key if provided in query or header
            $apiKey = ApiKeyGuard::getHeader('X-API-Key') ?? $_GET['api_key'] ?? null;
            if (!$apiKey) {
              return Response::error('Access denied by Referer policy', 403);
            }
            // Validate key
            ApiKeyGuard::authenticate();
          }
        }
      }
    }

    $storage = StorageFactory::create();
    $stream = $storage->getStream($file['path']);

    if (!$stream) {
      return Response::error('File data not found', 404);
    }

    header('Content-Type: ' . $file['mime_type'] ?? 'application/octet-stream');
    header('Content-Length: ' . $file['size']);
    header('Content-Disposition: inline; filename="' . addslashes($file['original_name']) . '"');

    fpassthru($stream);
    fclose($stream);
    exit;
  }

  public function meta($id)
  {
    $auth = ApiKeyGuard::authenticate();
    $file = File::findById($id);

    if (!$file) {
      return Response::error('File not found', 404);
    }

    // Verify ownership
    if (isset($auth['project_id'])) {
      if ($file['project_id'] != $auth['project_id']) {
        return Response::error('Access denied', 403);
      }
    } else if (isset($auth['session_user_id'])) {
      // Verify project of file belongs to user
      $db = \App\Core\Database::getInstance();
      $stmt = $db->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
      $stmt->execute([$file['project_id'], $auth['session_user_id']]);
      if (!$stmt->fetch()) {
        return Response::error('Access denied', 403);
      }
    }

    return Response::json($file);
  }

  public function delete($id)
  {
    $auth = ApiKeyGuard::authenticate();
    $file = File::findById($id);

    if (!$file) {
      return Response::error('File not found', 404);
    }

    // Verify ownership
    if (isset($auth['project_id'])) {
      if ($file['project_id'] != $auth['project_id']) {
        return Response::error('Access denied', 403);
      }
    } else if (isset($auth['session_user_id'])) {
      $db = \App\Core\Database::getInstance();
      $stmt = $db->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
      $stmt->execute([$file['project_id'], $auth['session_user_id']]);
      if (!$stmt->fetch()) {
        return Response::error('Access denied', 403);
      }
    }

    $storage = StorageFactory::create();
    $storage->delete($file['path']);
    File::delete($id);

    return Response::json(['message' => 'File deleted']);
  }

  public function updateVisibility($id)
  {
    $auth = ApiKeyGuard::authenticate();
    $file = File::findById($id);

    if (!$file) {
      return Response::error('File not found', 404);
    }

    // Verify ownership
    if (isset($auth['project_id'])) {
      if ($file['project_id'] != $auth['project_id']) {
        return Response::error('Access denied', 403);
      }
    } else if (isset($auth['session_user_id'])) {
      $db = \App\Core\Database::getInstance();
      $stmt = $db->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
      $stmt->execute([$file['project_id'], $auth['session_user_id']]);
      if (!$stmt->fetch()) {
        return Response::error('Access denied', 403);
      }
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $isPublic = $input['is_public'] ?? 1;

    File::updateVisibility($id, $isPublic);

    return Response::json(['message' => 'File visibility updated', 'is_public' => (int) $isPublic]);
  }
}
