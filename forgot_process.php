<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require 'config.php';

header('Content-Type: application/json');

// Hiện lỗi nếu có (debug)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Lấy email từ form
$email = trim($_POST['email']);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email không hợp lệ.']);
    exit;
}

// Kiểm tra email có tồn tại trong DB không
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Email không tồn tại.']);
    exit;
}

// Tạo token và thời gian hết hạn
$token = bin2hex(random_bytes(32));
$expires_at = date('Y-m-d H:i:s', strtotime('+30 minutes'));

// Xóa token cũ (nếu có)
$conn->query("DELETE FROM password_resets WHERE email = '$email'");

// Lưu token mới vào DB
$stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $email, $token, $expires_at);
$stmt->execute();

// Tạo link đặt lại mật khẩu
$link = "http://localhost/QLTBCNTT/CNTT/reset_password.php?token=$token";

$mail = new PHPMailer(true);
try {
    // Cấu hình SMTP
    $mail->isSMTP();
    $mail->Host = $mail_config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $mail_config['username'];
    $mail->Password = $mail_config['password'];
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Thông tin người gửi và người nhận
    $mail->setFrom($mail_config['from'], $mail_config['from_name']);
    $mail->addAddress($email);

    // Gắn logo nếu có
    $mail->AddEmbeddedImage('logo.png', 'logo_cid');

    $mail->isHTML(true);
    $mail->Subject = '🔐 Yêu cầu khôi phục mật khẩu - Bệnh viện Ung Bướu';

    $mail->Body = '
    <div style="max-width:600px;margin:auto;border:1px solid #eee;border-radius:10px;overflow:hidden;font-family:\'Segoe UI\',sans-serif;">
      <div style="background:#ffffff;padding:20px;text-align:center;">
        <img src="cid:logo_cid" alt="Logo" style="width:100px;height:auto;margin-bottom:10px;">
        <h2 style="margin:0;color:#4CAF50;">HỆ THỐNG QL TBCNTT</h2>
        <p style="margin-top:5px;color:#888;">Bệnh viện Ung Bướu TP.HCM</p>
      </div>

      <div style="padding:30px;background:#fafafa;">
        <p style="font-size:16px;color:#333;">Xin chào,</p>
        <p style="font-size:15px;color:#555;line-height:1.6;">
          Hệ thống ghi nhận yêu cầu khôi phục mật khẩu cho tài khoản của bạn.<br>
          Nếu bạn là người thực hiện, vui lòng nhấn nút bên dưới để đặt lại mật khẩu:
        </p>

        <div style="text-align:center;margin:30px 0;">
          <a href="' . $link . '" style="padding:12px 25px;background:#4CAF50;color:white;text-decoration:none;border-radius:6px;font-weight:bold;">
            Đặt lại mật khẩu
          </a>
        </div>

        <p style="font-size:13px;color:#999;">
          Liên kết này có hiệu lực trong vòng 30 phút. Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email.
        </p>
      </div>

      <div style="background:#f5f5f5;padding:15px;text-align:center;font-size:12px;color:#999;">
        📧 Email được gửi tự động từ hệ thống quản lý thiết bị CNTT. Vui lòng không trả lời.
      </div>
    </div>';

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Liên kết đặt lại mật khẩu đã được gửi đến email của bạn.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi gửi mail: ' . $mail->ErrorInfo]);
}
