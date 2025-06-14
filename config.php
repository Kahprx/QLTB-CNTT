<?php
$conn = new mysqli('localhost', 'root', '', 'qltbcntt');
if ($conn->connect_error) {
    die("Kết nối CSDL thất bại: " . $conn->connect_error);
}

$mail_config = [
    'host' => 'smtp.gmail.com',
    'username' => 'kaquach62@gmail.com',
    'password' => 'jbyb zino hstz jpwg',
    'from' => 'your_email@gmail.com',
    'from_name' => 'Hệ thống QL TBCNTT'
];
?>
