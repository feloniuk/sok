<?php
// app/views/orders/create.php - Сторінка створення нового замовлення
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
                            <input type="number" class="form-control item-price" id="price_${itemCounter}" name="price[]" step="0.01" required>
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
    
    // Ініціалізація автозаповнення для продуктів
    function initAutocomplete(element) {
        element.autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "' . base_url('orders/products_json') . '",
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
    
    // Запуск першого товару, якщо немає жодного
    if ($(".order-item").length === 0) {
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
                <li class="breadcrumb-item"><a href="<?= base_url('orders') ?>">Замовлення</a></li>
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
                            <?php if (has_role(['admin', 'sales_manager']) && !empty($customers)): ?>
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
                            <?php endif; ?>
                            
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
                                <textarea class="form-control <?= has_error('notes') ? 'is-invalid' : '' ?>" id="notes" name="notes" rows="3"><?= old('notes') ?></textarea>
                                <?php if (has_error('notes')): ?>
                                    <div class="invalid-feedback"><?= get_error('notes') ?></div>
                                <?php endif; ?>
                                <div class="form-text">Додаткова інформація для замовлення (необов'язково)</div>
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
                            <h5 class="mb-0">Товари</h5>
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
                                ?>
                            </div>
                            
                            <?php if (empty($_POST['product_id'])): ?>
                                <div class="text-center py-4" id="noItemsMessage">
                                    <p class="text-muted">Натисніть "Додати товар", щоб додати товари до замовлення</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('orders') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Скасувати
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i> Створити замовлення
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>