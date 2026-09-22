<?php
$host = "localhost";
$user = "root";     // Mặc định XAMPP
$pass = "";         // Mặc định XAMPP để trống
$dbname = "bakes";  // Đảm bảo tên này khớp với tên Database bạn tạo trong phpMyAdmin

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Lỗi kết nối cơ sở dữ liệu: " . mysqli_connect_error()]);
    exit();
}

mysqli_set_charset($conn, "utf8mb4");
?>