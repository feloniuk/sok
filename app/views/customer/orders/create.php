<?php
// app/views/customer/orders/create.php - Переработанная страница создания заказа
$title = 'Оформлення замовлення';

// Подключение дополнительных CSS
$extra_css = '
<style>
    .order-form-card {
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: none;
    }
    
    .product-search-card {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .product-item {
        border: 1px solid #e9ecef;
        border-radius: 0.375rem;
        padding: 15px;
        margin-bottom: 15px;
        background: #fff;
        transition: all 0.3s ease;
    }
    
    .product-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .product-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 0.375rem;
    }
    
    .container-option {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 10px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .container-option:hover {
        background-color: #f8f9fa;
        border-color: #007bff;
    }
    
    .container-option.selected {
        background-color: #e3f2fd;
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    
    .quantity-controls {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .quantity-btn {
        width: 32px;
        height: 32px;
        border: 1px solid #dee2e6;
        background: #fff;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .quantity-btn:hover {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }
    
    .quantity-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .quantity-input {
        width: 60px;
        text-align: center;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 5px;
    }
    
    .cart-item {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 0.375rem;
        padding: 15px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }
    
    .cart-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .cart-total {
        font-size: 1.25rem;
        font-weight: bold;
        color: #007bff;
        text-align: center;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 0.375rem;
        margin-top: 15px;
    }
    
    .empty-cart {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }
    
    .product-search {
        position: relative;
    }
    
    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-top: none;
        border-radius: 0 0 0.375rem 0.375rem;
        max-height: 300px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }
    
    .search-result-item {
        padding: 10px 15px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    
    .search-result-item:hover {
        background-color: #f8f9fa;
    }
    
    .search-result-item:last-child {
        border-bottom: none;
    }
    
    .badge-stock {
        font-size: 0.75rem;
    }
    
    .price-per-liter {
        font-size: 0.875rem;
        color: #6c757d;
    }
</style>';

// Подключение дополнительных JS
$extra_js = '
<script>
let cart = {};
let searchTimeout;

$(document).ready(function() {
    loadCart();
    
    // Поиск товаров
    $("#productSearch").on("input", function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => searchProducts(query), 300);
        } else {
            hideSearchResults();
        }
    });
    
    // Скрытие результатов поиска при клике вне области
    $(document).on("click", function(e) {
        if (!$(e.target).closest(".product-search").length) {
            hideSearchResults();
        }
    });
    
    // Обработчики для корзины
    $(document).on("click", ".quantity-increase", function() {
        const cartKey = $(this).data("cart-key");
        const maxQuantity = parseInt($(this).data("max-quantity"));
        increaseQuantity(cartKey, maxQuantity);
    });
    
    $(document).on("click", ".quantity-decrease", function() {
        const cartKey = $(this).data("cart-key");
        decreaseQuantity(cartKey);
    });
    
    $(document).on("change", ".quantity-input", function() {
        const cartKey = $(this).data("cart-key");
        const quantity = parseInt($(this).val()) || 1;
        const maxQuantity = parseInt($(this).attr("max"));
        updateQuantity(cartKey, Math.min(quantity, maxQuantity));
    });
    
    $(document).on("click", ".remove-item", function() {
        const cartKey = $(this).data("cart-key");
        removeFromCart(cartKey);
    });
    
    // Отправка заказа
    $("#orderForm").on("submit", function(e) {
        e.preventDefault();
        submitOrder();
    });
});

function searchProducts(query) {
    $.ajax({
        url: "' . base_url('api/products-with-containers') . '",
        type: "GET",
        data: { search: query },
        dataType: "json",
        success: function(response) {
            showSearchResults(response.products || []);
        },
        error: function() {
            showSearchResults([]);
        }
    });
}

function showSearchResults(products) {
    const resultsContainer = $("#searchResults");
    resultsContainer.empty();
    
    if (products.length === 0) {
        resultsContainer.html("<div class=\"search-result-item\">Товари не знайдено</div>");
    } else {
        products.forEach(function(product) {
            const item = createSearchResultItem(product);
            resultsContainer.append(item);
        });
    }
    
    resultsContainer.show();
}

function createSearchResultItem(product) {
    const minPrice = product.min_price || product.price || 0;
    const hasContainers = product.containers && product.containers.length > 0;
    
    return `
        <div class="search-result-item" onclick="showProductModal(${product.id})">
            <div class="d-flex align-items-center">
                <img src="${product.image ? "' . upload_url('') . '" + product.image : "' . asset_url('images/no-image.jpg') . '"}" 
                     alt="${product.name}" class="me-3" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                <div class="flex-grow-1">
                    <h6 class="mb-1">${product.name}</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-primary fw-bold">від ${parseFloat(minPrice).toFixed(2)} грн</span>
                        <span class="badge ${hasContainers ? "bg-info" : "bg-success"} badge-stock">
                            ${hasContainers ? "Різні об\\\'єми" : "В наявності"}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function hideSearchResults() {
    $("#searchResults").hide();
}

function showProductModal(productId) {
    hideSearchResults();
    $("#productSearch").val("");
    
    $.ajax({
        url: "' . base_url('api/products-with-containers') . '",
        type: "GET",
        data: { product_id: productId },
        dataType: "json",
        success: function(response) {
            if (response.products && response.products.length > 0) {
                const product = response.products[0];
                displayProductModal(product);
            }
        },
        error: function() {
            alert("Помилка завантаження інформації про товар");
        }
    });
}

function displayProductModal(product) {
    let modalContent = `
        <div class="modal fade" id="productModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${product.name}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <img src="${product.image ? "' . upload_url('') . '" + product.image : "' . asset_url('images/no-image.jpg') . '"}" 
                                     alt="${product.name}" class="img-fluid rounded">
                            </div>
                            <div class="col-md-8">
                                <p class="text-muted">${product.description || "Опис відсутній"}</p>
                                
                                <h6>Оберіть об\'єм:</h6>
                                <div id="containerOptions">
    `;
    
    if (product.containers && product.containers.length > 0) {
        product.containers.forEach(function(container, index) {
            const pricePerLiter = (container.price / container.volume).toFixed(2);
            const stockBadge = container.stock_quantity > 0 ?
                `<span class="badge bg-success">В наявності: ${container.stock_quantity}</span>` :
                `<span class="badge bg-danger">Немає в наявності</span>`;
            
            modalContent += `
                <div class="container-option ${index === 0 ? \'selected\' : \'\'}" 
                     data-container-id="${container.id}"
                     data-product-id="${product.id}"
                     data-volume="${container.volume}"
                     data-price="${container.price}"
                     data-stock="${container.stock_quantity}"
                     onclick="selectContainer(this)">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${container.volume} л</strong>
                            <div class="price-per-liter">${pricePerLiter} грн/л</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-primary">${parseFloat(container.price).toFixed(2)} грн</div>
                            ${stockBadge}
                        </div>
                    </div>
                </div>
            `;
        });
    } else {
        // Если контейнеров нет, создаем базовый вариант
        modalContent += `
            <div class="container-option selected" 
                 data-container-id="default"
                 data-product-id="${product.id}"
                 data-volume="1"
                 data-price="${product.min_price || product.price}"
                 data-stock="${product.stock_quantity || 0}"
                 onclick="selectContainer(this)">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Базовий об\\\'єм</strong>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-primary">${parseFloat(product.min_price || product.price).toFixed(2)} грн</div>
                        <span class="badge bg-success">В наявності</span>
                    </div>
                </div>
            </div>
        `;
    }
    
    modalContent += `
                                </div>
                                
                                <div class="mt-3">
                                    <label class="form-label">Кількість:</label>
                                    <div class="quantity-controls">
                                        <button type="button" class="quantity-btn" onclick="changeModalQuantity(-1)">-</button>
                                        <input type="number" id="modalQuantity" class="quantity-input" value="1" min="1">
                                        <button type="button" class="quantity-btn" onclick="changeModalQuantity(1)">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрити</button>
                        <button type="button" class="btn btn-primary" onclick="addSelectedToCart()">Додати до кошика</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Удаляем предыдущий модал и добавляем новый
    $("#productModal").remove();
    $("body").append(modalContent);
    
    // Показываем модал
    const modal = new bootstrap.Modal(document.getElementById("productModal"));
    modal.show();
}

function selectContainer(element) {
    $(".container-option").removeClass("selected");
    $(element).addClass("selected");
    
    const maxStock = parseInt($(element).data("stock"));
    const quantityInput = $("#modalQuantity");
    quantityInput.attr("max", maxStock);
    
    if (parseInt(quantityInput.val()) > maxStock) {
        quantityInput.val(maxStock);
    }
}

function changeModalQuantity(change) {
    const quantityInput = $("#modalQuantity");
    const currentValue = parseInt(quantityInput.val()) || 1;
    const maxValue = parseInt(quantityInput.attr("max")) || 999;
    const newValue = Math.max(1, Math.min(maxValue, currentValue + change));
    
    quantityInput.val(newValue);
}

function addSelectedToCart() {
    const selectedContainer = $(".container-option.selected");
    if (selectedContainer.length === 0) {
        alert("Оберіть об\\\'єм товару");
        return;
    }
    
    const productId = selectedContainer.data("product-id");
    const containerId = selectedContainer.data("container-id");
    const volume = selectedContainer.data("volume");
    const price = selectedContainer.data("price");
    const stock = selectedContainer.data("stock");
    const quantity = parseInt($("#modalQuantity").val()) || 1;
    
    if (quantity > stock) {
        alert(`Недостатньо товару на складі. Доступно: ${stock}`);
        return;
    }
    
    const cartKey = `${productId}_${containerId}`;
    
    if (cart[cartKey]) {
        cart[cartKey].quantity += quantity;
    } else {
        cart[cartKey] = {
            productId: productId,
            containerId: containerId === "default" ? null : containerId,
            name: selectedContainer.closest(".modal").find(".modal-title").text(),
            volume: volume,
            price: price,
            quantity: quantity,
            maxStock: stock
        };
    }
    
    // Проверяем, не превышает ли количество доступное
    if (cart[cartKey].quantity > stock) {
        cart[cartKey].quantity = stock;
    }
    
    saveCart();
    renderCart();
    
    // Закрываем модал
    bootstrap.Modal.getInstance(document.getElementById("productModal")).hide();
}

function increaseQuantity(cartKey, maxQuantity) {
    if (cart[cartKey] && cart[cartKey].quantity < maxQuantity) {
        cart[cartKey].quantity++;
        saveCart();
        renderCart();
    }
}

function decreaseQuantity(cartKey) {
    if (cart[cartKey] && cart[cartKey].quantity > 1) {
        cart[cartKey].quantity--;
        saveCart();
        renderCart();
    }
}

function updateQuantity(cartKey, quantity) {
    if (cart[cartKey]) {
        cart[cartKey].quantity = Math.max(1, Math.min(quantity, cart[cartKey].maxStock));
        saveCart();
        renderCart();
    }
}

function removeFromCart(cartKey) {
    if (cart[cartKey]) {
        delete cart[cartKey];
        saveCart();
        renderCart();
    }
}

function saveCart() {
    localStorage.setItem("orderCart", JSON.stringify(cart));
}

function loadCart() {
    const savedCart = localStorage.getItem("orderCart");
    if (savedCart) {
        try {
            cart = JSON.parse(savedCart);
        } catch (e) {
            cart = {};
        }
    }
    renderCart();
}

function renderCart() {
    const cartContainer = $("#cartItems");
    cartContainer.empty();
    
    if (Object.keys(cart).length === 0) {
        cartContainer.html(`
            <div class="empty-cart">
                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                <h5>Кошик порожній</h5>
                <p>Використайте пошук вище, щоб додати товари</p>
            </div>
        `);
        $("#cartTotal").text("0.00 грн");
        $("#submitOrderBtn").prop("disabled", true);
        return;
    }
    
    let total = 0;
    
    Object.keys(cart).forEach(function(cartKey) {
        const item = cart[cartKey];
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        
        const cartItem = `
            <div class="cart-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${item.name}</h6>
                        <small class="text-muted">Об\\\'єм: ${item.volume} л | ${parseFloat(item.price).toFixed(2)} грн</small>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="quantity-controls">
                            <button type="button" class="quantity-btn quantity-decrease" 
                                    data-cart-key="${cartKey}" 
                                    ${item.quantity <= 1 ? "disabled" : ""}>-</button>
                            <input type="number" class="quantity-input" 
                                   value="${item.quantity}" 
                                   min="1" 
                                   max="${item.maxStock}"
                                   data-cart-key="${cartKey}">
                            <button type="button" class="quantity-btn quantity-increase" 
                                    data-cart-key="${cartKey}"
                                    data-max-quantity="${item.maxStock}"
                                    ${item.quantity >= item.maxStock ? "disabled" : ""}>+</button>
                        </div>
                        <div class="text-end" style="min-width: 80px;">
                            <div class="fw-bold">${itemTotal.toFixed(2)} грн</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item" 
                                data-cart-key="${cartKey}" title="Видалити">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        cartContainer.append(cartItem);
    });
    
    $("#cartTotal").text(total.toFixed(2) + " грн");
    $("#submitOrderBtn").prop("disabled", false);
}

function submitOrder() {
    if (Object.keys(cart).length === 0) {
        alert("Додайте товари до кошика");
        return;
    }
    
    const formData = new FormData(document.getElementById("orderForm"));
    
    // Добавляем товары из корзины
    Object.keys(cart).forEach(function(cartKey) {
        const item = cart[cartKey];
        formData.append("cart_items[]", JSON.stringify({
            product_id: item.productId,
            container_id: item.containerId,
            quantity: item.quantity,
            price: item.price,
            volume: item.volume
        }));
    });
    
    $.ajax({
        url: "' . base_url('orders/store') . '",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            // Очищаем корзину
            cart = {};
            localStorage.removeItem("orderCart");
            
            // Перенаправляем на страницу заказа
            if (response.order_id) {
                window.location.href = "' . base_url('orders/view/') . '" + response.order_id;
            } else {
                window.location.href = "' . base_url('orders') . '";
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            if (response && response.errors) {
                let errorMessage = "Помилки у формі:\\n";
                Object.keys(response.errors).forEach(function(field) {
                    errorMessage += "- " + response.errors[field] + "\\n";
                });
                alert(errorMessage);
            } else {
                alert("Помилка при створенні замовлення");
            }
        }
    });
}

// Очистка корзины при загрузке страницы (опционально)
function clearCart() {
    cart = {};
    localStorage.removeItem("orderCart");
    renderCart();
}
</script>';
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
    <!-- Пошук та кошик -->
    <div class="col-md-8">
        <!-- Пошук товарів -->
        <div class="card order-form-card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-search me-2"></i> Пошук товарів
                </h5>
            </div>
            <div class="card-body">
                <div class="product-search">
                    <input type="text" 
                           class="form-control form-control-lg" 
                           id="productSearch" 
                           placeholder="Введіть назву товару для пошуку..."
                           autocomplete="off">
                    <div id="searchResults" class="search-results"></div>
                </div>
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Введіть мінімум 2 символи для пошуку товарів
                </small>
            </div>
        </div>

        <!-- Кошик -->
        <div class="card order-form-card">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-shopping-cart me-2"></i> Ваше замовлення
                </h5>
                <button type="button" class="btn btn-sm btn-outline-light" onclick="clearCart()">
                    <i class="fas fa-trash me-1"></i> Очистити
                </button>
            </div>
            <div class="card-body">
                <div id="cartItems"></div>
            </div>
            <div class="card-footer">
                <div class="cart-total" id="cartTotal">0.00 грн</div>
            </div>
        </div>
    </div>

    <!-- Форма замовлення -->
    <div class="col-md-4">
        <form id="orderForm">
            <?= csrf_field() ?>

            <div class="card order-form-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-shipping-fast me-2"></i> Деталі замовлення
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="shipping_address" class="form-label">
                            <i class="fas fa-map-marker-alt me-1"></i> Адреса доставки
                        </label>
                        <textarea class="form-control" 
                                  id="shipping_address" 
                                  name="shipping_address" 
                                  rows="3" 
                                  placeholder="Введіть повну адресу доставки" 
                                  required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">
                            <i class="fas fa-credit-card me-1"></i> Спосіб оплати
                        </label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="">Оберіть спосіб оплати</option>
                            <option value="cash_on_delivery">
                                <i class="fas fa-money-bill-wave me-1"></i> Оплата при отриманні
                            </option>
                            <option value="bank_transfer">
                                <i class="fas fa-university me-1"></i> Банківський переказ
                            </option>
                            <option value="credit_card">
                                <i class="fas fa-credit-card me-1"></i> Онлайн-оплата
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">
                            <i class="fas fa-comment me-1"></i> Додаткові коментарі
                        </label>
                        <textarea class="form-control" 
                                  id="notes" 
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
        
        <!-- Швидкі дії -->
        <div class="card order-form-card mt-4">
            <div class="card-header bg-dark text-white">
                <h6 class="mb-0">
                    <i class="fas fa-bolt me-2"></i> Швидкі дії
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= base_url('products') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-th-list me-1"></i> Перейти до каталогу
                    </a>
                    <a href="<?= base_url('orders') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-history me-1"></i> Історія замовлень
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>