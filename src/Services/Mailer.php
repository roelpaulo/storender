<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Core\Config;

class Mailer
{
  public static function send($to, $subject, $body, $isHtml = true)
  {
    $mail = new PHPMailer(true);

    try {
      // Server settings
      $mail->isSMTP();
      $mail->Host = Config::get('SMTP_HOST');
      $mail->SMTPAuth = !empty(Config::get('SMTP_USER'));
      if ($mail->SMTPAuth) {
        $mail->Username = Config::get('SMTP_USER');
        $mail->Password = Config::get('SMTP_PASS');
      }

      $port = (int) Config::get('SMTP_PORT', 587);
      $mail->Port = $port;

      if ($port === 465) {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      } elseif ($port === 587) {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      } else {
        $mail->SMTPSecure = '';
        $mail->SMTPAutoTLS = false;
      }

      // Recipients
      $mail->setFrom(Config::get('SMTP_FROM_EMAIL'), Config::get('SMTP_FROM_NAME'));
      $mail->addAddress($to);

      // Content
      $mail->isHTML($isHtml);
      $mail->Subject = $subject;
      $mail->Body = $body;

      $mail->send();
      return true;
    } catch (Exception $e) {
      error_log("Mailer Error: {$mail->ErrorInfo}");
      return false;
    }
  }
}
