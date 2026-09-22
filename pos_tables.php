<?php
require_once 'db.php';
$tables =$conn->query("SELECT * FROM tables ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAKES POS - Sơ Đồ Bàn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-[#fcf8f9] text-gray-800 font-sans p-6">

    <!-- Header -->
    <header class="flex justify-between items-center bg-white p-4 rounded-2xl shadow-sm border mb-6">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-black text-[#800020]">Thu ngân - <span class="text-pink-600 font-semibold text-lg">Nguyễn Văn A</span></h1>
        </div>
        <div class="text-gray-500 font-medium text-lg" id="clock">--:--:--</div>
    </header>

    <!-- Khu vực / Tầng -->
    <div class="flex gap-3 mb-6 overflow-x-auto pb-2">
        <button class="px-6 py-2.5 bg-[#800020] text-white font-bold rounded-xl shadow">Ăn tại chỗ (GF)</button>
        <button class="px-6 py-2.5 bg-white text-gray-700 font-bold rounded-xl border hover:bg-gray-50">Ăn tại chỗ (1F)</button>
        <a href="index.php?table_id=0" class="px-6 py-2.5 bg-white text-gray-700 font-bold rounded-xl border hover:bg-gray-50">Mang đi</a>
        <button class="px-6 py-2.5 bg-white text-gray-700 font-bold rounded-xl border hover:bg-gray-50">Trực tuyến</button>
    </div>

    <!-- Lưới danh sách Bàn -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php while($t =$tables->fetch_assoc()): ?>
            <?php 
                $res_query =$conn->query("SELECT * FROM reservations WHERE table_id = {$t['id']} AND status = 'confirmed' LIMIT 1");
                $res_info = $res_query ? $res_query->fetch_assoc() : null;
                
                $status_bg = "bg-white border-gray-200";
                $status_text = "Trống";
                
                if($t['status'] == 'occupied') {$status_bg = "bg-red-50 border-red-200";
                    $status_text = "Đang phục vụ";
                } else if($t['status'] == 'reserved') {$status_bg = "bg-amber-50 border-amber-300";
                    $status_text = "Đã đặt bàn trước";
                } else if($t['status'] == 'cleaning') {$status_bg = "bg-gray-100 border-gray-300";
                    $status_text = "Đã dọn sạch";
                }
            ?>
            <div onclick="selectTable(<?= $t['id'] ?>, '<?=$t['status'] ?>', '<?= $res_info ? addslashes($res_info['customer_name']) : '' ?>')" 
                 class="<?= $status_bg ?> border-2 rounded-2xl p-5 shadow-sm hover:shadow-md cursor-pointer transition flex flex-col justify-between h-44 relative">
                
                <div class="flex justify-between items-start">
                    <span class="text-2xl font-black text-gray-800"><?= $t['id'] ?></span>
                    <span class="text-xs px-3 py-1 rounded-full font-bold bg-white border shadow-sm text-gray-700">
                        <?= $status_text ?>
                    </span>
                </div>

                <div class="text-center">
                    <div class="font-bold text-xl text-[#800020]"><?= $t['name'] ?></div>
                    <?php if($res_info): ?>
                        <div class="text-xs text-amber-700 font-bold mt-1 bg-amber-100/60 py-1 px-2 rounded-lg inline-block">
                            <i class="fas fa-user-clock mr-1"></i> <?= htmlspecialchars($res_info['customer_name']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="text-right text-xs text-gray-400 font-medium">Chạm để chọn món &rarr;</div>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
        function selectTable(id, status, customerName) {
            if (status === 'reserved' && customerName) {
                if (confirm(`Bàn này đã được đặt trước bởi khách: ${customerName}.\nXác nhận xếp khách vào bàn và chọn món?`)) {
                    window.location.href = `index.php?table_id=${id}&customer_name=${encodeURIComponent(customerName)}`;
                }
            } else {
                window.location.href = `index.php?table_id=${id}`;
            }
        }

        setInterval(() => {
            document.getElementById('clock').innerText = new Date().toLocaleTimeString('vi-VN');
        }, 1000);
    </script>
</body>
</html>