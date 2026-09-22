<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || empty($data['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
    exit();
}

$table_id = intval($data['table_id']);
$customer_name = $conn->real_escape_string($data['customer_name']);
$customer_phone = $conn->real_escape_string($data['customer_phone']);
$customer_address = $conn->real_escape_string($data['customer_address']);
$cart = $data['cart'];

// 1. Tính tổng tiền đơn hàng
$total_money = 0;
foreach ($cart as $item) {
    $unit_price = doubleval($item['base_price']) + doubleval($item['extra_price']);
    $total_money += $unit_price * intval($item['qty']);
}

// 2. Chèn dữ liệu vào bảng orders
$sql_order = "INSERT INTO orders (table_id, customer_name, customer_phone, customer_address, total_money, status) VALUES (?, ?, ?, ?, ?, 'completed')";
$stmt = $conn->prepare($sql_order);
$stmt->bind_param("isssd", $table_id, $customer_name, $customer_phone, $customer_address, $total_money);

if ($stmt->execute()) {
    $order_id = $conn->insert_id;

    // 3. Chèn chi tiết món vào order_details
    $sql_item = "INSERT INTO order_details (order_id, product_id, size_id, quantity, price, sugar, ice, toppings_text) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $item_stmt = $conn->prepare($sql_item);

    foreach ($cart as $item) {
        $product_id = intval($item['product_id']);
        $size_id = !empty($item['size_id']) ? intval($item['size_id']) : NULL;
        $quantity = intval($item['qty']);
        $price = doubleval($item['base_price']) + doubleval($item['extra_price']);
        $sugar = $conn->real_escape_string($item['sugar']);
        $ice = $conn->real_escape_string($item['ice']);
        $toppings_text = $conn->real_escape_string($item['toppings_text']);

        $item_stmt->bind_param("iiiidsss", $order_id, $product_id, $size_id, $quantity, $price, $sugar, $ice, $toppings_text);
        $item_stmt->execute();
    }

    // 4. Cập nhật trạng thái bàn thành 'occupied' (Đang phục vụ) nếu có bàn
    if ($table_id > 0) {
        $conn->query("UPDATE tables SET status = 'occupied' WHERE id = $table_id");
        $conn->query("UPDATE reservations SET status = 'checked_in' WHERE table_id = $table_id AND status = 'confirmed'");
    }

    echo json_encode(['success' => true, 'order_id' => $order_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi lưu đơn hàng: ' . $conn->error]);
}
?>