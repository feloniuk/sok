<?php
// app/views/customer/orders/create.php - Сторінка створення нового замовлення для клієнта
$title = 'Створення нового замовлення';

// Підключення додаткових CSS
$extra_css = '
<style>
    .order-form-card {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: none;
    }
    
    .order-item {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        padding: 15px;
        margin-bottom: 15px;
        position: relative;
    }
    
    .product-image-preview {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
        margin-right: 10px;
    }
    
    .remove-item-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .product-stock {
        color: #6c757d;
        font-size: 0.85rem;
    }
    
    .product-card {
        transition: all 0.3s ease;
        overflow: hidden;
        height: 100%;
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 15px;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .product-card .card-img-top {
        height: 150px;
        object-fit: cover;
    }
</style>';

// Підключення додаткових JS
$extra_js = '
<script>
$(document).ready(function() {
    // Лічильник для індексації товарів
    let itemCounter = $(".order-item").length;
    
    // Додавання нового товару
    $("#addItemBtn").on("click", function() {
        const itemHtml = `
            <div class="order-item">
                <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn">
                    <i class="fas fa-times"></i>
                </button>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="product_${itemCounter}" class="form-label">Продукт <span class="text-danger">*</span></label>
                        <input type="text" class="form-control product-input" id="product_${itemCounter}" placeholder="Почніть вводити назву продукту..." required>
                        <input type="hidden" name="product_id[]" class="product-id" required>
                        <small class="product-stock"></small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="quantity_${itemCounter}" class="form-label">Кількість <span class="text-danger">*</span></label>
                        <input type="number" class="form-control item-quantity" id="quantity_${itemCounter}" name="quantity[]" min="1" value="1" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="price_${itemCounter}" class="form-label">Ціна <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control item-price" id="price_${itemCounter}" name="price[]" step="0.01" required readonly>
                            <span class="input-group-text">грн</span>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <strong>Сума: <span class="item-total">0.00 грн</span></strong>
                </div>
            </div>
        `;
        
        $("#itemsContainer").append(itemHtml);
        initAutocomplete($(`#product_${itemCounter}`));
        itemCounter++;
        updateTotalAmount();
    });
    
    // Видалення товару
    $(document).on("click", ".remove-item-btn", function() {
        $(this).closest(".order-item").remove();
        updateTotalAmount();
    });
    
    // Оновлення суми при зміні кількості або ціни
    $(document).on("input", ".item-quantity, .item-price", function() {
        updateItemTotal($(this).closest(".order-item"));
        updateTotalAmount();
    });
    
    // Вибір існуючого продукту зі списку
    $(".add-product-btn").on("click", function() {
        const productId = $(this).data("product-id");
        const productName = $(this).data("product-name");
        const productPrice = parseFloat($(this).data("product-price"));
        const productStock = parseInt($(this).data("product-stock"));
        const productImage = $(this).data("product-image");
        
        // Створення нового елемента замовлення
        const itemHtml = `
            <div class="order-item">
                <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn">
                    <i class="fas fa-times"></i>
                </button>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Продукт</label>
                        <div class="d-flex align-items-center">
                            <img src="${productImage}" class="product-image-preview">
                            <div>
                                <strong>${productName}</strong>
                                <input type="hidden" name="product_id[]" value="${productId}">
                            </div>
                        </div>
                        <small class="product-stock">
                            <i class="fas fa-box me-1"></i> В наявності: ${productStock} шт.
                        </small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="quantity_${itemCounter}" class="form-label">Кількість <span class="text-danger">*</span></label>
                        <input type="number" class="form-control item-quantity" id="quantity_${itemCounter}" name="quantity[]" min="1" max="${productStock}" value="1" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="price_${itemCounter}" class="form-label">Ціна</label>
                        <div class="input-group">
                            <input type="number" class="form-control item-price" id="price_${itemCounter}" name="price[]" value="${productPrice.toFixed(2)}" readonly>
                            <span class="input-group-text">грн</span>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <strong>Сума: <span class="item-total">${productPrice.toFixed(2)} грн</span></strong>
                </div>
            </div>
        `;
        
        $("#itemsContainer").append(itemHtml);
        itemCounter++;
        updateTotalAmount();
    });
    
    // Ініціалізація автозаповнення для продуктів
    function initAutocomplete(element) {
        element.autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "' . base_url("orders/products_json") . '",
                    dataType: "json",
                    data: { term: request.term },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function(event, ui) {
                const item = $(this).closest(".order-item");
                item.find(".product-id").val(ui.item.id);
                item.find(".item-price").val(ui.item.price);
                item.find(".product-stock").html(`<i class="fas fa-box me-1"></i> В наявності: ${ui.item.stock} шт.`);
                
                // Встановлення максимальної кількості
                item.find(".item-quantity").attr("max", ui.item.stock);
                
                updateItemTotal(item);
                updateTotalAmount();
                return true;
            }
        }).autocomplete("instance")._renderItem = function(ul, item) {
            return $("<li>")
                .append(`<div class="d-flex align-items-center">
                    <img src="${item.image}" class="product-image-preview">
                    <div>
                        <div>${item.label}</div>
                        <small class="text-muted">${item.price} грн</small>
                    </div>
                </div>`)
                .appendTo(ul);
        };
    }
    
    // Розрахунок вартості товару
    function updateItemTotal(item) {
        const quantity = parseFloat(item.find(".item-quantity").val()) || 0;
        const price = parseFloat(item.find(".item-price").val()) || 0;
        const total = quantity * price;
        
        item.find(".item-total").text(total.toFixed(2) + " грн");
    }
    
    // Розрахунок загальної вартості замовлення
    function updateTotalAmount() {
        let total = 0;
        
        $(".order-item").each(function() {
            const quantity = parseFloat($(this).find(".item-quantity").val()) || 0;
            const price = parseFloat($(this).find(".item-price").val()) || 0;
            total += quantity * price;
        });
        
        $("#orderTotal").text(total.toFixed(2) + " грн");
        $("#totalAmountInput").val(total.toFixed(2));
    }
    
    // Ініціалізація автозаповнення для існуючих полів
    $(".product-input").each(function() {
        initAutocomplete($(this));
    });
    
    // Запуск першого товару, якщо немає жодного і немає товарів у списку рекомендованих
    if ($(".order-item").length === 0 && $(".recommended-products .product-card").length === 0) {
        $("#addItemBtn").click();
    }
});
</script>';
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Головна</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('orders') ?>">Мої замовлення</a></li>
                <li class="breadcrumb-item active">Створення нового замовлення</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <form action="<?= base_url('orders/store') ?>" method="POST" class="needs-validation" novalidate>
            <?= csrf_field() ?>
            
            <div class="row">
                <!-- Інформація про замовлення -->
                <div class="col-md-4 mb-4">
                    <div class="card order-form-card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Інформація про замовлення</h5>
                        </div>
                        <div class="card-body">                            
                            <div class="mb-3">
                                <label for="shipping_address" class="form-label">Адреса доставки <span class="text-danger">*</span></label>
                                <textarea class="form-control <?= has_error('shipping_address') ? 'is-invalid' : '' ?>" id="shipping_address" name="shipping_address" rows="3" required><?= old('shipping_address') ?></textarea>
                                <?php if (has_error('shipping_address')): ?>
                                    <div class="invalid-feedback"><?= get_error('shipping_address') ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Спосіб оплати <span class="text-danger">*</span></label>
                                <select class="form-select <?= has_error('payment_method') ? 'is-invalid' : '' ?>" id="payment_method" name="payment_method" required>
                                    <option value="">Виберіть спосіб оплати</option>
                                    <option value="credit_card" <?= old('payment_method') == 'credit_card' ? 'selected' : '' ?>>Кредитна картка</option>
                                    <option value="bank_transfer" <?= old('payment_method') == 'bank_transfer' ? 'selected' : '' ?>>Банківський переказ</option>
                                    <option value="cash_on_delivery" <?= old('payment_method') == 'cash_on_delivery' ? 'selected' : '' ?>>Оплата при отриманні</option>
                                </select>
                                <?php if (has_error('payment_method')): ?>
                                    <div class="invalid-feedback"><?= get_error('payment_method') ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label">Примітки</label>
                                <textarea class="form-control <?= has_error('notes') ? 'is-invalid' : '' ?>" id="notes" name="notes" rows="3" placeholder="Додаткова інформація для замовлення (за бажанням)"><?= old('notes') ?></textarea>
                                <?php if (has_error('notes')): ?>
                                    <div class="invalid-feedback"><?= get_error('notes') ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="alert alert-info">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="fas fa-info-circle fa-2x"></i>
                                    </div>
                                    <div>
                                        <h5 class="alert-heading">Загальна сума замовлення:</h5>
                                        <p class="mb-0 fs-4 fw-bold" id="orderTotal">0.00 грн</p>
                                        <input type="hidden" name="total_amount" id="totalAmountInput" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Товари -->
                <div class="col-md-8 mb-4">
                    <div class="card order-form-card">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Товари у замовленні</h5>
                            <button type="button" id="addItemBtn" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-1"></i> Додати товар
                            </button>
                        </div>
                        <div class="card-body">
                            <?php if (has_error('products')): ?>
                                <div class="alert alert-danger">
                                    <?= get_error('products') ?>
                                </div>
                            <?php endif; ?>
                            
                            <div id="itemsContainer">
                                <?php
                                // Відтворення раніше доданих товарів (при помилці валідації)
                                if (isset($_POST['product_id']) && is_array($_POST['product_id'])) {
                                    foreach ($_POST['product_id'] as $index => $productId) {
                                        $quantity = $_POST['quantity'][$index] ?? 1;
                                        $price = $_POST['price'][$index] ?? 0;
                                        // Тут можна було б отримати деталі продукту з бази даних для відображення
                                    }
                                }
                                
                                // Якщо передано product_id через GET (додавання з каталогу)
                                if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
                                    $productModel = new Product();
                                    $product = $productModel->getById($_GET['product_id']);
                                    
                                    if ($product && $product['is_active'] && $product['stock_quantity'] > 0) {
                                        // Тут буде код для додавання товару в замовлення, але він буде виконуватися через JavaScript
                                    }
                                }
                                ?>
                            </div>
                            
                            <?php 
                            // Якщо немає товарів в контейнері, показуємо повідомлення
                            if (empty($_POST['product_id']) && !isset($_GET['product_id'])): 
                            ?>
                                <div class="text-center py-4" id="noItemsMessage">
                                    <p class="text-muted">Додайте товари до вашого замовлення</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Рекомендовані товари -->
                    <?php 
                    // Відображення рекомендованих товарів
                    $productModel = new Product();
                    $recommendedProducts = $productModel->getFeatured(4);
                    
                    if (!empty($recommendedProducts)):
                    ?>
                    <div class="card order-form-card mt-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Рекомендовані товари</h5>
                        </div>
                        <div class="card-body recommended-products">
                            <div class="row">
                                <?php foreach ($recommendedProducts as $product): ?>
                                    <?php if ($product['is_active'] && $product['stock_quantity'] > 0): ?>
                                        <div class="col-md-6">
                                            <div class="product-card">
                                                <div class="row g-0">
                                                    <div class="col-md-4">
                                                        <img src="<?= $product['image'] ? upload_url($product['image']) : asset_url('images/no-image.jpg') ?>" 
                                                            class="img-fluid rounded-start" alt="<?= $product['name'] ?>" 
                                                            style="height: 100%; object-fit: cover;">
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="card-body">
                                                            <h5 class="card-title"><?= $product['name'] ?></h5>
                                                            <p class="card-text">
                                                                <span class="fw-bold text-primary"><?= number_format($product['price'], 2) ?> грн</span>
                                                            </p>
                                                            <p class="card-text">
                                                                <small class="text-muted">В наявності: <?= $product['stock_quantity'] ?> шт.</small>
                                                            </p>
                                                            <button type="button" class="btn btn-sm btn-outline-success add-product-btn"
                                                                    data-product-id="<?= $product['id'] ?>"
                                                                    data-product-name="<?= $product['name'] ?>"
                                                                    data-product-price="<?= $product['price'] ?>"
                                                                    data-product-stock="<?= $product['stock_quantity'] ?>"
                                                                    data-product-image="<?= $product['image'] ? upload_url($product['image']) : asset_url('images/no-image.jpg') ?>">
                                                                <i class="fas fa-plus me-1"></i> Додати до замовлення
                                                            </button>
                                                        </div>
                                                    </div>
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
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('products') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Повернутися до каталогу
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i> Оформити замовлення
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>