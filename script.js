let cart = [];
let currentProduct = null;
let currentQuantity = 1;

document.addEventListener("DOMContentLoaded", () => {
    fetchProducts();
});

function fetchProducts(category = 'all', search = '') {
    let url = `get_products.php?category=${encodeURIComponent(category)}&search=${encodeURIComponent(search)}`;
    fetch(url)
        .then(res => res.json())
        .then(data => {
            renderProducts(data);
        })
        .catch(err => console.error("Lỗi lấy dữ liệu:", err));
}

function renderProducts(products) {
    const list = document.getElementById("product-list");
    list.innerHTML = "";
    if (!products || products.length === 0) {
        list.innerHTML = "<p style='grid-column: 1/-1; text-align: center; color: #888; padding: 40px;'>Không tìm thấy sản phẩm nào trong danh mục này.</p>";
        return;
    }

    products.forEach(p => {
        const card = document.createElement("div");
        card.className = "product-card";
        card.innerHTML = `
            <img src="${p.image}" alt="${p.name}" onerror="this.src='https://via.placeholder.com/200?text=Bakes+Bakery'">
            <div class="product-info">
                <span class="product-category">${p.category_name || ''}</span>
                <h3>${p.name}</h3>
                <p class="product-desc">${p.description || ''}</p>
                <div class="product-bottom">
                    <span class="product-price">${Number(p.price).toLocaleString('vi-VN')}đ</span>
                    <button onclick='openProduct(${JSON.stringify(p)})'>Đặt món</button>
                </div>
            </div>
        `;
        list.appendChild(card);
    });
}

function filterCategory(catName, btn) {
    document.querySelectorAll(".category-btn").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");
    fetchProducts(catName, document.getElementById("search").value);
}

function searchProduct() {
    const val = document.getElementById("search").value;
    const activeBtn = document.querySelector(".category-btn.active");
    const activeCat = activeBtn ? activeBtn.innerText.trim() : 'all';
    fetchProducts(activeCat === 'Tất cả' ? 'all' : activeCat, val);
}

// MỞ MODAL XEM CHI TIẾT SẢN PHẨM
function openProduct(product) {
    currentProduct = product;
    currentQuantity = 1;

    document.getElementById("detail-image").src = product.image;
    document.getElementById("detail-category").innerText = product.category_name || '';
    document.getElementById("detail-name").innerText = product.name;
    document.getElementById("detail-description").innerText = product.description || '';
    document.getElementById("detail-price").innerText = Number(product.price).toLocaleString('vi-VN') + "đ";
    document.getElementById("quantity").innerText = 1;

    // Reset options
    document.querySelectorAll("input[name='size']")[0].checked = true;
    document.querySelectorAll(".topping-cb").forEach(cb => cb.checked = false);

    // KIỂM TRA NẾU LÀ BÁNH THÌ ẨN KHỐI SIZE VÀ TOPPING
    const optionsBlock = document.querySelector(".options");
    const catName = (product.category_name || '').toLowerCase();
    
    if (catName.includes('bakes') || catName.includes('cake') || catName.includes('bánh')) {
        if (optionsBlock) optionsBlock.style.display = "none";
    } else {
        if (optionsBlock) optionsBlock.style.display = "block";
    }

    document.getElementById("product-modal").style.display = "flex";
}

function closeProduct() {
    document.getElementById("product-modal").style.display = "none";
}

function changeQuantity(amount) {
    currentQuantity += amount;
    if (currentQuantity < 1) currentQuantity = 1;
    document.getElementById("quantity").innerText = currentQuantity;
}

function addToCart() {
    const catName = (currentProduct.category_name || '').toLowerCase();
    const isCake = catName.includes('bakes') || catName.includes('cake') || catName.includes('bánh');

    let selectedSize = 'S';
    let extraSizePrice = 0;
    let selectedToppings = [];
    let extraToppingPrice = 0;

    // Chỉ tính Size và Topping nếu là Nước uống
    if (!isCake) {
        selectedSize = document.querySelector("input[name='size']:checked").value;
        if (selectedSize === 'M') extraSizePrice = 3000;
        if (selectedSize === 'L') extraSizePrice = 5000;

        document.querySelectorAll(".topping-cb:checked").forEach(cb => {
            selectedToppings.push(cb.value);
            extraToppingPrice += Number(cb.dataset.price);
        });
    }

    const itemPrice = Number(currentProduct.price) + extraSizePrice + extraToppingPrice;

    cart.push({
        productId: currentProduct.id,
        name: currentProduct.name,
        isCake: isCake,
        size: isCake ? '' : selectedSize,
        toppings: selectedToppings,
        price: itemPrice,
        quantity: currentQuantity
    });

    updateCartBadge();
    closeProduct();
}

