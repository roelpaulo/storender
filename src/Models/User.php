<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
  public static function create($email, $password)
  {
    $db = Database::getInstance();
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
    $stmt->execute([$email, $hash]);

    return $db->lastInsertId();
  }

  public static function findByEmail($email)
  {
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
  }

  public static function verifyPassword($user, $password)
  {
    return password_verify($password, $user['password_hash']);
  }
}
