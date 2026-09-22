CREATE DATABASE IF NOT EXISTS Bakes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE Bakes;

-- 1. BẢNG CATEGORIES
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255)
);

-- 2. BẢNG PRODUCTS
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(500),
    status ENUM('available', 'unavailable', 'sold_out') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- 3. BẢNG SIZES
CREATE TABLE IF NOT EXISTS sizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    extra_price DECIMAL(10,2) DEFAULT 0
);

-- 4. BẢNG TOPPINGS
CREATE TABLE IF NOT EXISTS toppings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) DEFAULT 0,
    status ENUM('available', 'unavailable') DEFAULT 'available'
);

-- 5. BẢNG QUẢN LÝ BÀN (Đã cập nhật trạng thái chuẩn 'available' thay cho 'ready')
CREATE TABLE IF NOT EXISTS tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    area VARCHAR(50) DEFAULT 'GF',
    status ENUM('available', 'occupied', 'reserved', 'cleaning') DEFAULT 'available'
);

-- 6. BẢNG ĐẶT BÀN TRƯỚC
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    booking_time DATETIME NOT NULL,
    status ENUM('pending', 'confirmed', 'checked_in', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (table_id) REFERENCES tables(id)
);

-- 7. BẢNG ORDERS
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_id INT NULL,
    customer_name VARCHAR(100) NOT NULL DEFAULT 'Khách vãng lai',
    customer_phone VARCHAR(20) DEFAULT '',
    customer_address TEXT NULL,
    total_money DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'Tiền mặt',
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (table_id) REFERENCES tables(id)
);

-- 8. BẢNG ORDER_DETAILS
CREATE TABLE IF NOT EXISTS order_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    size_id INT,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    sugar VARCHAR(20) DEFAULT '100%',
    ice VARCHAR(20) DEFAULT '100%',
    toppings_text VARCHAR(255),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (size_id) REFERENCES sizes(id)
);

-- ==========================================
-- CHÈN DỮ LIỆU MẪU
-- ==========================================

-- Thêm Danh Mục
INSERT INTO categories (id, name, description) VALUES
(1, 'Coffee', 'Các loại cà phê'),
(2, 'Tea', 'Các loại trà'),
(3, 'Matcha', 'Các loại matcha'),
(4, 'Bakes good', 'Các loại bánh ngọt'),
(5, 'CAKES', 'Các loại bánh lớn')
ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description);

-- Thêm Sản Phẩm
INSERT INTO products (category_id, name, description, price, image) VALUES
(1, 'Americano', 'Cà phê Espresso kết hợp với nước nóng.', 60000, 'images/americano.jpg'), 
(1, 'Cafe Latte', 'Espresso kết hợp sữa tươi.', 60000, 'images/latte.jpg'),
(1, 'Paris Bạc Xỉu', 'Bạc xỉu mang hương vị cà phê và sữa.', 70000, 'images/bac-xiu.jpg'),
(1, 'Cà Phê Đen', 'Cà phê đen đậm đà truyền thống.', 50000, 'images/ca-phe-den.jpg'),
(1, 'Cà Phê Sữa', 'Cà phê kết hợp sữa đặc.', 50000, 'images/ca-phe-sua.jpg'),

(2, 'Fleur Oolong Matcha', 'Trà Oolong kết hợp Matcha.', 85000, 'images/oolong-matcha.jpg'),
(2, 'Tangerine Jasmine Matcha', 'Trà Jasmine kết hợp hương quýt và Matcha.', 85000, 'images/tangerine.jpg'),
(2, 'Olive', 'Thức uống trà thanh mát.', 45000, 'images/olive.jpg'),
(2, 'Thai Young Mango Juice.', 'Nước ép xoài non Thái Lan' ,80000,'images/thaiyoungmangojuice.jpg'),
(2, 'Melon Coconut Juice.', 'Nước ép dưa hấu dừa', 80000, 'images/meloncoconutjuice.jpg'),

