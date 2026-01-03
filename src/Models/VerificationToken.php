<?php

namespace App\Models;

use App\Core\Database;

class VerificationToken
{
  public static function create($email, $token, $type = 'email_verification', $expiresIn = 3600)
  {
    $db = Database::getInstance();
    $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);

    $table = ($type === 'email_verification') ? 'email_verifications' : 'password_resets';

    $stmt = $db->prepare("INSERT OR REPLACE INTO $table (email, token, expires_at) VALUES (?, ?, ?)");
    return $stmt->execute([$email, $token, $expiresAt]);
  }

  public static function verify($token, $type = 'email_verification')
  {
    $db = Database::getInstance();
    $table = ($type === 'email_verification') ? 'email_verifications' : 'password_resets';

    $stmt = $db->prepare("SELECT * FROM $table WHERE token = ? AND expires_at > CURRENT_TIMESTAMP");
    $stmt->execute([$token]);
    return $stmt->fetch();
  }

  public static function delete($email, $type = 'email_verification')
  {
    $db = Database::getInstance();
    $table = ($type === 'email_verification') ? 'email_verifications' : 'password_resets';

    $stmt = $db->prepare("DELETE FROM $table WHERE email = ?");
    return $stmt->execute([$email]);
  }
}