function updateCartBadge() {
    const totalCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    document.getElementById("cart-count").innerText = totalCount;
}

function openCart() {
    renderCartItems();
    document.getElementById("cart-modal").style.display = "flex";
    toggleQRSection();
}

function closeCart() {
    document.getElementById("cart-modal").style.display = "none";
}

function renderCartItems() {
    const container = document.getElementById("cart-list");
    container.innerHTML = "";

    let total = 0;
    if (cart.length === 0) {
        container.innerHTML = "<p style='text-align:center; padding:20px; color:#888;'>Giỏ hàng đang trống</p>";
    } else {
        cart.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;

            const div = document.createElement("div");
            div.className = "cart-item";
            div.style = "display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #eee;";
            
            let noteText = '';
            if (!item.isCake) {
                noteText = `<small>(Size ${item.size})</small><br><small style="color:#666;">${item.toppings.length > 0 ? 'Topping: ' + item.toppings.join(', ') : 'Không topping'}</small><br>`;
            }

            div.innerHTML = `
                <div>
                    <strong>${item.name}</strong> ${noteText}
                    <small style="color:#e74c3c;">${Number(item.price).toLocaleString('vi-VN')}đ x ${item.quantity}</small>
                </div>
                <div>
                    <strong style="margin-right:10px;">${Number(itemTotal).toLocaleString('vi-VN')}đ</strong>
                    <button onclick="removeItem(${index})" style="background:#ff7675; color:white; border:none; border-radius:4px; padding:4px 8px; cursor:pointer;">Xóa</button>
                </div>
            `;
            container.appendChild(div);
        });
    }

    document.getElementById("cart-total").innerText = Number(total).toLocaleString('vi-VN') + "đ";
    toggleQRSection();
}

function removeItem(index) {
    cart.splice(index, 1);
    updateCartBadge();
    renderCartItems();
}

function toggleQRSection() {
    const payMethod = document.getElementById("pay-method").value;
    const qrSection = document.getElementById("qr-section");

    if (payMethod === 'BANK' && cart.length > 0) {
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const memo = "BAKES " + Math.floor(1000 + Math.random() * 9000);

        const qrUrl = `https://img.vietqr.io/image/MB-0898553213-compact2.png?amount=${total}&addInfo=${encodeURIComponent(memo)}&accountName=Duong%20Nguyen%20Phong`;

        document.getElementById("qr-code-img").src = qrUrl;
        document.getElementById("qr-memo").innerText = memo;
        qrSection.style.display = "block";
    } else {
        qrSection.style.display = "none";
    }
}

function checkout() {
    if (cart.length === 0) {
        alert("Giỏ hàng của bạn đang trống!");
        return;
    }

    const name = document.getElementById("cust-name").value.trim();
    const phone = document.getElementById("cust-phone").value.trim();
    const address = document.getElementById("cust-address").value.trim();

    if (!name || !phone || !address) {
        alert("Vui lòng điền đầy đủ thông tin giao hàng!");
        return;
    }

    const totalMoney = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

    const orderData = {
        customer_name: name,
        customer_phone: phone,
        customer_address: address,
        total_money: totalMoney,
        cart: cart
    };

    fetch('save_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Đặt hàng thành công! Mã đơn hàng của bạn là #" + data.order_id);
            cart = [];
            updateCartBadge();
            closeCart();
            document.getElementById("cust-name").value = "";
            document.getElementById("cust-phone").value = "";
            document.getElementById("cust-address").value = "";
        } else {
            alert("Có lỗi xảy ra: " + (data.message || "Không thể đặt hàng"));
        }
    })
    .catch(err => console.error("Lỗi đặt hàng:", err));
}