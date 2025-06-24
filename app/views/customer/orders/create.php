<?php
// app/views/customer/orders/create.php - Сторінка створення замовлення
$title = 'Оформлення замовлення';

// Підключення додаткових CSS
$extra_css = '
<style>
    .order-form-card {
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .product-item {
        display: flex;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .product-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        margin-right: 15px;
        border-radius: 0.25rem;
    }
    
    .product-details {
        flex-grow: 1;
    }
    
    .quantity-control {
        display: flex;
        align-items: center;
    }
    
    .quantity-control input {
        width: 60px;
        text-align: center;
        margin: 0 10px;
    }
    
    .remove-product {
        color: #dc3545;
        cursor: pointer;
    }
    
    .total-price {
        font-weight: bold;
        color: #007bff;
    }
</style>';

// Підключення додаткових JS
$extra_js = '
<script>
$(document).ready(function() {
    // Додавання товару з каталогу
    $(".add-product-btn").on("click", function() {
        const productId = $(this).data("product-id");
        const quantity = 1;
        
        $.ajax({
            url: "' . base_url('orders/cart') . '",
            type: "POST",
            data: { 
                action: "add", 
                product_id: productId,
                quantity: quantity
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    // Оновити кошик
                    loadCartItems();
                    // Показати повідомлення
                    alert(response.message);
                }
            },
            error: function(xhr) {
                alert("Помилка: " + (xhr.responseJSON ? xhr.responseJSON.error : "Неможливо додати товар"));
            }
        });
    });
    
    // Видалення товару з кошика
    $(document).on("click", ".remove-product", function() {
        const productId = $(this).data("product-id");
        
        $.ajax({
            url: "' . base_url('orders/cart') . '",
            type: "POST",
            data: { 
                action: "remove", 
                product_id: productId 
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    // Оновити кошик
                    loadCartItems();
                }
            }
        });
    });
    
    // Зміна кількості товару
    $(document).on("change", ".quantity-input", function() {
        const productId = $(this).data("product-id");
        const quantity = parseInt($(this).val());
        
        if (quantity > 0) {
            $.ajax({
                url: "' . base_url('orders/cart') . '",
                type: "POST",
                data: { 
                    action: "update", 
                    product_id: productId,
                    quantity: quantity
                },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // Оновити кошик
                        loadCartItems();
                    }
                }
            });
        }
    });
    
    // Збільшення кількості
    $(document).on("click", ".increase-quantity", function() {
        const input = $(this).siblings(".quantity-input");
        const currentValue = parseInt(input.val());
        const maxValue = parseInt(input.attr("max"));
        
        if (currentValue < maxValue) {
            input.val(currentValue + 1).trigger("change");
        }
    });
    
    // Зменшення кількості
    $(document).on("click", ".decrease-quantity", function() {
        const input = $(this).siblings(".quantity-input");
        const currentValue = parseInt(input.val());
        
        if (currentValue > 1) {
            input.val(currentValue - 1).trigger("change");
        }
    });
    
    // Завантаження вмісту кошика
    function loadCartItems() {
        $.ajax({
            url: "' . base_url('orders/cart') . '",
            type: "GET",
            data: { action: "get" },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    // Оновлення вмісту кошика
                    renderCartItems(response.items);
                    // Оновлення загальної суми
                    $("#totalPrice").text(response.cart_total.toFixed(2) + " грн");
                    // Активація кнопки оформлення
                    $("#submitOrderBtn").prop("disabled", response.cart_count === 0);
                    
                    // Показ/приховування порожнього повідомлення
                    if (Object.keys(response.items).length === 0) {
                        $("#emptyCartMessage").show();
                    } else {
                        $("#emptyCartMessage").hide();
                    }
                }
            }
        });
    }
    
    // Рендеринг товарів у кошику
    function renderCartItems(items) {
        const container = $("#orderItemsContainer");
        container.empty();
        
        $.each(items, function(id, item) {
            const itemHtml = `
                <div class="product-item" data-product-id="${item.id}">
                    <img src="${item.image ? "' . upload_url('') . '" + item.image : "' . asset_url('images/no-image.jpg') . '"}" 
                         alt="${item.name}" class="product-image">
                    <div class="product-details">
                        <h6 class="mb-1">${item.name}</h6>
                        <p class="text-muted mb-1">${parseFloat(item.price).toFixed(2)} грн</p>
                    </div>
                    <div class="quantity-control">
                        <button type="button" class="btn btn-sm btn-outline-secondary decrease-quantity">-</button>
                        <input type="number" 
                               class="form-control form-control-sm quantity-input" 
                               value="${item.quantity}" 
                               min="1" 
                               max="${item.stock_quantity}"
                               data-product-id="${item.id}">
                        <button type="button" class="btn btn-sm btn-outline-secondary increase-quantity">+</button>
                    </div>
                    <div class="ms-3">
                        <span class="item-total">${(item.price * item.quantity).toFixed(2)} грн</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-3 remove-product" data-product-id="${item.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            container.append(itemHtml);
        });
    }
    
    // Ініціалізація кошика при завантаженні сторінки
    loadCartItems();
});
</script>';

// Перевірка наявності товарів у кошику
$hasCartItems = !empty($cart_items);
?>

<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Головна</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('products') ?>">Каталог</a></li>
                <li class="breadcrumb-item active">Створення замовлення</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card order-form-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-shopping-cart me-2"></i> Ваше замовлення
                </h5>
            </div>
            <div class="card-body">
                <div id="orderItemsContainer"></div>
                
                <div class="alert alert-info text-center" id="emptyCartMessage" <?= $hasCartItems ? 'style="display:none"' : '' ?>>
                    <i class="fas fa-shopping-basket me-2"></i> Додайте товари до кошика
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Загальна сума: <span id="totalPrice" class="text-primary">0.00 грн</span></h4>
                    <a href="<?= base_url('products') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-shopping-basket me-1"></i> Продовжити покупки
                    </a>
                </div>
            </div>
        </div>

        <?php if (!empty($recommendedProducts)): ?>
        <div class="card mt-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Рекомендовані товари</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($recommendedProducts as $recProduct): ?>
                        <?php if ($recProduct['is_active'] && $recProduct['stock_quantity'] > 0): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <img src="<?= $recProduct['image'] ? upload_url($recProduct['image']) : asset_url('images/no-image.jpg') ?>" 
                                         class="card-img-top" alt="<?= $recProduct['name'] ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= $recProduct['name'] ?></h5>
                                        <p class="card-text">
                                            <span class="fw-bold"><?= number_format($recProduct['price'], 2) ?> грн</span>
                                        </p>
                                        <button class="btn btn-sm btn-success add-product-btn w-100"
                                                data-product-id="<?= $recProduct['id'] ?>">
                                            <i class="fas fa-cart-plus me-1"></i> Додати
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-md-4">
        <form id="orderForm" action="<?= base_url('orders/store') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="card order-form-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-shipping-fast me-2"></i> Деталі замовлення
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="shipping_address" class="form-label">Адреса доставки</label>
                        <textarea class="form-control" id="shipping_address" 
                                  name="shipping_address" 
                                  rows="3" 
                                  placeholder="Введіть повну адресу доставки" 
                                  required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Спосіб оплати</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="">Оберіть спосіб оплати</option>
                            <option value="cash_on_delivery">Оплата при отриманні</option>
                            <option value="bank_transfer">Банківський переказ</option>
                            <option value="credit_card">Онлайн-оплата</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Додаткові коментарі</label>
                        <textarea class="form-control" id="notes" 
                                  name="notes" 
                                  rows="3" 
                                  placeholder="Додаткова інформація або побажання"></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success w-100" id="submitOrderBtn" disabled>
                        <i class="fas fa-check me-2"></i> Оформити замовлення
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>