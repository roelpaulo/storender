<?php

namespace App\Controllers;

use App\Core\Response;
use App\Models\User;
use App\Models\VerificationToken;
use App\Services\Mailer;
use App\Core\Config;

class AuthController
{
  public function register()
  {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? null;
    $password = $input['password'] ?? null;

    if (!$email || !$password) {
      return Response::error('Email and password are required');
    }

    if (User::findByEmail($email)) {
      return Response::error('Email already registered');
    }

    try {
      $userId = User::create($email, $password);

      // Generate verification token
      $token = bin2hex(random_bytes(32));
      VerificationToken::create($email, $token);

      // Send email
      $verifyUrl = Config::get('APP_URL') . "/api/auth/verify-email?token=" . $token;
      $body = "<h1>Welcome to Storender</h1><p>Please verify your email by clicking the link below:</p><a href='$verifyUrl'>Verify Email</a>";
      Mailer::send($email, "Verify your email - Storender", $body);

      return Response::json(['message' => 'User registered successfully. Please check your email to verify your account.'], 201);
    } catch (\Exception $e) {
      return Response::error('Registration failed: ' . $e->getMessage());
    }
  }

  public function login()
  {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? null;
    $password = $input['password'] ?? null;

    if (!$email || !$password) {
      return Response::error('Email and password are required');
    }

    $user = User::findByEmail($email);
    if (!$user || !User::verifyPassword($user, $password)) {
      return Response::error('Invalid credentials', 401);
    }

    if ($user['status'] !== 'active') {
      return Response::error('Please verify your email before logging in', 403);
    }

    // Start session or generate JWT
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];

    return Response::json(['message' => 'Login successful']);
  }

  public function verifyEmail()
  {
    $token = $_GET['token'] ?? null;
    if (!$token) {
      header('Location: /login?error=' . urlencode('Token is required'));
      exit;
    }

    $verification = VerificationToken::verify($token);
    if (!$verification) {
      header('Location: /login?error=' . urlencode('Invalid or expired token'));
      exit;
    }

    $db = \App\Core\Database::getInstance();
    $stmt = $db->prepare("UPDATE users SET status = 'active' WHERE email = ?");
    $stmt->execute([$verification['email']]);

    VerificationToken::delete($verification['email']);

    header('Location: /login?verified=1');
    exit;
  }

  public function forgotPassword()
  {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? null;

    if (!$email) {
      return Response::error('Email is required');
    }

    $user = User::findByEmail($email);
    if ($user) {
      $token = bin2hex(random_bytes(32));
      VerificationToken::create($email, $token, 'password_reset');

      $resetUrl = Config::get('APP_URL') . "/reset-password?token=" . $token;
      $body = "<h1>Password Reset</h1><p>Click the link below to reset your password:</p><a href='$resetUrl'>Reset Password</a>";
      Mailer::send($email, "Reset your password - Storender", $body);
    }

    return Response::json(['message' => 'If an account exists with that email, a password reset link has been sent.']);
  }

  public function resetPassword()
  {
    $input = json_decode(file_get_contents('php://input'), true);
    $token = $input['token'] ?? null;
    $password = $input['password'] ?? null;

    if (!$token || !$password) {
      return Response::error('Token and password are required');
    }

    $reset = VerificationToken::verify($token, 'password_reset');
    if (!$reset) {
      return Response::error('Invalid or expired token', 400);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $db = \App\Core\Database::getInstance();
    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
    $stmt->execute([$hash, $reset['email']]);

    VerificationToken::delete($reset['email'], 'password_reset');

    return Response::json(['message' => 'Password reset successfully.']);
  }
}
