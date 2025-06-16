<?php
// app/views/orders/create.php - Обновленная форма создания заказа с поддержкой объемов тары
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
        border: 1px solid #dee2e6;
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
        width: 70px;
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
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 10px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .container-selector:hover {
        border-color: #007bff;
        background-color: #f8f9ff;
    }
    
    .container-selector.selected {
        border-color: #007bff;
        background-color: #e7f3ff;
    }
    
    .container-selector.out-of-stock {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .volume-badge {
        background: #007bff;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: bold;
    }
    
    .price-per-liter {
        font-size: 0.8rem;
        color: #6c757d;
    }
</style>';

// Подключение дополнительных JS
$extra_js = '
<script>
$(document).ready(function() {
    // Счетчик для индексации товаров
    let itemCounter = $(".order-item").length;
    
    // Добавление нового товара
    $("#addItemBtn").on("click", function() {
        showProductSelectionModal();
    });
    
    // Удаление товара из корзины
    $(document).on("click", ".remove-product", function() {
        $(this).closest(".order-item").remove();
        updateTotalAmount();
    });
    
    // Изменение количества товара
    $(document).on("change", ".quantity-input", function() {
        updateItemTotal($(this).closest(".order-item"));
        updateTotalAmount();
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
    
    // Выбор контейнера в модальном окне
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
    
    // Добавление выбранного товара в заказ
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
        
        addToOrder(productId, productName, productImage, containerId, price, volume, stock);
        $("#productSelectionModal").modal("hide");
    });
    
    // Функция добавления товара в заказ
    function addToOrder(productId, productName, productImage, containerId, price, volume, stock) {
        // Проверяем, есть ли уже такой контейнер в заказе
        const existingItem = $(`.order-item[data-container-id="${containerId}"]`);
        if (existingItem.length > 0) {
            const quantityInput = existingItem.find(".quantity-input");
            const currentQty = parseInt(quantityInput.val());
            if (currentQty < stock) {
                quantityInput.val(currentQty + 1).trigger("change");
            } else {
                alert("Недостатньо товару на складі");
            }
            return;
        }
        
        const itemHtml = `
            <div class="order-item" data-container-id="${containerId}">
                <input type="hidden" name="items[${itemCounter}][container_id]" value="${containerId}">
                <input type="hidden" name="items[${itemCounter}][product_id]" value="${productId}">
                <input type="hidden" name="items[${itemCounter}][price]" value="${price}">
                
                <button type="button" class="remove-product">
                    <i class="fas fa-times"></i>
                </button>
                
                <div class="d-flex align-items-center">
                    <img src="${productImage}" alt="${productName}" class="product-image-preview">
                    <div class="product-details">
                        <h6 class="mb-1">${productName}</h6>
                        <div class="mb-1">
                            <span class="volume-badge">${volume} л</span>
                            <span class="ms-2">${price.toFixed(2)} грн</span>
                            <span class="price-per-liter ms-2">(${(price/volume).toFixed(2)} грн/л)</span>
                        </div>
                        <small class="text-muted">Доступно: ${stock} шт.</small>
                    </div>
                    <div class="quantity-control">
                        <button type="button" class="btn btn-sm btn-outline-secondary decrease-quantity">-</button>
                        <input type="number" 
                               class="form-control form-control-sm quantity-input" 
                               name="items[${itemCounter}][quantity]"
                               value="1" 
                               min="1" 
                               max="${stock}"
                               data-max-stock="${stock}">
                        <button type="button" class="btn btn-sm btn-outline-secondary increase-quantity">+</button>
                    </div>
                    <div class="ms-3 text-end">
                        <div class="item-total">${price.toFixed(2)} грн</div>
                    </div>
                </div>
            </div>
        `;
        
        $("#itemsContainer").append(itemHtml);
        itemCounter++;
        updateTotalAmount();
        $("#emptyMessage").hide();
    }
    
    // Расчет стоимости товара
    function updateItemTotal(item) {
        const quantity = parseInt(item.find(".quantity-input").val()) || 1;
        const price = parseFloat(item.find("input[name*=\"[price]\"]").val()) || 0;
        const total = quantity * price;
        
        item.find(".item-total").text(total.toFixed(2) + " грн");
    }
    
    // Расчет общей стоимости заказа
    function updateTotalAmount() {
        let total = 0;
        
        $(".order-item").each(function() {
            const quantity = parseInt($(this).find(".quantity-input").val()) || 0;
            const price = parseFloat($(this).find("input[name*=\"[price]\"]").val()) || 0;
            total += quantity * price;
        });
        
        $("#orderTotal").text(total.toFixed(2) + " грн");
        $("#totalAmountInput").val(total.toFixed(2));
        
        // Показать/скрыть кнопку заказа
        $("#submitOrderBtn").prop("disabled", total === 0);
        
        if ($(".order-item").length === 0) {
            $("#emptyMessage").show();
        }
    }
    
    // Показать модальное окно выбора товара
    function showProductSelectionModal() {
        loadProducts();
        $("#productSelectionModal").modal("show");
    }
    
    // Загрузка списка товаров
    function loadProducts() {
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
    
    // Отображение списка товаров
    function renderProducts(products) {
        let html = "";
        
        if (products.length === 0) {
            html = "<div class=\"alert alert-info\">Товари не знайдені</div>";
        } else {
            products.forEach(function(product) {
                html += `
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex">
                                    <img src="${product.image || '<?= asset_url('images/no-image.jpg') ?>'}"
                                         class="me-3"
                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                    <div class="flex-grow-1">
                                        <h6 class="card-title mb-1">${product.name}</h6>
                                        <p class="card-text text-muted small mb-2">${product.description || ''}</p>
                                        <button class="btn btn-sm btn-primary select-product"
                                                data-product-id="${product.id}"
                                                data-product-name="${product.name}"
                                                data-product-image="${product.image || '<?= asset_url('images/no-image.jpg') ?>'}"
                                                data-containers='${JSON.stringify(product.containers)}'>
                                            Обрати
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        }
        
        $("#productsList").html(html);
    }
    
    // Выбор продукта
    $(document).on("click", ".select-product", function() {
        const productId = $(this).data("product-id");
        const productName = $(this).data("product-name");
        const productImage = $(this).data("product-image");
        const containers = $(this).data("containers");
        
        $("#selectedProductId").val(productId);
        $("#selectedProductName").val(productName);
        $("#selectedProductImage").val(productImage);
        
        showContainerSelection(containers);
    });
    
    // Показать выбор контейнеров
    function showContainerSelection(containers) {
        let html = "";
        
        if (!containers || containers.length === 0) {
            html = "<div class=\"alert alert-warning\">Немає доступних об\'ємів для цього товару</div>";
        } else {
            containers.forEach(function(container) {
                const isOutOfStock = container.stock_quantity <= 0;
                const pricePerLiter = (container.price / container.volume).toFixed(2);
                
                html += `
                    <div class="container-selector ${isOutOfStock ? 'out-of-stock' : ''}"
                         data-container-id="${container.id}"
                         data-price="${container.price}"
                         data-volume="${container.volume}"
                         data-stock="${container.stock_quantity}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="d-flex align-items-center mb-1">
                                    <span class="volume-badge me-2">${container.volume} л</span>
                                    <strong>${container.price.toFixed(2)} грн</strong>
                                </div>
                                <div class="price-per-liter">${pricePerLiter} грн/л</div>
                            </div>
                            <div class="text-end">
                                ${isOutOfStock ? 
                                    '<span class="text-danger small">Немає в наявності</span>' : 
                                    `<span class="text-success small">В наявності: ${container.stock_quantity} шт.</span>`
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
    
    // Инициализация общей суммы
    updateTotalAmount();
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

<div class="row">
    <div class="col-md-8">
        <div class="card order-form-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-shopping-cart me-2"></i> Ваше замовлення
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6>Товари в замовленні</h6>
                    <button type="button" id="addItemBtn" class="btn btn-success btn-sm">
                        <i class="fas fa-plus me-1"></i> Додати товар
                    </button>
                </div>
                
                <div id="itemsContainer"></div>
                
                <div class="alert alert-info text-center" id="emptyMessage">
                    <i class="fas fa-shopping-basket me-2"></i> 
                    Натисніть "Додати товар", щоб додати товари до замовлення
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Загальна сума: <span id="orderTotal" class="text-primary">0.00 грн</span></h4>
                    <a href="<?= base_url('products') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-shopping-basket me-1"></i> Перейти до каталогу
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <form id="orderForm" action="<?= base_url('orders/store') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="total_amount" id="totalAmountInput" value="0">

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
        </form>
    </div>
</div>

<!-- Модальное окно выбора товара -->
<div class="modal fade" id="productSelectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
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
                
                <!-- Список товаров -->
                <div class="mb-4">
                    <h6>Оберіть товар:</h6>
                    <div class="row" id="productsList">
                        <div class="col-12 text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Завантаження...</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Выбор объема тары -->
                <div id="containerSelectionStep" style="display: none;">
                    <h6>Оберіть об'єм тари:</h6>
                    <div id="containersList"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                <button type="button" class="btn btn-primary" id="addToOrderBtn" disabled>
                    <i class="fas fa-plus me-1"></i> Додати до замовлення
                </button>
            </div>
        </div>
    </div>
</div>