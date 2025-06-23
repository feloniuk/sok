<?php
// app/views/admin/orders/create.php - Form for creating new orders
$title = 'Створення нового замовлення';

// Additional CSS styles
$extra_css = '
<style>
    .form-card {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: none;
        margin-bottom: 20px;
    }
    
    .product-item {
        display: flex;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .product-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
    }
    
    .product-search-results {
        max-height: 300px;
        overflow-y: auto;
        display: none;
        position: absolute;
        width: 100%;
        z-index: 1000;
        background: white;
        border: 1px solid #ddd;
        border-radius: 0.25rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .cart-item {
        padding: 10px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .cart-item:last-child {
        border-bottom: none;
    }
</style>';

// Additional JS scripts
$extra_js = '
<script>
$(document).ready(function() {
    // Product search with autocomplete
    $("#product_search").on("keyup", function() {
        const keyword = $(this).val();
        
        if (keyword.length < 2) {
            $("#searchResults").hide();
            return;
        }
        
        $.ajax({
            url: "' . base_url('orders/products_json') . '",
            type: "GET",
            data: { term: keyword },
            dataType: "json",
            success: function(data) {
                let html = "";
                
                if (data.length > 0) {
                    data.forEach(function(product) {
                        html += `
                            <div class="product-item" data-id="${product.id}" data-name="${product.name}" 
                                 data-price="${product.price}" data-stock="${product.stock}" data-image="${product.image}">
                                <img src="${product.image}" class="product-image me-2" alt="${product.name}">
                                <div class="flex-grow-1">
                                    <div><strong>${product.name}</strong></div>
                                    <div>Ціна: ${formatCurrency(product.price)} | Залишок: ${product.stock} шт.</div>
                                </div>
                            </div>
                        `;
                    });
                    
                    $("#searchResults").html(html).show();
                } else {
                    $("#searchResults").html("<div class=\'p-3\'>Продукти не знайдено</div>").show();
                }
            }
        });
    });
    
    // Add product to cart when clicked
    $(document).on("click", ".product-item", function() {
        const productId = $(this).data("id");
        const productName = $(this).data("name");
        const productPrice = $(this).data("price");
        const productStock = $(this).data("stock");
        const productImage = $(this).data("image");
        
        // Check if the product is already in the cart
        if ($("#cart-item-" + productId).length > 0) {
            // Update quantity
            const currentQty = parseInt($("#qty-" + productId).val());
            $("#qty-" + productId).val(currentQty + 1).trigger("change");
        } else {
            // Add new product to cart
            addToCart(productId, productName, productPrice, 1, productStock, productImage);
        }
        
        // Clear search
        $("#product_search").val("");
        $("#searchResults").hide();
    });
    
    // Update cart when quantity changes
    $(document).on("change", ".item-qty", function() {
        updateCart();
    });
    
    // Remove item from cart
    $(document).on("click", ".remove-item", function() {
        const productId = $(this).data("id");
        const containerId = $(this).data("container");
        $("#cart-item-" + productId + "-" + containerId).remove();
        updateCart();
    });
    
    // Format currency
    function formatCurrency(value) {
        return parseFloat(value).toFixed(2) + " грн.";
    }
    
    // Add product to cart
    function addToCart(id, name, price, qty, stock, image, containerId = null, volume = 1) {
    const volumeText = volume !== 1 ? ` (${volume} л)` : \'\';
    const html = `
        <div class="cart-item" id="cart-item-${id}-${containerId || \'base\'}">
            <input type="hidden" name="product_id[]" value="${id}">
            <input type="hidden" name="container_id[]" value="${containerId || \'\'}">
            <input type="hidden" name="volume[]" value="${volume}">
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <img src="${image}" class="product-image me-2" alt="${name}">
                        <div>
                            <div><strong>${name}${volumeText}</strong></div>
                            <div>${formatCurrency(price)}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control item-qty"
                               id="qty-${id}-${containerId || \'base\'}"
                               name="quantity[]"
                               value="${qty}" min="1" max="${stock}">
                        <input type="hidden" name="price[]" value="${price}">
                    </div>
                </div>
                <div class="col-md-2 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-item"
                            data-id="${id}" data-container="${containerId || \'base\'}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    $("#cart-items").append(html);
    updateCart();
}

    // Update cart totals
    function updateCart() {
        let totalQty = 0;
        let totalAmount = 0;
        let totalVolume = 0;
        
        $(".cart-item").each(function() {
            const qty = parseInt($(this).find(".item-qty").val()) || 0;
            const price = parseFloat($(this).find("input[name=\'price[]\']").val()) || 0;
            const volume = parseFloat($(this).find("input[name=\'volume[]\']").val()) || 1;
            
            totalQty += qty;
            totalAmount += qty * price;
            totalVolume += qty * volume;
        });
        
        $("#total-quantity").text(totalQty);
        $("#total-amount").text(formatCurrency(totalAmount));
        $("#total-volume").text(totalVolume.toFixed(2) + " л"); // Добавляем отображение общего объема
        
        // Обновление скрытого поля для общей суммы
        $("#total_amount").val(totalAmount);
        
        // Показать/скрыть содержимое корзины
        if (totalQty > 0) {
            $("#cart-content").show();
            $("#cart-empty").hide();
        } else {
            $("#cart-content").hide();
            $("#cart-empty").show();
        }
}
    
    // Initialize cart on page load
    updateCart();
    
    // Handle form submission validation
    $("#orderForm").on("submit", function(e) {
        if ($(".item-qty").length === 0) {
            e.preventDefault();
            alert("Додайте хоча б один товар до замовлення");
            return false;
        }
        
        // For admin/sales manager: validate customer selection
        if ($("#customer_id").length > 0 && $("#customer_id").val() === "") {
            e.preventDefault();
            alert("Виберіть клієнта");
            return false;
        }
        
        // Validate address
        if ($("#shipping_address").val().trim() === "") {
            e.preventDefault();
            alert("Введіть адресу доставки");
            return false;
        }
        
        return true;
    });
    
    // Hide search results when clicking outside
    $(document).on("click", function(e) {
        if (!$(e.target).closest("#product_search, #searchResults").length) {
            $("#searchResults").hide();
        }
    });
});
</script>';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Головна</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('orders') ?>">Замовлення</a></li>
                <li class="breadcrumb-item active">Створення замовлення</li>
            </ol>
        </nav>
    </div>
</div>

<form id="orderForm" action="<?= base_url('orders/store') ?>" method="POST" class="needs-validation" novalidate>
    <?= csrf_field() ?>
    
    <div class="row">
        <!-- Информация о заказе -->
        <div class="col-md-4 mb-4">
            <div class="card form-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i> Інформація про замовлення
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (has_role(['admin', 'sales_manager'])): ?>
                    <!-- Выбор клиента (для админа и менеджера продаж) -->
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Клієнт <span class="text-danger">*</span></label>
                        <select class="form-select <?= has_error('customer_id') ? 'is-invalid' : '' ?>" id="customer_id" name="customer_id" required>
                            <option value="">Виберіть клієнта</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= $customer['id'] ?>" <?= old('customer_id') == $customer['id'] ? 'selected' : '' ?>>
                                    <?= $customer['first_name'] . ' ' . $customer['last_name'] ?> (<?= $customer['email'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (has_error('customer_id')): ?>
                            <div class="invalid-feedback"><?= get_error('customer_id') ?></div>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <!-- Для клиента скрытое поле с его ID -->
                    <input type="hidden" name="customer_id" value="<?= get_current_user_id() ?>">
                    <?php endif; ?>
                    
                    <!-- Адрес доставки -->
                    <div class="mb-3">
                        <label for="shipping_address" class="form-label">Адреса доставки <span class="text-danger">*</span></label>
                        <textarea class="form-control <?= has_error('shipping_address') ? 'is-invalid' : '' ?>" id="shipping_address" name="shipping_address" rows="3" required><?= old('shipping_address') ?></textarea>
                        <?php if (has_error('shipping_address')): ?>
                            <div class="invalid-feedback"><?= get_error('shipping_address') ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Способ оплаты -->
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Спосіб оплати <span class="text-danger">*</span></label>
                        <select class="form-select <?= has_error('payment_method') ? 'is-invalid' : '' ?>" id="payment_method" name="payment_method" required>
                            <option value="credit_card" <?= old('payment_method') == 'credit_card' ? 'selected' : '' ?>>Кредитна карта</option>
                            <option value="bank_transfer" <?= old('payment_method') == 'bank_transfer' ? 'selected' : '' ?>>Банківський переказ</option>
                            <option value="cash_on_delivery" <?= old('payment_method') == 'cash_on_delivery' ? 'selected' : '' ?>>Накладений платіж</option>
                        </select>
                        <?php if (has_error('payment_method')): ?>
                            <div class="invalid-feedback"><?= get_error('payment_method') ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Примечание к заказу -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">Примітки до замовлення</label>
                        <textarea class="form-control <?= has_error('notes') ? 'is-invalid' : '' ?>" id="notes" name="notes" rows="3"><?= old('notes') ?></textarea>
                        <?php if (has_error('notes')): ?>
                            <div class="invalid-feedback"><?= get_error('notes') ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Общая сумма заказа (скрытое поле) -->
                    <input type="hidden" id="total_amount" name="total_amount" value="0">
                </div>
            </div>
        </div>
        
        <!-- Добавление товаров -->
        <div class="col-md-8 mb-4">
            <div class="card form-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-cart me-2"></i> Товари в замовленні
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Поиск товаров -->
                    <div class="mb-4">
                        <label for="product_search" class="form-label">Пошук товарів</label>
                        <div class="position-relative">
                            <input type="text" class="form-control" id="product_search" placeholder="Введіть назву товару...">
                            <div id="searchResults" class="product-search-results"></div>
                        </div>
                    </div>
                    
                    <!-- Список товаров в корзине -->
                    <div class="border rounded p-3 mb-3">
                        <h6 class="mb-3">Товари в кошику</h6>
                        
                        <div id="cart-empty" class="text-center py-4">
                            <i class="fas fa-shopping-basket fa-2x text-muted mb-2"></i>
                            <p class="text-muted">Кошик порожній. Додайте товари, використовуючи поле пошуку вище.</p>
                        </div>
                        
                        <div id="cart-content">
                            <!-- Обновление отображения товаров в корзине (admin/orders/create.php) -->
                            <div id="cart-items">
                                <?php if (!empty($cart_items)): ?>
                                    <?php foreach ($cart_items as $item): ?>
                                        <div class="cart-item" id="cart-item-<?= $item['id'] ?>-<?= $item['container_id'] ?? 'base' ?>">
                                            <input type="hidden" name="product_id[]" value="<?= $item['id'] ?>">
                                            <input type="hidden" name="container_id[]" value="<?= $item['container_id'] ?? '' ?>">
                                            <input type="hidden" name="volume[]" value="<?= $item['volume'] ?? 1 ?>">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?= $item['image'] ? upload_url($item['image']) : asset_url('images/no-image.jpg') ?>" 
                                                            class="product-image me-2" alt="<?= $item['name'] ?>">
                                                        <div>
                                                            <div>
                                                                <strong><?= $item['name'] ?></strong>
                                                                <?php if (($item['volume'] ?? 1) != 1): ?>
                                                                    <span class="badge bg-info ms-2"><?= number_format($item['volume'], 2) ?> л</span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div>
                                                                <?= number_format($item['price'], 2) ?> грн.
                                                                <small class="text-muted">
                                                                    (<?= number_format($item['price'] / ($item['volume'] ?? 1), 2) ?> грн/л)
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control item-qty" 
                                                            id="qty-<?= $item['id'] ?>-<?= $item['container_id'] ?? 'base' ?>" 
                                                            name="quantity[]" 
                                                            value="<?= $item['quantity'] ?>" min="1" max="999">
                                                        <input type="hidden" name="price[]" value="<?= $item['price'] ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-2 text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-item" 
                                                            data-id="<?= $item['id'] ?>" 
                                                            data-container="<?= $item['container_id'] ?? 'base' ?>">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <hr>

                            <!-- Итоги с объемом -->
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>Загальна кількість:</strong> <span id="total-quantity">0</span> шт.
                                </div>
                                <div>
                                    <strong>Загальний об'єм:</strong> <span id="total-volume">0.00</span> л
                                </div>
                                <div>
                                    <strong>Сума замовлення:</strong> <span id="total-amount">0.00 грн.</span>
                                </div>
                            </div>
                        </div>

                    <!-- Кнопки -->
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('orders') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Назад до списку
                        </a>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle me-1"></i> Оформити замовлення
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>