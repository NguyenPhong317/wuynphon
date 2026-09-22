<?php
header('Content-Type: application/json');
require_once 'db.php';

// Nhận dữ liệu JSON gửi từ JavaScript
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Dữ liệu không hợp lệ"]);
    exit();
}

$total_amount = $data['total'] ?? 0;
$payment_method = $data['payment_method'] ?? 'Tiền mặt';
$items = $data['items'] ?? [];

// Thêm đơn hàng vào database
$sql = "INSERT INTO orders (total_amount, payment_method, created_at) VALUES ('$total_amount', '$payment_method', NOW())";

if (mysqli_query($conn, $sql)) {
    echo json_encode(["status" => "success", "message" => "Lưu đơn hàng thành công!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Lỗi SQL: " . mysqli_error($conn)]);
}

mysqli_close($conn);
?>