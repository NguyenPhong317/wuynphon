<?php
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

$category = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT p.*, c.name AS category_name 
        FROM products p 
        INNER JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";

$params = [];
$types = "";

// Kiểm tra lọc danh mục
if ($category !== 'all' && $category !== '' && $category !== 'Tất cả') {
    $sql .= " AND (c.name = ? OR LOWER(c.name) = LOWER(?))";
    $params[] = $category;
    $params[] = $category;
    $types .= "ss";
}

// Kiểm tra từ khóa tìm kiếm
if ($search !== '') {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

$sql .= " ORDER BY p.id ASC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode($products, JSON_UNESCAPED_UNICODE);