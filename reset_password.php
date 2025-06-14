<?php
require 'config.php';

$token = $_GET['token'] ?? '';
$status = '';
$valid = false;
$email = '';

if ($token) {
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $valid = true;
        $email = $res->fetch_assoc()['email'];
    } else {
        $status = "❌ Token không hợp lệ.";
    }
} else {
    $status = "❌ Liên kết không hợp lệ.";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Đặt lại mật khẩu</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    :root {
      --accent: #4CAF50;
      --accent-hover: #43a047;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f0f2f5;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }

    body::before {
      content: "";
      position: fixed;
      inset: 0;
      background: url('ungbuou.png') no-repeat center center fixed;
      background-size: cover;
      filter: brightness(0.6) blur(3px);
      z-index: -2;
      transform: scale(1.05);
      animation: bgZoom 30s ease-in-out infinite alternate;
    }

    @keyframes bgZoom {
      to { transform: scale(1.08); }
    }

    .reset-box {
      background: rgba(255, 255, 255, 0.95);
      padding: 2rem;
      border-radius: 1rem;
      box-shadow: 0 8px 30px rgba(0,0,0,0.2);
      width: 90%;
      max-width: 420px;
      text-align: center;
      position: relative;
      z-index: 1;
      animation: fadeIn 1s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .logo {
      width: 80px;
      margin-bottom: 1rem;
    }

    h2 {
      margin-bottom: 1.5rem;
      color: var(--accent);
    }

    .input-group {
      margin-bottom: 1.5rem;
    }

    input {
      width: 100%;
      padding: 0.75rem;
      font-size: 1rem;
      border: 1px solid #ccc;
      border-radius: 0.75rem;
    }

    input[type="submit"] {
      background: var(--accent);
      color: white;
      border: none;
      cursor: pointer;
      transition: background 0.3s;
    }

    input[type="submit"]:hover {
      background: var(--accent-hover);
    }

    .message {
      text-align: center;
      margin-top: 1rem;
      font-size: 0.95rem;
      color: red;
    }

    .message.success {
      color: green;
    }
  </style>
</head>
<body>
  <div class="reset-box">
    <img src="logo.png" alt="Logo bệnh viện" class="logo">
    <h2>🔐 Đặt lại mật khẩu</h2>

    <?php if ($valid): ?>
      <form method="post">
        <div class="input-group">
          <input type="password" name="new_password" placeholder="Mật khẩu mới" required>
        </div>
        <div class="input-group">
          <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
        </div>
        <input type="submit" name="reset_submit" value="Đổi mật khẩu">
      </form>
    <?php else: ?>
      <div class="message"><?= $status ?></div>
    <?php endif; ?>

    <?php
    if ($valid && isset($_POST['reset_submit'])) {
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        if ($new !== $confirm) {
            echo "<div class='message'>❌ Mật khẩu không khớp.</div>";
        } else {
            $hashed = password_hash($new, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed, $email);
            $stmt->execute();
            $conn->query("DELETE FROM password_resets WHERE email = '$email'");
            echo "<div class='message success'>✅ Mật khẩu đã được đặt lại thành công.</div>";
        }
    }
    ?>
  </div>
</body>
</html>
