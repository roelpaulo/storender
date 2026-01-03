<?php

namespace App\Storage;

use App\Core\Config;

class LocalDriver implements StorageInterface
{
  protected $basePath;

  public function __construct()
  {
    $this->basePath = rtrim(Config::get('STORAGE_PATH'), '/');
    if (!is_dir($this->basePath)) {
      mkdir($this->basePath, 0777, true);
    }
  }

  public function putStream($stream, string $path): bool
  {
    $fullPath = $this->basePath . '/' . ltrim($path, '/');
    $dir = dirname($fullPath);
    if (!is_dir($dir)) {
      mkdir($dir, 0777, true);
    }

    $target = fopen($fullPath, 'wb');
    if (!$target)
      return false;

    $result = stream_copy_to_stream($stream, $target);
    fclose($target);

    return $result !== false;
  }

  public function getStream(string $path)
  {
    $fullPath = $this->basePath . '/' . ltrim($path, '/');
    if (!$this->exists($path))
      return false;
    return fopen($fullPath, 'rb');
  }

  public function delete(string $path): bool
  {
    $fullPath = $this->basePath . '/' . ltrim($path, '/');
    if ($this->exists($path)) {
      return unlink($fullPath);
    }
    return true;
  }

  public function exists(string $path): bool
  {
    $fullPath = $this->basePath . '/' . ltrim($path, '/');
    return file_exists($fullPath);
  }

  public function getUrl(string $path): ?string
  {
    // For local public access, we might need a symlink or a proxy route
    // Assuming /storage is mapped to the public path or a proxy route
    return Config::get('APP_URL') . '/storage/' . ltrim($path, '/');
  }
}
