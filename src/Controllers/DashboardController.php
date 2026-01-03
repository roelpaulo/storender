<?php

namespace App\Controllers;

use App\Core\Response;

class DashboardController
{
  private function render($template, $data = [])
  {
    extract($data);
    ob_start();
    include dirname(__DIR__, 2) . "/templates/$template.php";
    $content = ob_get_clean();
    include dirname(__DIR__, 2) . "/templates/base.php";
  }

  private function auth()
  {
    session_start();
    if (!isset($_SESSION['user_id'])) {
      header('Location: /login');
      exit;
    }
    return $_SESSION['user_id'];
  }

  public function index()
  {
    $userId = $this->auth();
    $this->render('dashboard', [
      'title' => 'Dashboard | Storender',
      'showNav' => true
    ]);
  }

  public function login()
  {
    $this->render('login', [
      'title' => 'Login | Storender',
      'showNav' => false
    ]);
  }

  public function register()
  {
    $this->render('register', [
      'title' => 'Register | Storender',
      'showNav' => false
    ]);
  }

  public function logout()
  {
    session_start();
    session_destroy();
    header('Location: /login');
    exit;
  }

  public function resetPassword()
  {
    $this->render('reset-password', [
      'title' => 'Reset Password | Storender',
      'showNav' => false
    ]);
  }
}
