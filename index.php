<?php
require_once 'db.php';

// Lấy thông tin bàn từ URL
$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;
$customer_name_param = isset($_GET['customer_name']) ? htmlspecialchars($_GET['customer_name']) : '';

$table_name = "Mang Đi";
if ($table_id > 0) {
    $t_res = $conn->query("SELECT name FROM tables WHERE id = $table_id");
    if ($t_res && $t_row = $t_res->fetch_assoc()) {
        $table_name = $t_row['name'];
    }
}

// Lấy danh sách danh mục từ DB
$categories = $conn->query("SELECT * FROM categories ORDER BY id ASC");

// Lấy danh sách sản phẩm duy nhất theo tên
$products = $conn->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.status = 'available'
    GROUP BY p.name 
    ORDER BY p.id ASC
");

$products_list = [];
if ($products) {
    while ($row = $products->fetch_assoc()) {
        $products_list[] = $row;
    }
}

// Lấy danh sách Size và Topping từ DB
$sizes = $conn->query("SELECT * FROM sizes ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
$toppings = $conn->query("SELECT * FROM toppings WHERE status = 'available' ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAKES - Đặt Món & Thanh Toán POS</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        corePlugins: { preflight: false }
      }
    </script>
    
    <!-- Google Fonts & Lucide Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', -apple-system, sans-serif; }
        body { background-color: #fcf8f9; color: #333; }

        /* NAVBAR TRÊN CÙNG */
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 30px;
            background: #fff;
            border-bottom: 1px solid #f0e6e8;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-weight: 900;
            font-size: 26px;
            color: #800020;
            letter-spacing: 1px;
            text-decoration: none;
        }

        .search-box {
            position: relative;
            width: 380px;
        }

        .search-box input {
            width: 100%;
            padding: 8px 40px 8px 18px;
            border: 1px solid #e8d8dc;
            border-radius: 20px;
            outline: none;
            background: #faf8f9;
            font-size: 14px;
        }

        .search-box i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #800020;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn-table-nav {
            background: #800020;
            color: #fff;
            text-decoration: none;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .cart-icon {
            position: relative;
            color: #800020;
            font-size: 20px;
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -10px;
            background: #800020;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            border-radius: 50%;
            padding: 2px 6px;
        }

        /* BỐ CỤC CHÍNH */
        .main-layout {
            display: flex;
            max-width: 100%;
            margin: 20px auto;
            padding: 0 20px;
            gap: 20px;
        }

        /* 1. CỘT DANH MỤC TRÁI */
        .sidebar-categories {
            width: 160px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
            position: sticky;
            top: 80px;
            height: fit-content;
        }

        .sidebar-title {
            font-size: 13px;
            font-weight: 800;
            color: #800020;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .cat-btn {
            display: block;
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #800020;
            background: #fff;
            color: #800020;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .cat-btn:hover, .cat-btn.active {
            background: #800020;
            color: #fff;
        }

        /* 2. CỘT SẢN PHẨM GIỮA */
        .content-area { flex-grow: 1; }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
        }

        .product-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            border: 1px solid #f2e6e8;
        }

        .product-img {
            width: 100%;
            height: 140px;
            object-fit: cover;
        }

        .product-info {
            padding: 10px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .cat-tag { font-size: 10px; color: #800020; font-weight: bold; text-transform: uppercase; }
        .p-title { font-size: 14px; font-weight: 700; color: #222; margin: 2px 0 4px 0; }
        .p-desc { font-size: 11px; color: #666; margin-bottom: 10px; line-height: 1.3; flex-grow: 1; }
        .p-bottom { display: flex; align-items: center; justify-content: space-between; margin-top: auto; }
        .p-price { font-size: 13px; font-weight: bold; color: #800020; }

        .btn-add {
            background: #800020;
            color: #fff;
            border: none;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-add:hover { background: #5c0017; }

        /* 3. CỘT PAYMENT PHẢI */
        .payment-sidebar {
            width: 330px;
            flex-shrink: 0;
            background: #fff;
            border: 1.5px solid #f2cfd6;
            border-radius: 12px;
            padding: 18px;
            position: sticky;
            top: 80px;
            height: fit-content;
            box-shadow: 0 4px 12px rgba(128, 0, 32, 0.05);
        }

        .payment-header {
            font-size: 20px;
            font-weight: 800;
            color: #800020;
            text-align: center;
            border-bottom: 2px solid #800020;
            padding-bottom: 8px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .customer-input { margin-bottom: 12px; }
        .customer-input label { font-size: 12px; font-weight: 700; color: #444; display: block; margin-bottom: 4px; }
        .customer-input input {
            width: 100%;
            padding: 7px 10px;
            border: 1px solid #e0c8ce;
            border-radius: 6px;
            font-size: 12px;
            outline: none;
        }

        .bill-header {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            font-size: 13px;
            color: #555;
            padding-bottom: 6px;
            border-bottom: 1px dashed #e0c8ce;
            margin-bottom: 10px;
        }

        .bill-items { max-height: 220px; overflow-y: auto; margin-bottom: 15px; }
        .bill-item { border-bottom: 1px solid #f7ecee; padding-bottom: 8px; margin-bottom: 8px; }
        .bill-item-row { display: flex; justify-content: space-between; align-items: center; font-size: 13px; }
        .bill-item-name { font-weight: 700; color: #222; }
        .bill-item-options { font-size: 11px; color: #777; margin-top: 3px; line-height: 1.2; }
        .bill-item-qty { display: flex; align-items: center; gap: 6px; }
        
        .btn-qty {
            background: #f0e6e8;
            border: none;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            color: #800020;
        }

        .bill-item-price { font-weight: 700; color: #800020; }
        .empty-cart { text-align: center; color: #aaa; font-size: 13px; padding: 20px 0; }

        .discount-section { border-top: 1px solid #f2e6e8; padding-top: 12px; margin-bottom: 12px; }
        .discount-input-group { display: flex; gap: 6px; margin-top: 6px; }
        .discount-input-group input {
            flex-grow: 1;
            padding: 6px 10px;
            border: 1px solid #e0c8ce;
            border-radius: 6px;
            font-size: 12px;
            text-transform: uppercase;
        }

        .btn-apply {
            background: #800020;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            font-weight: 600;
        }

        .summary-line { display: flex; justify-content: space-between; font-size: 13px; margin-top: 8px; color: #555; }
        .summary-line.total {
            border-top: 2px solid #800020;
            padding-top: 10px;
            margin-top: 12px;
            font-size: 16px;
            font-weight: 800;
            color: #800020;
        }

        .btn-checkout-pos {
            width: 100%;
            background: #1f7285;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 14px;
            cursor: pointer;
            margin-top: 15px;
            text-transform: uppercase;
            transition: all 0.2s;
        }
        .btn-checkout-pos:hover { background: #185e6e; }

        /* MODAL OPTION */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-card {
            background: #fff;
            border-radius: 12px;
            width: 420px;
            max-width: 90%;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }

        .modal-title {
            font-size: 18px;
            font-weight: 800;
            color: #800020;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-close { background: none; border: none; font-size: 20px; cursor: pointer; color: #888; }
        .option-group { margin-bottom: 15px; }
        .option-title { font-size: 13px; font-weight: 700; color: #444; margin-bottom: 8px; }
        .option-items { display: flex; flex-wrap: wrap; gap: 8px; }

        .option-btn {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: #fff;
            font-size: 12px;
            cursor: pointer;
        }
        .option-btn.active { border-color: #800020; background: #800020; color: #fff; font-weight: bold; }

        .checkbox-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #eee;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
        }

        .btn-confirm-add {
            width: 100%;
            background: #800020;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            margin-top: 10px;
        }

        /* MODAL POS THANH TOÁN */
        .pos-modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 2000;
            overflow-y: auto;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <header class="navbar">
        <a href="index.php" class="logo">BAKES</a>
        <div class="search-box">
            <input type="text" id="searchInput" onkeyup="searchProduct()" placeholder="Tìm món ăn, đồ uống...">
            <i class="fas fa-search"></i>
        </div>
        <div class="nav-right">
            <a href="pos_tables.php" class="btn-table-nav">
                <i class="fas fa-chair"></i> Vị Trí: <?= $table_name ?>
            </a>
            <div class="cart-icon">
                <i class="fas fa-shopping-bag"></i>
                <span class="cart-badge" id="cartBadge">0</span>
            </div>
        </div>
    </header>

    <!-- Main 3 Cột -->
    <div class="main-layout">
        
        <!-- CỘT 1: DANH MỤC TRÁI -->
        <aside class="sidebar-categories">
            <div class="sidebar-title">Danh mục</div>
            <button class="cat-btn active" onclick="filterCategory('all', this)">Tất cả</button>
            <?php if ($categories && $categories->num_rows > 0): ?>
                <?php while($cat = $categories->fetch_assoc()): ?>
                    <button class="cat-btn" onclick="filterCategory('<?= strtolower(trim($cat['name'])) ?>', this)">
                        <?= htmlspecialchars($cat['name']) ?>
                    </button>
                <?php endwhile; ?>
            <?php endif; ?>
        </aside>

        <!-- CỘT 2: GIỮA (SẢN PHẨM) -->
        <main class="content-area">
            <div class="product-grid" id="productGrid">
                <?php foreach($products_list as $p): ?>
                    <div class="product-card" data-cat="<?= strtolower(trim($p['category_name'])) ?>" data-name="<?= strtolower(trim($p['name'])) ?>">
                        <img class="product-img" src="<?= $p['image'] ?>" alt="<?= htmlspecialchars($p['name']) ?>" onerror="this.src='https://via.placeholder.com/200?text=BAKES'">
                        <div class="product-info">
                            <div class="cat-tag"><?= htmlspecialchars($p['category_name']) ?></div>
                            <div class="p-title"><?= htmlspecialchars($p['name']) ?></div>
                            <div class="p-desc"><?= htmlspecialchars($p['description']) ?></div>
                            <div class="p-bottom">
                                <span class="p-price"><?= number_format($p['price'], 0, ',', '.') ?>đ</span>
                                <button class="btn-add" onclick="openOptionModal(<?= $p['id'] ?>, '<?= addslashes($p['name']) ?>', <?= $p['price'] ?>, '<?= strtolower($p['category_name']) ?>')">+ Đặt món</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>

        <!-- CỘT 3: PAYMENT BÊN PHẢI -->
        <aside class="payment-sidebar">
            <div class="payment-header">Payment</div>

            <!-- Customer Info -->
            <div class="customer-input">
                <label><i class="fas fa-user"></i> Tên Khách Hàng</label>
                <input type="text" id="customerName" value="<?= $customer_name_param ?>" placeholder="Nhập tên khách hàng...">
            </div>

            <div class="customer-input">
                <label><i class="fas fa-phone"></i> Số Điện Thoại</label>
                <input type="text" id="customerPhone" placeholder="SĐT (nếu có)...">
            </div>

            <div class="customer-input">
                <label><i class="fas fa-map-marker-alt"></i> Địa Chỉ</label>
                <input type="text" id="customerAddress" placeholder="Địa chỉ giao (nếu có)...">
            </div>

            <!-- Bill Item & QTY -->
            <div class="bill-header">
                <span>Bill item</span>
                <span>QTY</span>
            </div>

            <div class="bill-items" id="billItems">
                <div class="empty-cart">Chưa chọn món nào</div>
            </div>

            <!-- Discount Section -->
            <div class="discount-section">
                <label style="font-size: 13px; font-weight: 700; color: #444;">+ Discount</label>
                <div class="discount-input-group">
                    <input type="text" id="discountCode" placeholder="Mã giảm (BAKES55, BAKES10...)">
                    <button class="btn-apply" onclick="applyDiscount()">Áp dụng</button>
                </div>
            </div>

            <!-- Discount item & Total -->
            <div class="summary-line">
                <span>Tạm tính:</span>
                <span id="subTotalDisplay">0đ</span>
            </div>

            <div class="summary-line">
                <span>Discount item (<span id="discountPercentTag">0%</span>):</span>
                <span id="discountDisplay" style="color: #d9534f; font-weight: bold;">-0đ</span>
            </div>

            <div class="summary-line total">
                <span>Total:</span>
                <span id="totalDisplay">0đ</span>
            </div>

            <!-- NÚT MỞ MÀN HÌNH POS THANH TOÁN -->
            <button class="btn-checkout-pos" onclick="openPosPaymentModal()"><i class="fas fa-credit-card"></i> Thanh Toán POS</button>
        </aside>

    </div>

    <!-- POPUP MODAL TÙY CHỌN SIZE / ĐƯỜNG / ĐÁ / TOPPING -->
    <div class="modal-overlay" id="optionModal">
        <div class="modal-card">
            <div class="modal-title">
                <span id="modalProductName">Tên sản phẩm</span>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>

            <!-- Size Options -->
            <div class="option-group" id="sizeGroup">
                <div class="option-title">Chọn Size</div>
                <div class="option-items">
                    <?php foreach($sizes as $idx => $s): ?>
                        <button class="option-btn size-btn <?= $idx === 0 ? 'active' : '' ?>" 
                                data-id="<?= $s['id'] ?>" 
                                data-name="<?= $s['name'] ?>" 
                                data-extra="<?= $s['extra_price'] ?>" 
                                onclick="selectSize(this)">
                            Size <?= $s['name'] ?> (+<?= number_format($s['extra_price'], 0, ',', '.') ?>đ)
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sugar Options -->
            <div class="option-group" id="sugarGroup">
                <div class="option-title">Mức Đường (Sugar)</div>
                <div class="option-items">
                    <button class="option-btn sugar-btn" onclick="selectSugar('100%', this)">100% Đường</button>
                    <button class="option-btn sugar-btn active" onclick="selectSugar('70%', this)">70% Đường</button>
                    <button class="option-btn sugar-btn" onclick="selectSugar('50%', this)">50% Đường</button>
                    <button class="option-btn sugar-btn" onclick="selectSugar('0%', this)">0% Đường</button>
                </div>
            </div>

            <!-- Ice Options -->
            <div class="option-group" id="iceGroup">
                <div class="option-title">Mức Đá (Ice)</div>
                <div class="option-items">
                    <button class="option-btn ice-btn active" onclick="selectIce('100%', this)">100% Đá</button>
                    <button class="option-btn ice-btn" onclick="selectIce('50%', this)">50% Đá</button>
                    <button class="option-btn ice-btn" onclick="selectIce('0%', this)">Không Đá</button>
                </div>
            </div>

            <!-- Topping Options -->
            <div class="option-group" id="toppingGroup">
                <div class="option-title">Thêm Topping</div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <?php foreach($toppings as $top): ?>
                        <label class="checkbox-label">
                            <span><input type="checkbox" class="topping-cb" value="<?= htmlspecialchars($top['name']) ?>" data-price="<?= $top['price'] ?>"> <?= htmlspecialchars($top['name']) ?></span>
                            <b>+<?= number_format($top['price'], 0, ',', '.') ?>đ</b>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <button class="btn-confirm-add" onclick="confirmAddToCart()">Xác Nhận Thêm Về Bill</button>
        </div>
    </div>

    <!-- MÀN HÌNH THANH TOÁN POS CÓ VIETQR -->
    <div class="pos-modal-overlay" id="posPaymentModal">
        <div class="w-full max-w-[1100px] bg-white rounded-3xl shadow-2xl border border-neutral-300/80 overflow-hidden flex flex-col relative my-auto">
            
            <header class="bg-[#F8F1F1] border-b border-[#ebdcdc] px-6 py-4 text-center relative">
                <button onclick="closePosPaymentModal()" class="absolute top-4 right-4 text-slate-500 hover:text-slate-900 text-xl font-bold px-3 py-1 bg-white rounded-full border">✕ Đóng</button>
                <div class="text-xs sm:text-sm font-semibold text-slate-600 tracking-wide uppercase">
                    Khách Hàng: <span class="text-amber-700 font-bold" id="posCustomerNameDisplay">Khách Lẻ</span> &bull; Vị trí: <?= $table_name ?>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 mt-1 uppercase font-['Space_Grotesk']">
                    Xác Nhận Thanh Toán POS
                </h1>
            </header>

            <div class="p-4 sm:p-6 md:p-8 grid grid-cols-1 lg:grid-cols-12 gap-6 bg-[#F5F5F5]">
                
                <!-- CỘT TRÁI: Đơn hàng -->
                <div class="lg:col-span-5 flex flex-col gap-5">
                    <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-sm flex flex-col">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <h2 class="text-lg font-bold text-slate-900">Chi Tiết Đơn Hàng</h2>
                        </div>

                        <div id="pos-order-items-list" class="divide-y divide-slate-100 my-2 max-h-[220px] overflow-y-auto pr-1"></div>

                        <div class="pt-3 border-t border-slate-100 space-y-1.5 text-sm">
                            <div class="flex justify-between text-slate-600 font-medium">
                                <span>Tạm tính</span>
                                <span id="pos-subtotal-val" class="font-semibold text-slate-800">0 VND</span>
                            </div>
                            <div class="flex justify-between text-slate-600 font-medium">
                                <span>Giảm giá (<span id="pos-discount-percent">0%</span>)</span>
                                <span id="pos-discount-val" class="font-semibold text-red-600">0 VND</span>
                            </div>
                            <div class="flex justify-between text-slate-900 font-bold pt-1 border-t border-dashed border-slate-200">
                                <span>Tổng thanh toán</span>
                                <span id="pos-grand-total-val" class="text-emerald-700 font-black text-lg">0 VND</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CỘT PHẢI: Phương Thức Thanh Toán -->
                <div class="lg:col-span-7 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-lg font-bold text-slate-900">Phương Thức Thanh Toán</h2>
                            <span class="text-xs text-slate-500 font-medium bg-white px-2.5 py-1 rounded-full border border-slate-200">Chạm để chọn</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <button type="button" onclick="selectPayment('cash')" id="btn-cash" class="payment-option text-left p-4 rounded-2xl bg-white border-2 border-slate-200 hover:border-teal-600 transition flex flex-col justify-between min-h-[110px] shadow-sm relative cursor-pointer">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center border border-emerald-200"><i data-lucide="banknote"></i></div>
                                <div class="mt-2">
                                    <h3 class="font-extrabold text-slate-900 text-sm">TIỀN MẶT</h3>
                                    <p class="text-xs text-slate-500 mt-1">Nhận tiền mặt trực tiếp</p>
                                </div>
                            </button>

                            <button type="button" onclick="selectPayment('wallet')" id="btn-wallet" class="payment-option text-left p-4 rounded-2xl bg-white border-2 border-slate-200 hover:border-teal-600 transition flex flex-col justify-between min-h-[110px] shadow-sm relative cursor-pointer">
                                <div class="w-10 h-10 rounded-xl bg-pink-50 text-pink-700 flex items-center justify-center border border-pink-200"><i data-lucide="wallet"></i></div>
                                <div class="mt-2">
                                    <h3 class="font-extrabold text-slate-900 text-sm">VÍ ĐIỆN TỬ</h3>
                                    <p class="text-xs text-slate-500 mt-1">MoMo, ZaloPay...</p>
                                </div>
                            </button>

                            <button type="button" onclick="selectPayment('qr')" id="btn-qr" class="payment-option text-left p-4 rounded-2xl bg-white border-2 border-teal-600 bg-teal-50/20 hover:border-teal-600 transition flex flex-col justify-between min-h-[110px] shadow-sm relative cursor-pointer">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center border border-blue-200"><i data-lucide="qr-code"></i></div>
                                <div class="mt-2">
                                    <h3 class="font-extrabold text-slate-900 text-sm">CHUYỂN KHOẢN VietQR</h3>
                                    <p class="text-xs text-slate-500 mt-1">Quét QR Ngân hàng</p>
                                </div>
                            </button>

                            <button type="button" onclick="selectPayment('card')" id="btn-card" class="payment-option text-left p-4 rounded-2xl bg-white border-2 border-slate-200 hover:border-teal-600 transition flex flex-col justify-between min-h-[110px] shadow-sm relative cursor-pointer">
                                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center border border-amber-200"><i data-lucide="credit-card"></i></div>
                                <div class="mt-2">
                                    <h3 class="font-extrabold text-slate-900 text-sm">QUẸT THẺ POS</h3>
                                    <p class="text-xs text-slate-500 mt-1">Visa/Mastercard/ATM</p>
                                </div>
                            </button>
                        </div>

                        <div id="qr-info-section" class="mt-4 p-4 bg-white rounded-2xl border border-blue-200 shadow-sm flex flex-col sm:flex-row items-center gap-4">
                            <div class="flex-shrink-0 text-center">
                                <img id="vietQrImg" src="" alt="Mã QR" class="w-36 h-36 object-contain border rounded-xl p-1 bg-white shadow-sm">
                                <span class="text-[10px] text-slate-500 mt-1 block" id="qrNoteDisplay">Quét bằng App Ngân Hàng</span>
                            </div>
                            <div class="flex-grow space-y-1.5 text-xs text-slate-700 w-full">
                                <div class="font-bold text-slate-900 text-sm border-b pb-1" id="qrTitleDisplay">Chuyển Khoản Ngân Hàng</div>
                                <div>Ngân hàng: <b id="bankNameDisplay" class="text-blue-700">MBBank</b></div>
                                <div>Chủ tài khoản: <b id="bankAccountNameDisplay" class="text-slate-900">BAKES BAKERY</b></div>
                                <div>Số tài khoản: <b id="bankAccountNumberDisplay" class="text-emerald-700 font-mono text-sm">0792557818</b></div>
                                <div>Số tiền: <b id="qrAmountDisplay" class="text-red-600 font-bold">0 VND</b></div>
                                <div>Nội dung CK: <b id="qrMemoDisplay" class="bg-amber-100 text-amber-900 px-2 py-0.5 rounded font-mono">BAKES POS</b></div>
                            </div>
                        </div>

                    </div>

                    <div class="mt-4 pt-2">
                        <button type="button" onclick="confirmAndSaveOrder()" class="w-full py-4 px-6 rounded-2xl bg-[#1f7285] hover:bg-[#185e6e] text-white font-extrabold text-lg flex items-center justify-center gap-3 transition">
                            <i data-lucide="printer"></i>
                            <span>HOÀN TẤT & LƯU CƠ SỞ DỮ LIỆU</span>
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- SCRIPT XỬ LÝ ĐỒNG BỘ CƠ SỞ DỮ LIỆU -->
    <script>
        const BANK_INFO = {
            bankId: 'MB',
            accountNo: '0792557818',
            accountName: 'BAKES BAKERY',
            bankName: 'MBBank (Ngân Hàng Quân Đội)'
        };

        const tableId = <?= $table_id ?>;
        let cart = [];
        let discountPercent = 0;
        let currentItem = { id: 0, name: '', price: 0 };
        
        let selectedSize = { id: null, name: 'S', extra_price: 0 };
        let selectedSugar = '70%';
        let selectedIce = '100%';
        let selectedPaymentMethod = 'qr';

        function filterCategory(catName, btnElement) {
            document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
            btnElement.classList.add('active');

            let cards = document.querySelectorAll('.product-card');
            cards.forEach(card => {
                let pCat = (card.getAttribute('data-cat') || '').trim();
                card.style.display = (catName === 'all' || pCat === catName) ? 'flex' : 'none';
            });
        }

        function searchProduct() {
            let query = document.getElementById('searchInput').value.toLowerCase().trim();
            document.querySelectorAll('.product-card').forEach(card => {
                let name = card.getAttribute('data-name') || '';
                card.style.display = name.includes(query) ? 'flex' : 'none';
            });
        }

        function openOptionModal(id, name, price, category) {
            currentItem = { id: id, name: name, price: price };
            document.getElementById('modalProductName').innerText = name;
            
            if(category.includes('cake') || category.includes('bakes')) {
                document.getElementById('sugarGroup').style.display = 'none';
                document.getElementById('iceGroup').style.display = 'none';
            } else {
                document.getElementById('sugarGroup').style.display = 'block';
                document.getElementById('iceGroup').style.display = 'block';
            }

            document.querySelectorAll('.topping-cb').forEach(cb => cb.checked = false);
            document.getElementById('optionModal').style.display = 'flex';
        }

        function closeModal() { document.getElementById('optionModal').style.display = 'none'; }

        function selectSize(btn) {
            document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            selectedSize = {
                id: btn.getAttribute('data-id'),
                name: btn.getAttribute('data-name'),
                extra_price: parseFloat(btn.getAttribute('data-extra'))
            };
        }

        function selectSugar(val, btn) {
            selectedSugar = val;
            document.querySelectorAll('.sugar-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }

        function selectIce(val, btn) {
            selectedIce = val;
            document.querySelectorAll('.ice-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }

        function confirmAddToCart() {
            let toppingList = [];
            let toppingExtraPrice = 0;

            document.querySelectorAll('.topping-cb:checked').forEach(cb => {
                toppingList.push(cb.value);
                toppingExtraPrice += parseInt(cb.getAttribute('data-price'));
            });

            let isDrink = document.getElementById('sugarGroup').style.display !== 'none';
            let toppingsText = toppingList.join(', ');
            
            let optionNote = `Size ${selectedSize.name}`;
            if (isDrink) optionNote += ` | Đường ${selectedSugar}, Đá ${selectedIce}`;
            if (toppingsText) optionNote += ` | Topping: ${toppingsText}`;

            let unitPrice = currentItem.price + selectedSize.extra_price + toppingExtraPrice;

            cart.push({
                product_id: currentItem.id,
                name: currentItem.name,
                base_price: currentItem.price,
                size_id: selectedSize.id,
                extra_price: selectedSize.extra_price + toppingExtraPrice,
                price: unitPrice,
                qty: 1,
                sugar: isDrink ? selectedSugar : '100%',
                ice: isDrink ? selectedIce : '100%',
                toppings_text: toppingsText,
                options: optionNote
            });

            closeModal();
            updateBillUI();
        }

        function changeQty(index, delta) {
            if (cart[index]) {
                cart[index].qty += delta;
                if (cart[index].qty <= 0) cart.splice(index, 1);
            }
            updateBillUI();
        }

        function applyDiscount() {
            let code = document.getElementById('discountCode').value.trim().toUpperCase();
            let match = code.match(/BAKES(\d{1,2})$/);

            if (match) {
                let percent = parseInt(match[1]);
                if (percent >= 0 && percent <= 100) {
                    discountPercent = percent;
                    alert(`Áp dụng thành công mã ${code}: Giảm ${discountPercent}%!`);
                } else {
                    alert('Phần trăm giảm từ 0% đến 100%!');
                    discountPercent = 0;
                }
            } else {
                alert('Mã không hợp lệ! Cú pháp: BAKES10, BAKES55...');
                discountPercent = 0;
            }
            updateBillUI();
        }

        function updateBillUI() {
            let billContainer = document.getElementById('billItems');
            let totalQty = 0;
            let subTotal = 0;

            if (cart.length === 0) {
                billContainer.innerHTML = '<div class="empty-cart">Chưa chọn món nào</div>';
            } else {
                let html = '';
                cart.forEach((item, index) => {
                    totalQty += item.qty;
                    let itemTotal = item.price * item.qty;
                    subTotal += itemTotal;

                    html += `
                        <div class="bill-item">
                            <div class="bill-item-row">
                                <span class="bill-item-name">${item.name}</span>
                                <div class="bill-item-qty">
                                    <button class="btn-qty" onclick="changeQty(${index}, -1)">-</button>
                                    <span>${item.qty}</span>
                                    <button class="btn-qty" onclick="changeQty(${index}, 1)">+</button>
                                </div>
                                <span class="bill-item-price">${itemTotal.toLocaleString('vi-VN')}đ</span>
                            </div>
                            <div class="bill-item-options">${item.options}</div>
                        </div>`;
                });
                billContainer.innerHTML = html;
            }

            let discountAmount = (subTotal * discountPercent) / 100;
            let finalTotal = subTotal - discountAmount;

            document.getElementById('cartBadge').innerText = totalQty;
            document.getElementById('subTotalDisplay').innerText = subTotal.toLocaleString('vi-VN') + 'đ';
            document.getElementById('discountPercentTag').innerText = discountPercent + '%';
            document.getElementById('discountDisplay').innerText = '-' + discountAmount.toLocaleString('vi-VN') + 'đ';
            document.getElementById('totalDisplay').innerText = finalTotal.toLocaleString('vi-VN') + 'đ';
        }

        function openPosPaymentModal() {
            if (cart.length === 0) {
                alert('Vui lòng chọn món trước!');
                return;
            }

            let customerName = document.getElementById('customerName').value.trim() || 'Khách Vãng Lai';
            document.getElementById('posCustomerNameDisplay').innerText = customerName;

            let posListContainer = document.getElementById('pos-order-items-list');
            let subTotal = 0;
            let html = '';

            cart.forEach(item => {
                let itemTotal = item.price * item.qty;
                subTotal += itemTotal;

                html += `
                    <div class="py-2 flex justify-between items-center">
                        <div>
                            <div class="font-bold text-slate-800 text-sm">${item.name} x ${item.qty}</div>
                            <div class="text-xs text-slate-400">${item.options}</div>
                        </div>
                        <div class="font-bold text-slate-900 text-sm">${itemTotal.toLocaleString('vi-VN')} VND</div>
                    </div>`;
            });

            posListContainer.innerHTML = html;

            let discountAmount = (subTotal * discountPercent) / 100;
            let finalTotal = subTotal - discountAmount;

            document.getElementById('pos-subtotal-val').innerText = subTotal.toLocaleString('vi-VN') + ' VND';
            document.getElementById('pos-discount-percent').innerText = discountPercent + '%';
            document.getElementById('pos-discount-val').innerText = '-' + discountAmount.toLocaleString('vi-VN') + ' VND';
            document.getElementById('pos-grand-total-val').innerText = finalTotal.toLocaleString('vi-VN') + ' VND';

            let orderMemo = 'BAKES' + Math.floor(1000 + Math.random() * 9000);
            let qrUrl = `https://img.vietqr.io/image/${BANK_INFO.bankId}-${BANK_INFO.accountNo}-compact2.png?amount=${finalTotal}&addInfo=${encodeURIComponent(orderMemo)}&accountName=${encodeURIComponent(BANK_INFO.accountName)}`;

            document.getElementById('vietQrImg').src = qrUrl;
            document.getElementById('bankNameDisplay').innerText = BANK_INFO.bankName;
            document.getElementById('bankAccountNameDisplay').innerText = BANK_INFO.accountName;
            document.getElementById('bankAccountNumberDisplay').innerText = BANK_INFO.accountNo;
            document.getElementById('qrAmountDisplay').innerText = finalTotal.toLocaleString('vi-VN') + ' VND';
            document.getElementById('qrMemoDisplay').innerText = orderMemo;

            selectPayment('qr');
            document.getElementById('posPaymentModal').style.display = 'flex';
            lucide.createIcons();
        }

        function closePosPaymentModal() {
            document.getElementById('posPaymentModal').style.display = 'none';
        }

        function selectPayment(method) {
            selectedPaymentMethod = method;
            document.querySelectorAll('.payment-option').forEach(btn => btn.classList.remove('border-teal-600', 'bg-teal-50/20'));
            
            let activeBtn = document.getElementById('btn-' + method);
            if (activeBtn) activeBtn.classList.add('border-teal-600', 'bg-teal-50/20');

            let qrSection = document.getElementById('qr-info-section');
            if (method === 'qr' || method === 'wallet') {
                qrSection.style.display = 'flex';
            } else {
                qrSection.style.display = 'none';
            }
        }

        // ĐỒNG BỘ DỮ LIỆU ĐẾN SERVER VÀ LƯU VÀO MYSQL DB
        function confirmAndSaveOrder() {
            const customerName = document.getElementById('customerName').value.trim() || 'Khách Vãng Lai';
            const customerPhone = document.getElementById('customerPhone').value.trim();
            const customerAddress = document.getElementById('customerAddress').value.trim();

            const payload = {
                table_id: tableId,
                customer_name: customerName,
                customer_phone: customerPhone,
                customer_address: customerAddress,
                cart: cart
            };

            fetch('process_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(`Thanh toán thành công! Mã đơn: #${data.order_id}\nPhương thức: ${selectedPaymentMethod.toUpperCase()}`);
                    window.location.href = 'pos_tables.php';
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Lỗi kết nối cơ sở dữ liệu!');
            });
        }
    </script>
</body>
</html>