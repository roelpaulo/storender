<?php

namespace App\Storage;

use App\Core\Config;
use Exception;

class StorageFactory
{
  public static function create(): StorageInterface
  {
    $driver = Config::get('STORAGE_DRIVER', 'local');

    switch ($driver) {
      case 'local':
        return new LocalDriver();
      default:
        throw new Exception("Storage driver $driver not supported.");
    }
  }
}
