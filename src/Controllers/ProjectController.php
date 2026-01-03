<?php

namespace App\Controllers;

use App\Core\Response;
use App\Models\Project;
use App\Models\ApiKey;
use App\Models\File;
use App\Auth\ApiKeyGuard;

class ProjectController
{
  // Removed private auth() in favor of ApiKeyGuard

  // Removed private auth() in favor of ApiKeyGuard

  public function index()
  {
    $auth = ApiKeyGuard::authenticate();
    if (!isset($auth['session_user_id'])) {
      error_log("ProjectController::index - Unauthorized access attempt");
      Response::error('Unauthorized', 403);
    }
    
    $userId = $auth['session_user_id'];
    $projects = Project::allByUserId($userId);
    error_log("ProjectController::index - Found " . count($projects) . " projects for user ID: " . $userId);
    Response::json($projects);
  }

  public function create()
  {
    $auth = ApiKeyGuard::authenticate();
    if (!isset($auth['session_user_id']))
      Response::error('Unauthorized', 403);

    $input = json_decode(file_get_contents('php://input'), true);
    $name = $input['name'] ?? null;
    $allowedDomains = $input['allowed_domains'] ?? null;

    if (!$name) {
      return Response::error('Project name is required');
    }

    try {
      $projectId = Project::create($auth['session_user_id'], $name, $allowedDomains);
      error_log("ProjectController::create - Created project ID: $projectId for user: " . $auth['session_user_id']);
      return Response::json(['message' => 'Project created', 'id' => $projectId], 201);
    } catch (\Exception $e) {
      error_log("ProjectController::create - Error: " . $e->getMessage());
      return Response::error('Failed to create project: ' . $e->getMessage(), 500);
    }
  }

  public function update($projectId)
  {
    $auth = ApiKeyGuard::authenticate();
    if (!isset($auth['session_user_id']))
      Response::error('Unauthorized', 403);

    $project = Project::findById($projectId);
    if (!$project || $project['user_id'] !== $auth['session_user_id']) {
      return Response::error('Project not found', 404);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $data = [];

    if (isset($input['name']))
      $data['name'] = $input['name'];
    if (isset($input['allowed_domains']))
      $data['allowed_domains'] = $input['allowed_domains'];

    if (!empty($data)) {
      Project::update($projectId, $data);
    }

    return Response::json(['message' => 'Project updated']);
  }

  public function createKey($projectId)
  {
    $auth = ApiKeyGuard::authenticate();
    if (!isset($auth['session_user_id']))
      Response::error('Unauthorized', 403);

    $project = Project::findById($projectId);

    if (!$project || $project['user_id'] !== $auth['session_user_id']) {
      return Response::error('Project not found', 404);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $label = $input['label'] ?? 'Default Key';

    $key = ApiKey::create($projectId, $label);
    return Response::json(['message' => 'API Key created', 'key' => $key], 201);
  }

  public function listKeys($projectId)
  {
    $auth = ApiKeyGuard::authenticate();
    if (!isset($auth['session_user_id']))
      Response::error('Unauthorized', 403);

    $project = Project::findById($projectId);

    if (!$project || $project['user_id'] !== $auth['session_user_id']) {
      return Response::error('Project not found', 404);
    }

    $keys = ApiKey::listByProject($projectId);
    return Response::json($keys);
  }

  public function deleteKey($id)
  {
    $auth = ApiKeyGuard::authenticate();
    if (!isset($auth['session_user_id']))
      Response::error('Unauthorized', 403);
    // Ideally verify key ownership through project
    ApiKey::delete($id);
    return Response::json(['message' => 'API Key deleted']);
  }

  public function listFiles($projectId)
  {
    $auth = ApiKeyGuard::authenticate();

    // Allow Session Owner OR API Key matching Project ID
    $authorized = false;
    if (isset($auth['session_user_id'])) {
      $project = Project::findById($projectId);
      if ($project && $project['user_id'] === $auth['session_user_id']) {
        $authorized = true;
      }
    } elseif (isset($auth['project_id']) && $auth['project_id'] == $projectId) {
      $authorized = true;
    }

    if (!$authorized) {
      return Response::error('Project access denied', 403);
    }

    $db = \App\Core\Database::getInstance();
    $stmt = $db->prepare("SELECT * FROM files WHERE project_id = ? ORDER BY created_at DESC");
    $stmt->execute([$projectId]);
    $files = $stmt->fetchAll();

    return Response::json($files);
  }

  public function delete($id)
  {
    $auth = ApiKeyGuard::authenticate();
    if (!isset($auth['session_user_id']))
      Response::error('Unauthorized', 403);

    $project = Project::findById($id);

    if (!$project || $project['user_id'] !== $auth['session_user_id']) {
      return Response::error('Project not found', 404);
    }

    // Recursive cleanup: Delete all files from storage
    $db = \App\Core\Database::getInstance();
    $stmt = $db->prepare("SELECT path FROM files WHERE project_id = ?");
    $stmt->execute([$id]);
    $files = $stmt->fetchAll();

    $storage = \App\Storage\StorageFactory::create();
    foreach ($files as $file) {
      $storage->delete($file['path']);
    }

    // Database cleanup
    Project::delete($id);

    return Response::json(['message' => 'Project deleted']);
  }
}
