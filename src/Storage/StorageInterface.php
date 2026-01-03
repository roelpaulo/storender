<?php

namespace App\Storage;

interface StorageInterface
{
  /**
   * Store a file from a stream.
   * 
   * @param resource $stream
   * @param string $path
   * @return bool
   */
  public function putStream($stream, string $path): bool;

  /**
   * Get a file stream for reading.
   * 
   * @param string $path
   * @return resource|bool
   */
  public function getStream(string $path);

  /**
   * Delete a file.
   * 
   * @param string $path
   * @return bool
   */
  public function delete(string $path): bool;

  /**
   * Check if file exists.
   * 
   * @param string $path
   * @return bool
   */
  public function exists(string $path): bool;

  /**
   * Get public URL for a file if available.
   * 
   * @param string $path
   * @return string|null
   */
  public function getUrl(string $path): ?string;
}
