<?php
// app/views/orders/create.php - Обновленная форма создания заказа с полной поддержкой контейнеров
$title = 'Створення нового замовлення';

// Подключение дополнительных CSS
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
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .order-item:hover {
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0,123,255,0.15);
    }
    
    .product-image-preview {
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
        gap: 10px;
    }
    
    .quantity-control input {
        width: 80px;
        text-align: center;
    }
    
    .remove-product {
        position: absolute;
        top: 10px;
        right: 10px;
        color: #dc3545;
        cursor: pointer;
        background: white;
        border: 1px solid #dc3545;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .remove-product:hover {
        background: #dc3545;
        color: white;
    }
    
    .total-price {
        font-weight: bold;
        color: #007bff;
    }
    
    .container-selector {
        border: 2px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 15px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .container-selector:hover {
        border-color: #007bff;
        background-color: #f8f9ff;
        box-shadow: 0 2px 8px rgba(0,123,255,0.15);
    }
    
    .container-selector.selected {
        border-color: #007bff;
        background-color: #e7f3ff;
        box-shadow: 0 4px 12px rgba(0,123,255,0.25);
    }
    
    .container-selector.out-of-stock {
        opacity: 0.6;
        cursor: not-allowed;
        background-color: #f8f9fa;
    }
    
    .container-selector.out-of-stock:hover {
        border-color: #e9ecef;
        background-color: #f8f9fa;
        box-shadow: none;
    }
    
    .volume-badge {
        position: absolute;
        top: -8px;
        left: 15px;
        background: #007bff;
        color: white;
        padding: 5px 15px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: bold;
    }
    
    .container-selector.out-of-stock .volume-badge {
        background: #6c757d;
    }
    
    .price-per-liter {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 5px;
    }
    
    .best-value-badge {
        position: absolute;
        top: -8px;
        right: 15px;
        background: #28a745;
        color: white;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: bold;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .product-search-card {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .product-card-mini {
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 10px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .product-card-mini:hover {
        border-color: #007bff;
        background-color: #f8f9ff;
    }
    
    .product-card-mini img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
    }
    
    .order-summary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 0.5rem;
        padding: 20px;
        margin-top: 20px;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 1px solid rgba(255,255,255,0.2);
    }
    
    .summary-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
        font-weight: bold;
        font-size: 1.2rem;
    }
</style>';

// Подключение дополнительных JS
$extra_js = '
<script>
$(document).ready(function() {
    let itemCounter = 0;
    let selectedContainers = new Set();
    
    // Загрузка продуктов при открытии модального окна
    $("#addItemBtn").on("click", function() {
        loadProducts();
        $("#productSelectionModal").modal("show");
    });
    
    // Поиск продуктов
    $("#productSearch").on("input", function() {
        const keyword = $(this).val();
        if (keyword.length >= 2) {
            searchProducts(keyword);
        } else if (keyword.length === 0) {
            loadProducts();
        }
    });
    
    // Выбор продукта
    $(document).on("click", ".select-product", function() {
        const productId = $(this).data("product-id");
        const productName = $(this).data("product-name");
        const productImage = $(this).data("product-image");
        const containers = $(this).data("containers");
        
        $("#selectedProductId").val(productId);
        $("#selectedProductName").val(productName);
        $("#selectedProductImage").val(productImage);
        
        showContainerSelection(productName, containers);
    });
    
    // Выбор контейнера
    $(document).on("click", ".container-selector", function() {
        if ($(this).hasClass("out-of-stock")) {
            return;
        }
        
        $(".container-selector").removeClass("selected");
        $(this).addClass("selected");
        
        const containerId = $(this).data("container-id");
        const price = $(this).data("price");
        const volume = $(this).data("volume");
        const stock = $(this).data("stock");
        
        $("#selectedContainerId").val(containerId);
        $("#selectedPrice").val(price);
        $("#selectedVolume").val(volume);
        $("#selectedStock").val(stock);
        
        $("#addToOrderBtn").prop("disabled", false);
    });
    
    // Добавление товара в заказ
    $("#addToOrderBtn").on("click", function() {
        const productId = $("#selectedProductId").val();
        const productName = $("#selectedProductName").val();
        const productImage = $("#selectedProductImage").val();
        const containerId = $("#selectedContainerId").val();
        const price = parseFloat($("#selectedPrice").val());
        const volume = $("#selectedVolume").val();
        const stock = parseInt($("#selectedStock").val());
        
        if (!containerId) {
            alert("Будь ласка, оберіть об\'єм тари");
            return;
        }
        
        // Проверяем, не добавлен ли уже этот контейнер
        if (selectedContainers.has(containerId)) {
            alert("Цей об\'єм вже додано до замовлення");
            return;
        }
        
        addToOrder(productId, productName, productImage, containerId, price, volume, stock);
        $("#productSelectionModal").modal("hide");
        clearModalData();
    });
    
    // Удаление товара из заказа
    $(document).on("click", ".remove-product", function() {
        const containerId = $(this).data("container-id");
        selectedContainers.delete(containerId);
        $(this).closest(".order-item").remove();
        updateOrderSummary();
        checkEmptyOrder();
    });
    
    // Изменение количества
    $(document).on("change", ".quantity-input", function() {
        const item = $(this).closest(".order-item");
        updateItemTotal(item);
        updateOrderSummary();
    });
    
    // Увеличение количества
    $(document).on("click", ".increase-quantity", function() {
        const input = $(this).siblings(".quantity-input");
        const currentValue = parseInt(input.val());
        const maxValue = parseInt(input.data("max-stock"));
        
        if (currentValue < maxValue) {
            input.val(currentValue + 1).trigger("change");
        }
    });
    
    // Уменьшение количества
    $(document).on("click", ".decrease-quantity", function() {
        const input = $(this).siblings(".quantity-input");
        const currentValue = parseInt(input.val());
        
        if (currentValue > 1) {
            input.val(currentValue - 1).trigger("change");
        }
    });
    
    // Функция загрузки продуктов
    function loadProducts() {
        $("#productsList").html("<div class=\"text-center p-3\"><div class=\"spinner-border\" role=\"status\"></div></div>");
        
        $.ajax({
            url: "' . base_url('api/products_with_containers') . '",
            type: "GET",
            dataType: "json",
            success: function(data) {
                renderProducts(data.products || []);
            },
            error: function() {
                $("#productsList").html("<div class=\"alert alert-danger\">Помилка завантаження товарів</div>");
            }
        });
    }
    
    // Функция поиска продуктов
    function searchProducts(keyword) {
        $.ajax({
            url: "' . base_url('api/products_with_containers') . '",
            type: "GET",
            data: { search: keyword },
            dataType: "json",
            success: function(data) {
                renderProducts(data.products || []);
            }
        });
    }
    
    // Отображение продуктов
    function renderProducts(products) {
        let html = "";
        
        if (products.length === 0) {
            html = "<div class=\"alert alert-info\">Товари не знайдені</div>";
        } else {
            products.forEach(function(product) {
                const hasAvailableContainers = product.containers && product.containers.some(c => c.is_active && c.stock_quantity > 0);
                
                html += `
                    <div class="product-card-mini ${!hasAvailableContainers ? 'opacity-50' : ''}" 
                         ${hasAvailableContainers ? 'data-product-id="' + product.id + '"' : ''}
                         ${hasAvailableContainers ? 'data-product-name="' + product.name + '"' : ''}
                         ${hasAvailableContainers ? 'data-product-image="' + (product.image || '<?= asset_url('images/no-image.jpg') ?>') + '"' : ''}
                         ${hasAvailableContainers ? 'data-containers=\'' + JSON.stringify(product.containers) + '\'' : ''}>
                        <div class="d-flex align-items-center">
                            <img src="${product.image || '<?= asset_url('images/no-image.jpg') ?>'}" alt="${product.name}">
                            <div class="ms-3 flex-grow-1">
                                <h6 class="mb-1">${product.name}</h6>
                                <p class="mb-1 text-muted small">${product.description || ''}</p>
                                <div class="d-flex justify-content-between">
                                    <span class="text-primary fw-bold">
                                        від ${product.min_price ? parseFloat(product.min_price).toFixed(2) : '0.00'} грн
                                    </span>
                                    ${hasAvailableContainers ? 
                                        '<button class="btn btn-sm btn-primary select-product">Обрати</button>' :
                                        '<span class="badge bg-danger">Немає в наявності</span>'
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        }
        
        $("#productsList").html(html);
    }
    
    // Показать выбор контейнеров
    function showContainerSelection(productName, containers) {
        $("#selectedProductTitle").text(productName);
        
        let html = "";
        let bestValueId = null;
        let bestPricePerLiter = Infinity;
        
        // Находим лучшее предложение
        containers.forEach(function(container) {
            if (container.is_active && container.stock_quantity > 0) {
                const pricePerLiter = container.price / container.volume;
                if (pricePerLiter < bestPricePerLiter) {
                    bestPricePerLiter = pricePerLiter;
                    bestValueId = container.id;
                }
            }
        });
        
        if (!containers || containers.length === 0) {
            html = "<div class=\"alert alert-warning\">Немає доступних об\'ємів для цього товару</div>";
        } else {
            containers.forEach(function(container) {
                const isOutOfStock = !container.is_active || container.stock_quantity <= 0;
                const isAlreadySelected = selectedContainers.has(container.id.toString());
                const pricePerLiter = (container.price / container.volume).toFixed(2);
                const isBestValue = container.id === bestValueId;
                
                html += `
                    <div class="container-selector ${isOutOfStock || isAlreadySelected ? 'out-of-stock' : ''}"
                         data-container-id="${container.id}"
                         data-price="${container.price}"
                         data-volume="${container.volume}"
                         data-stock="${container.stock_quantity}">
                        
                        <div class="volume-badge">${container.volume} л</div>
                        
                        ${isBestValue && !isOutOfStock && !isAlreadySelected ? 
                            '<div class="best-value-badge">Найвигідніше!</div>' : ''}
                        
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="fw-bold fs-5">${container.price.toFixed(2)} грн</div>
                                <div class="price-per-liter">${pricePerLiter} грн/л</div>
                            </div>
                            <div class="col-md-6 text-end">
                                ${isAlreadySelected ? 
                                    '<span class="text-warning"><i class="fas fa-check-circle me-1"></i>Вже додано</span>' :
                                    (isOutOfStock ? 
                                        '<span class="text-danger"><i class="fas fa-times-circle me-1"></i>Немає в наявності</span>' :
                                        `<span class="text-success"><i class="fas fa-check-circle me-1"></i>Доступно: ${container.stock_quantity} шт.</span>`
                                    )
                                }
                            </div>
                        </div>
                    </div>
                `;
            });
        }
        
        $("#containersList").html(html);
        $("#containerSelectionStep").show();
        $("#addToOrderBtn").prop("disabled", true);
    }
    
    // Добавление товара в заказ
    function addToOrder(productId, productName, productImage, containerId, price, volume, stock) {
        selectedContainers.add(containerId);
        
        const pricePerLiter = (price / volume).toFixed(2);
        
        const itemHtml = `
            <div class="order-item" data-container-id="${containerId}">
                <input type="hidden" name="items[${itemCounter}][container_id]" value="${containerId}">
                <input type="hidden" name="items[${itemCounter}][product_id]" value="${productId}">
                <input type="hidden" name="items[${itemCounter}][price]" value="${price}">
                <input type="hidden" name="items[${itemCounter}][volume]" value="${volume}">
                
                <button type="button" class="remove-product" data-container-id="${containerId}">
                    <i class="fas fa-times"></i>
                </button>
                
                <div class="d-flex align-items-center">
                    <img src="${productImage}" alt="${productName}" class="product-image-preview">
                    <div class="product-details">
                        <h6 class="mb-1">${productName}</h6>
                        <div class="mb-2">
                            <span class="volume-badge">${volume} л</span>
                            <span class="fw-bold ms-2">${price.toFixed(2)} грн</span>
                            <span class="price-per-liter ms-2">(${pricePerLiter} грн/л)</span>
                        </div>
                        <small class="text-muted">Доступно: ${stock} шт.</small>
                    </div>
                    <div class="quantity-control">
                        <button type="button" class="btn btn-sm btn-outline-secondary decrease-quantity">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" 
                               class="form-control form-control-sm quantity-input" 
                               name="items[${itemCounter}][quantity]"
                               value="1" 
                               min="1" 
                               max="${stock}"
                               data-max-stock="${stock}">
                        <button type="button" class="btn btn-sm btn-outline-secondary increase-quantity">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="ms-3 text-end">
                        <div class="total-price item-total" data-unit-price="${price}">
                            ${price.toFixed(2)} грн
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $("#itemsContainer").append(itemHtml);
        itemCounter++;
        updateOrderSummary();
        $("#emptyMessage").hide();
    }
    
    // Обновление итогов товара
    function updateItemTotal(item) {
        const quantity = parseInt(item.find(".quantity-input").val()) || 1;
        const unitPrice = parseFloat(item.find(".item-total").data("unit-price"));
        const total = quantity * unitPrice;
        
        item.find(".item-total").text(total.toFixed(2) + " грн");
    }
    
    // Обновление общих итогов заказа
    function updateOrderSummary() {
        let totalQuantity = 0;
        let totalVolume = 0;
        let totalAmount = 0;
        let uniqueProducts = 0;
        
        $(".order-item").each(function() {
            const quantity = parseInt($(this).find(".quantity-input").val()) || 0;
            const price = parseFloat($(this).find("input[name*=\"[price]\"]").val()) || 0;
            const volume = parseFloat($(this).find("input[name*=\"[volume]\"]").val()) || 1;
            
            totalQuantity += quantity;
            totalVolume += quantity * volume;
            totalAmount += quantity * price;
            uniqueProducts++;
        });
        
        // Обновляем итоги
        $("#orderTotal").text(totalAmount.toFixed(2) + " грн");
        $("#totalQuantity").text(totalQuantity);
        $("#totalVolume").text(totalVolume.toFixed(2) + " л");
        $("#uniqueProducts").text(uniqueProducts);
        $("#totalAmountInput").val(totalAmount.toFixed(2));
        
        // Включаем/выключаем кнопку заказа
        $("#submitOrderBtn").prop("disabled", totalAmount === 0);
        
        // Показываем сводку, если есть товары
        if (totalAmount > 0) {
            $("#orderSummaryCard").show();
        } else {
            $("#orderSummaryCard").hide();
        }
    }
    
    // Проверка пустого заказа
    function checkEmptyOrder() {
        if ($(".order-item").length === 0) {
            $("#emptyMessage").show();
            $("#orderSummaryCard").hide();
        }
    }
    
    // Очистка данных модального окна
    function clearModalData() {
        $("#selectedProductId, #selectedProductName, #selectedProductImage, #selectedContainerId, #selectedPrice, #selectedVolume, #selectedStock").val("");
        $("#containerSelectionStep").hide();
        $("#addToOrderBtn").prop("disabled", true);
        $("#productSearch").val("");
    }
    
    // Закрытие модального окна
    $("#productSelectionModal").on("hidden.bs.modal", function() {
        clearModalData();
    });
    
    // Валидация формы
    $("#orderForm").on("submit", function(e) {
        if ($(".order-item").length === 0) {
            e.preventDefault();
            alert("Додайте хоча б один товар до замовлення");
            return false;
        }
        
        <?php if (has_role(['admin', 'sales_manager'])): ?>
        if ($("#customer_id").val() === "") {
            e.preventDefault();
            alert("Виберіть клієнта");
            return false;
        }
        <?php endif; ?>
        
        if ($("#shipping_address").val().trim() === "") {
            e.preventDefault();
            alert("Введіть адресу доставки");
            return false;
        }
        
        return true;
    });
    
    // Инициализация
    updateOrderSummary();
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

<form id="orderForm" action="<?= base_url('orders/store') ?>" method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="total_amount" id="totalAmountInput" value="0">

    <div class="row">
        <!-- Товары в заказе -->
        <div class="col-md-8">
            <div class="card order-form-card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-shopping-cart me-2"></i> Товари в замовленні
                        </h5>
                        <button type="button" id="addItemBtn" class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-1"></i> Додати товар
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="itemsContainer"></div>
                    
                    <div class="alert alert-info text-center" id="emptyMessage">
                        <i class="fas fa-shopping-basket fa-2x mb-2 d-block"></i> 
                        <h6>Кошик порожній</h6>
                        <p class="mb-0">Натисніть "Додати товар", щоб почати формувати замовлення</p>
                    </div>
                </div>
            </div>
            
            <!-- Сводка заказа -->
            <div class="card order-form-card mt-3" id="orderSummaryCard" style="display: none;">
                <div class="order-summary">
                    <h5 class="mb-3"><i class="fas fa-calculator me-2"></i> Підсумок замовлення</h5>
                    
                    <div class="summary-row">
                        <span>Кількість товарів:</span>
                        <span><span id="totalQuantity">0</span> шт.</span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Унікальних продуктів:</span>
                        <span id="uniqueProducts">0</span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Загальний об'єм:</span>
                        <span id="totalVolume">0.00 л</span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Загальна сума:</span>
                        <span id="orderTotal">0.00 грн</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Детали заказа -->
        <div class="col-md-4">
            <div class="card order-form-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-shipping-fast me-2"></i> Деталі замовлення
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (has_role(['admin', 'sales_manager'])): ?>
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Клієнт <span class="text-danger">*</span></label>
                            <select class="form-select" id="customer_id" name="customer_id" required>
                                <option value="">Виберіть клієнта</option>
                                <?php foreach ($customers ?? [] as $customer): ?>
                                    <option value="<?= $customer['id'] ?>">
                                        <?= $customer['first_name'] . ' ' . $customer['last_name'] ?> (<?= $customer['email'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="shipping_address" class="form-label">Адреса доставки <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="shipping_address" 
                                  name="shipping_address" 
                                  rows="3" 
                                  placeholder="Введіть повну адресу доставки" 
                                  required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Спосіб оплати <span class="text-danger">*</span></label>
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
        </div>
    </div>
</form>

<!-- Модальное окно выбора товара -->
<div class="modal fade" id="productSelectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Вибір товару</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Скрытые поля для выбранного товара -->
                <input type="hidden" id="selectedProductId">
                <input type="hidden" id="selectedProductName">
                <input type="hidden" id="selectedProductImage">
                <input type="hidden" id="selectedContainerId">
                <input type="hidden" id="selectedPrice">
                <input type="hidden" id="selectedVolume">
                <input type="hidden" id="selectedStock">
                
                <div class="row">
                    <!-- Список товаров -->
                    <div class="col-md-6">
                        <h6>Каталог товарів:</h6>
                        
                        <!-- Поиск -->
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="productSearch" 
                                       placeholder="Пошук товарів...">
                            </div>
                        </div>
                        
                        <!-- Список товаров -->
                        <div class="product-search-card">
                            <div id="productsList">
                                <div class="text-center p-3">
                                    <div class="spinner-border" role="status">
                                        <span class="visually-hidden">Завантаження...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Выбор объема тары -->
                    <div class="col-md-6">
                        <div id="containerSelectionStep" style="display: none;">
                            <h6>Оберіть об'єм тари для: <span id="selectedProductTitle" class="text-primary"></span></h6>
                            <div id="containersList"></div>
                        </div>
                        
                        <div class="text-center text-muted" style="display: block;" id="selectProductPrompt">
                            <i class="fas fa-hand-point-left fa-2x mb-3"></i>
                            <p>Спочатку оберіть товар зі списку</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Скасувати
                </button>
                <button type="button" class="btn btn-primary" id="addToOrderBtn" disabled>
                    <i class="fas fa-plus me-1"></i> Додати до замовлення
                </button>
            </div>
        </div>
    </div>
</div>