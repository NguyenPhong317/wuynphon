<?php
header('Content-Type: application/json');
require_once 'db.php';

// Nhận dữ liệu JSON gửi từ JavaScript
$input = file_get_contents("php://input");
$data = json_decode($input, true);

$table_id = $data['table_id'] ?? null;
$status = $data['status'] ?? null; // 'available', 'occupied', 'cleaning'

if (!$table_id || !$status) {
    echo json_encode(["status" => "error", "message" => "Thiếu thông tin bàn hoặc trạng thái!"]);
    exit();
}

// Cập nhật trạng thái bàn vào Database
$sql = "UPDATE tables SET status = '$status' WHERE id = '$table_id'";

if (mysqli_query($conn, $sql)) {
    echo json_encode(["status" => "success", "message" => "Đã cập nhật trạng thái bàn thành công!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Lỗi SQL: " . mysqli_error($conn)]);
}

mysqli_close($conn);
?>