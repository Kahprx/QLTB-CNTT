<?php
require 'vendor/autoload.php';
require 'config.php';

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = $mail_config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $mail_config['username'];
    $mail->Password = $mail_config['password'];
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom($mail_config['from'], $mail_config['from_name']);
    $mail->addAddress('kaquach62@gmail.com');
    $mail->Subject = 'TEST';
    $mail->Body    = 'Đây là mail test';

    $mail->send();
    echo '✅ Gửi thành công!';
} catch (Exception $e) {
    echo '❌ Lỗi gửi mail: ' . $mail->ErrorInfo;
}
