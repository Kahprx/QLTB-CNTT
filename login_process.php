<?php
require 'config.php';
session_start();

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password'])) {
        $_SESSION['user'] = $row['email'];
        echo "<script>alert('Đăng nhập thành công!');location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Sai mật khẩu!');history.back();</script>";
    }
} else {
    echo "<script>alert('Tài khoản không tồn tại!');history.back();</script>";
}
?>
