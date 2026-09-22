<?php
require_once 'db.php';

// Xử lý cập nhật trạng thái đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_update'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    header("Location: admin.php");
    exit;
}

// Lấy danh sách đơn hàng
$sql = "SELECT * FROM orders ORDER BY created_at DESC";
$orders_result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng - BAKES</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .order-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .order-table th, .order-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        .order-table th { background: #2c3e50; color: #fff; font-weight: 600; }
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-pending { background: #ffeaa7; color: #d63031; }
        .badge-processing { background: #74b9ff; color: #0984e3; }
        .badge-completed { background: #55efc4; color: #00b894; }
        .badge-cancelled { background: #dfe6e9; color: #636e72; }
        .item-detail { font-size: 13px; color: #555; background: #f8f9fa; padding: 6px; border-radius: 4px; margin-bottom: 4px; }
        .btn-status { padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; color: white; margin-right: 4px; }
        .btn-process { background: #0984e3; }
        .btn-complete { background: #00b894; }
        .btn-cancel { background: #d63031; }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="logo"><a href="index.php" style="text-decoration:none; color:inherit;">BAKES ADMIN</a></div>
        <div class="nav-right">
            <a href="index.php" class="admin-link"><i class="fas fa-store"></i> Về Trang Bán Hàng</a>
        </div>
    </header>

    <div class="admin-container">
        <h2><i class="fas fa-list-alt"></i> Danh Sách Đơn Hàng</h2>
        <br>

        <table class="order-table">
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Khách Hàng</th>
                    <th>Địa Chỉ</th>
                    <th>Chi Tiết Món</th>
                    <th>Tổng Tiền</th>
                    <th>Trạng Thái</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders_result && $orders_result->num_rows > 0): ?>
                    <?php while ($order = $orders_result->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?= $order['id'] ?></strong><br><small style="color:#888;"><?= date('H:i d/m/Y', strtotime($order['created_at'])) ?></small></td>
                            <td>
                                <strong><?= htmlspecialchars($order['customer_name']) ?></strong><br>
                                <small><?= htmlspecialchars($order['customer_phone']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($order['customer_address']) ?></td>
                            <td>
                                <?php
                                $order_id = $order['id'];
                                $details_sql = "SELECT od.*, p.name AS product_name, s.name AS size_name 
                                                FROM order_details od 
                                                LEFT JOIN products p ON od.product_id = p.id 
                                                LEFT JOIN sizes s ON od.size_id = s.id 
                                                WHERE od.order_id = $order_id";
                                $details_res = $conn->query($details_sql);
                                while ($d = $details_res->fetch_assoc()):
                                ?>
                                    <div class="item-detail">
                                        • <strong><?= htmlspecialchars($d['product_name']) ?></strong> x <?= $d['quantity'] ?>
                                        <?= $d['size_name'] ? " (Size " . $d['size_name'] . ")" : "" ?>
                                        <?= $d['toppings_text'] ? "<br><small style='color:#e67e22;'>Topping: " . htmlspecialchars($d['toppings_text']) . "</small>" : "" ?>
                                    </div>
                                <?php endwhile; ?>
                            </td>
                            <td><strong style="color:#e74c3c;"><?= number_format($order['total_money'], 0, ',', '.') ?>đ</strong></td>
                            <td>
                                <?php if ($order['status'] === 'pending'): ?>
                                    <span class="badge badge-pending">Chờ xác nhận tiền</span>
                                <?php elseif ($order['status'] === 'processing'): ?>
                                    <span class="badge badge-processing">Đang chuẩn bị</span>
                                <?php elseif ($order['status'] === 'completed'): ?>
                                    <span class="badge badge-completed">Hoàn thành</span>
                                <?php else: ?>
                                    <span class="badge badge-cancelled">Đã hủy</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <input type="hidden" name="action_update" value="1">
                                    
                                    <?php if ($order['status'] === 'pending'): ?>
                                        <button type="submit" name="status" value="processing" class="btn-status btn-process" title="Tiền đã về, bắt đầu làm món"><i class="fas fa-check"></i> Đã nhận tiền</button>
                                        <button type="submit" name="status" value="cancelled" class="btn-status btn-cancel" onclick="return confirm('Hủy đơn hàng này?');"><i class="fas fa-times"></i> Hủy</button>
                                    <?php elseif ($order['status'] === 'processing'): ?>
                                        <button type="submit" name="status" value="completed" class="btn-status btn-complete"><i class="fas fa-truck"></i> Hoàn thành</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding: 30px; color: #888;">Chưa có đơn hàng nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>