(3, 'Matcha Latte', 'Matcha kết hợp sữa tươi.', 80000, 'images/matcha-latte.jpg'),
(3, 'Matcha Corn Latte', 'Matcha kết hợp bắp và sữa tươi.', 90000, 'images/matcha-corn-latte.jpg'),
(3, 'Matcha Coco Cloud', 'Matcha kết hợp nước cốt dừa và kem tươi.', 85000, 'images/matcha-coco-cloud.jpg'),
(3, 'Matcha Sora', 'Matcha kết hợp kem tươi và hạt đậu đỏ.', 85000, 'images/matcha-sora.jpg'),
(3, 'Matcha Clear', 'Matcha kết hợp nước lọc và đá.', 70000, 'images/matcha-clear.jpg'),

(4, 'Ham & Cheese Pain Suisse', 'Bánh ngọt với nhân thịt xông khói và phô mai.', 80000, 'images/ham-cheese-pain-suisse.jpg'),
(4, 'Cafe Cinnamon Roll', 'Bánh ngọt cuộn quế với hương vị cà phê.', 70000, 'images/cafe-cinnamon-roll.jpg'),
(4, 'Egg Tart', 'Bánh trứng ngọt mềm mịn.', 52000, 'images/egg-tart.jpg'),
(4, 'Croissant Trứng Muối Lava', 'Bánh sừng bò với nhân trứng muối và sốt lava.', 83000, 'images/croissant-trung-muoi-lava.jpg'),
(4, 'Pizzant Pesto Bacon', 'Bánh pizza nướng cùng sốt pesto và thịt xông khói', 89000, 'images/Pizzant-Pesto-Bacon.jpg'),

(5, 'Velvet Heart Cake','Bánh Trái Tim Nhung Đỏ', 510000, 'images/Velvet Heart Cake.jpg'),
(5, 'Matcha Tiramisu', 'Bánh Tiramisu Trà Xanh', 640000, 'images/Matcha Tiramisu.jpg'),
(5, 'Chocolate Hazelnut Kiss','Bánh Socola Hạt Phỉ', 495000, 'images/Chocolate Hazelnut Kiss.jpg'),
(5, 'Signature Espresso Tiramisu', 'Bánh Tiramisu Cà Phê Đặc Biệt', 600000, 'images/Signature Espresso Tiramisu.jpg'),
(5, 'Berry Bliss Tiramisu','Bánh Tiramisu Quả Mọng', 640000, 'images/Berry Bliss Tiramisu.jpg');

-- Thêm Sizes
INSERT INTO sizes (id, name, extra_price) VALUES
(1, 'S', 0),
(2, 'M', 3000),
(3, 'L', 5000)
ON DUPLICATE KEY UPDATE name=VALUES(name), extra_price=VALUES(extra_price);

-- Thêm Toppings
INSERT INTO toppings (id, name, price) VALUES
(1, 'Trân châu', 5000),
(2, 'Kem cheese', 7000),
(3, 'Trân châu sợi', 5000)
ON DUPLICATE KEY UPDATE name=VALUES(name), price=VALUES(price);

-- Thêm Bàn (Cập nhật giá trị trạng thái chuẩn: available, occupied, reserved, cleaning)
INSERT INTO tables (id, name, area, status) VALUES
(1, 'Bàn 1', 'GF', 'available'),
(2, 'Bàn 2', 'GF', 'occupied'),
(3, 'Bàn 3', 'GF', 'occupied'),
(4, 'Bàn 4', 'GF', 'available'),
(5, 'Bàn 5', 'GF', 'reserved'),
(6, 'Bàn 6', 'GF', 'cleaning')
ON DUPLICATE KEY UPDATE name=VALUES(name), status=VALUES(status);

-- Thêm Khách Đặt Bàn Trước
INSERT INTO reservations (table_id, customer_name, customer_phone, booking_time, status) VALUES
(5, 'Nguyễn Văn B', '0901234567', NOW(), 'confirmed')
ON DUPLICATE KEY UPDATE customer_name=VALUES(customer_name);