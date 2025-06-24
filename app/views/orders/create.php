<?php
// app/views/orders/create.php - Полностью переделанная форма создания заказа
$title = 'Створення нового замовлення';

// Подключение дополнительных CSS
$extra_css = '
<style>
    :root {
        --primary-color: #007bff;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --light-color: #f8f9fa;
        --dark-color: #495057;
    }

    body {
        background-color: #f8f9fc;
    }

    .order-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .page-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, #0056b3 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0, 123, 255, 0.3);
    }

    .page-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 600;
    }

    .page-header p {
        margin: 10px 0 0 0;
        opacity: 0.9;
        font-size: 1.1rem;
    }

    .section-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .section-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .section-header {
        background: linear-gradient(90deg, var(--primary-color), #4dabf7);
        color: white;
        padding: 20px 30px;
        font-weight: 600;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .section-body {
        padding: 30px;
    }

    /* Каталог товаров */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .product-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        background: white;
    }

    .product-card:hover {
        border-color: var(--primary-color);
        box-shadow: 0 5px 15px rgba(0, 123, 255, 0.2);
        transform: translateY(-2px);
    }

    .product-card.unavailable {
        opacity: 0.6;
        cursor: not-allowed;
        background: #f8f9fa;
    }

    .product-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        margin-right: 15px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .product-info {
        flex: 1;
    }

    .product-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 1.1rem;
    }

    .product-description {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 10px;
    }

    .product-price {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--primary-color);
    }

    .availability-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .available {
        background: #d4edda;
        color: #155724;
    }

    .low-stock {
        background: #fff3cd;
        color: #856404;
    }

    .out-of-stock {
        background: #f8d7da;
        color: #721c24;
    }

    /* Выбор контейнеров */
    .container-selection {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #e9ecef;
    }

    .container-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        margin-top: 10px;
    }

    .container-option {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .container-option:hover {
        border-color: var(--primary-color);
    }

    .container-option.selected {
        border-color: var(--primary-color);
        background: #f8f9ff;
        box-shadow: 0 2px 8px rgba(0, 123, 255, 0.2);
    }

    .container-option.unavailable {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f8f9fa;
    }

    .volume-label {
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: 5px;
    }

    .container-price {
        font-weight: 700;
        margin-bottom: 3px;
    }

    .price-per-liter {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .best-value-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: var(--success-color);
        color: white;
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    /* Количество */
    .quantity-controls {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 15px;
    }

    .quantity-btn {
        width: 35px;
        height: 35px;
        border: 2px solid var(--primary-color);
        background: white;
        color: var(--primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
    }

    .quantity-btn:hover {
        background: var(--primary-color);
        color: white;
    }

    .quantity-input {
        width: 80px;
        text-align: center;
        font-weight: 600;
        font-size: 1.1rem;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 8px;
    }

    .add-to-order-btn {
        background: var(--success-color);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 15px;
        width: 100%;
    }

    .add-to-order-btn:hover {
        background: #218838;
        transform: translateY(-1px);
    }

    /* Корзина заказа */
    .order-cart {
        position: sticky;
        top: 20px;
    }

    .cart-item {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        background: #f8f9fa;
        position: relative;
    }

    .cart-item-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
    }

    .cart-item-name {
        font-weight: 600;
        color: #333;
    }

    .remove-item {
        background: var(--danger-color);
        color: white;
        border: none;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.8rem;
    }

    .cart-item-details {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 10px;
    }

    .cart-quantity-controls {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .cart-quantity-section {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .cart-quantity-btn {
        width: 28px;
        height: 28px;
        border: 1px solid var(--primary-color);
        background: white;
        color: var(--primary-color);
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.9rem;
    }

    .cart-quantity-input {
        width: 50px;
        text-align: center;
        font-weight: 600;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        padding: 4px;
    }

    .item-total {
        font-weight: 700;
        color: var(--primary-color);
    }

    /* Сводка заказа */
    .order-summary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px;
        border-radius: 12px;
        margin-top: 20px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .summary-row:last-child {
        border-bottom: 2px solid rgba(255, 255, 255, 0.5);
        margin-bottom: 0;
        font-weight: 700;
        font-size: 1.2rem;
    }

    /* Форма заказа */
    .order-form {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        display: block;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        outline: none;
    }

    .submit-order-btn {
        background: linear-gradient(135deg, var(--success-color) 0%, #20c997 100%);
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1.1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    }

    .submit-order-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
    }

    .submit-order-btn:disabled {
        background: #6c757d;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* Поиск */
    .search-section {
        margin-bottom: 25px;
    }

    .search-input {
        position: relative;
    }

    .search-input input {
        padding-left: 45px;
    }

    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }

    /* Фильтры */
    .filters-section {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .filter-select {
        min-width: 200px;
        padding: 10px 15px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        background: white;
    }

    /* Анимации */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .cart-item {
        animation: slideIn 0.3s ease-out;
    }

    /* Адаптивность */
    @media (max-width: 1200px) {
        .product-grid {
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .order-container {
            padding: 15px;
        }
        
        .page-header {
            padding: 20px;
        }
        
        .page-header h1 {
            font-size: 2rem;
        }
        
        .section-body {
            padding: 20px;
        }
        
        .product-grid {
            grid-template-columns: 1fr;
        }
        
        .container-options {
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        }
        
        .filters-section {
            flex-direction: column;
        }
        
        .filter-select {
            width: 100%;
        }
    }

    /* Состояния загрузки */
    .loading {
        opacity: 0.7;
        pointer-events: none;
    }

    .spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Уведомления */
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 1000;
        transform: translateX(100%);
        transition: all 0.3s ease;
    }

    .toast.show {
        transform: translateX(0);
    }

    .toast.success {
        background: var(--success-color);
    }

    .toast.error {
        background: var(--danger-color);
    }

    .empty-cart {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }

    .empty-cart-icon {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.5;
    }
</style>';

// Подключение дополнительных JS
$extra_js = '
<script>
$(document).ready(function() {
    // Состояние приложения
    let cartItems = [];
    let products = [];
    let selectedFilters = {
        category: "",
        search: ""
    };

    // Инициализация
    init();

    function init() {
        loadProducts();
        setupEventListeners();
        updateUI();
    }

    function setupEventListeners() {
        // Поиск
        $("#searchInput").on("input", debounce(function() {
            selectedFilters.search = $(this).val();
            filterProducts();
        }, 300));

        // Фильтр по категории
        $("#categoryFilter").on("change", function() {
            selectedFilters.category = $(this).val();
            filterProducts();
        });

        // Сброс фильтров
        $("#resetFilters").on("click", function() {
            selectedFilters = { category: "", search: "" };
            $("#searchInput").val("");
            $("#categoryFilter").val("");
            filterProducts();
        });

        // Отправка формы
        $("#orderForm").on("submit", function(e) {
            e.preventDefault();
            submitOrder();
        });
    }

    function loadProducts() {
        showLoading();
        $.ajax({
            url: "' . base_url('api/products_with_containers') . '",
            type: "GET",
            dataType: "json",
            success: function(data) {
                products = data.products || [];
                filterProducts();
                hideLoading();
            },
            error: function() {
                showToast("Помилка завантаження товарів", "error");
                hideLoading();
            }
        });
    }

    function filterProducts() {
        let filtered = products.filter(product => {
            let matchesSearch = true;
            let matchesCategory = true;

            if (selectedFilters.search) {
                const search = selectedFilters.search.toLowerCase();
                matchesSearch = product.name.toLowerCase().includes(search) ||
                               (product.description && product.description.toLowerCase().includes(search));
            }

            if (selectedFilters.category) {
                matchesCategory = product.category_id == selectedFilters.category;
            }

            return matchesSearch && matchesCategory;
        });

        renderProducts(filtered);
    }

    function renderProducts(productList) {
        const container = $("#productsGrid");
        
        if (productList.length === 0) {
            container.html(`
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Товари не знайдені</h5>
                        <p class="text-muted">Спробуйте змінити параметри пошуку</p>
                    </div>
                </div>
            `);
            return;
        }

        let html = "";
        productList.forEach(product => {
            html += renderProductCard(product);
        });

        container.html(html);
    }

    function renderProductCard(product) {
        const hasContainers = product.containers && product.containers.length > 0;
        const availableContainers = hasContainers ? 
            product.containers.filter(c => c.is_active && c.stock_quantity > 0) : [];
        
        const isAvailable = availableContainers.length > 0;
        const minPrice = isAvailable ? 
            Math.min(...availableContainers.map(c => c.price)) : 
            (product.price || 0);

        let stockClass = "out-of-stock";
        let stockText = "Немає в наявності";
        
        if (isAvailable) {
            const totalStock = availableContainers.reduce((sum, c) => sum + c.stock_quantity, 0);
            if (totalStock > 10) {
                stockClass = "available";
                stockText = "В наявності";
            } else {
                stockClass = "low-stock";
                stockText = "Мало в наявності";
            }
        }

        // Находим лучшее предложение
        let bestValueId = null;
        if (availableContainers.length > 1) {
            let bestPricePerLiter = Infinity;
            availableContainers.forEach(container => {
                const pricePerLiter = container.price / container.volume;
                if (pricePerLiter < bestPricePerLiter) {
                    bestPricePerLiter = pricePerLiter;
                    bestValueId = container.id;
                }
            });
        }

        return `
            <div class="product-card ${!isAvailable ? 'unavailable' : ''}" data-product-id="${product.id}">
                <span class="availability-badge ${stockClass}">${stockText}</span>
                
                <div class="d-flex">
                    <img src="${product.image || '<?= asset_url('images/no-image.jpg') ?>'}" 
                         alt="${product.name}" class="product-image">
                    
                    <div class="product-info">
                        <div class="product-name">${product.name}</div>
                        <div class="product-description">${product.description || ''}</div>
                        <div class="product-price">від ${minPrice.toFixed(2)} грн</div>
                    </div>
                </div>

                ${isAvailable ? `
                    <div class="container-selection">
                        <div class="fw-bold mb-2">Оберіть об'єм:</div>
                        <div class="container-options">
                            ${availableContainers.map(container => `
                                <div class="container-option" 
                                     data-container-id="${container.id}"
                                     data-product-id="${product.id}"
                                     data-price="${container.price}"
                                     data-volume="${container.volume}"
                                     data-stock="${container.stock_quantity}">
                                    ${container.id === bestValueId ? '<div class="best-value-badge">Вигідно!</div>' : ''}
                                    <div class="volume-label">${container.volume} л</div>
                                    <div class="container-price">${container.price.toFixed(2)} грн</div>
                                    <div class="price-per-liter">${(container.price / container.volume).toFixed(2)} грн/л</div>
                                </div>
                            `).join('')}
                        </div>
                        
                        <div class="quantity-controls" style="display: none;">
                            <span>Кількість:</span>
                            <div class="d-flex align-items-center">
                                <button type="button" class="quantity-btn decrease-qty">−</button>
                                <input type="number" class="quantity-input" value="1" min="1">
                                <button type="button" class="quantity-btn increase-qty">+</button>
                            </div>
                            <button type="button" class="add-to-order-btn">
                                <i class="fas fa-cart-plus me-1"></i>
                                Додати до замовлення
                            </button>
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
    }

    // Обработчики событий для продуктов (делегирование событий)
    $(document).on("click", ".container-option", function() {
        const card = $(this).closest(".product-card");
        const isUnavailable = $(this).hasClass("unavailable");
        
        if (isUnavailable) return;

        // Убираем выделение с других опций в этой карточке
        card.find(".container-option").removeClass("selected");
        $(this).addClass("selected");

        // Показываем элементы управления количеством
        const quantityControls = card.find(".quantity-controls");
        const maxStock = parseInt($(this).data("stock"));
        
        quantityControls.find(".quantity-input").attr("max", maxStock);
        if (parseInt(quantityControls.find(".quantity-input").val()) > maxStock) {
            quantityControls.find(".quantity-input").val(maxStock);
        }
        
        quantityControls.show();
    });

    // Кнопки изменения количества
    $(document).on("click", ".increase-qty", function() {
        const input = $(this).siblings(".quantity-input");
        const current = parseInt(input.val());
        const max = parseInt(input.attr("max"));
        
        if (current < max) {
            input.val(current + 1);
        }
    });

    $(document).on("click", ".decrease-qty", function() {
        const input = $(this).siblings(".quantity-input");
        const current = parseInt(input.val());
        
        if (current > 1) {
            input.val(current - 1);
        }
    });

    // Добавление в заказ
    $(document).on("click", ".add-to-order-btn", function() {
        const card = $(this).closest(".product-card");
        const selectedContainer = card.find(".container-option.selected");
        
        if (selectedContainer.length === 0) {
            showToast("Оберіть об\'єм тари", "error");
            return;
        }

        const productId = selectedContainer.data("product-id");
        const containerId = selectedContainer.data("container-id");
        const price = parseFloat(selectedContainer.data("price"));
        const volume = parseFloat(selectedContainer.data("volume"));
        const stock = parseInt(selectedContainer.data("stock"));
        const quantity = parseInt(card.find(".quantity-input").val());
        const productName = card.find(".product-name").text();
        const productImage = card.find(".product-image").attr("src");

        if (quantity > stock) {
            showToast(`Недостатньо товару на складі. Доступно: ${stock}`, "error");
            return;
        }

        // Проверяем, нет ли уже такого товара в корзине
        const existingItemIndex = cartItems.findIndex(item => 
            item.container_id === containerId
        );

        if (existingItemIndex !== -1) {
            // Обновляем количество
            cartItems[existingItemIndex].quantity += quantity;
        } else {
            // Добавляем новый товар
            cartItems.push({
                product_id: productId,
                container_id: containerId,
                name: productName,
                image: productImage,
                price: price,
                volume: volume,
                quantity: quantity,
                stock: stock
            });
        }

        updateCartUI();
        showToast("Товар додано до замовлення", "success");

        // Сбрасываем выбор
        card.find(".container-option").removeClass("selected");
        card.find(".quantity-controls").hide();
        card.find(".quantity-input").val(1);
    });

    // Управление корзиной
    $(document).on("click", ".remove-item", function() {
        const index = parseInt($(this).data("index"));
        cartItems.splice(index, 1);
        updateCartUI();
        showToast("Товар видалено з замовлення", "success");
    });

    $(document).on("click", ".cart-quantity-btn.increase", function() {
        const index = parseInt($(this).data("index"));
        const item = cartItems[index];
        
        if (item.quantity < item.stock) {
            item.quantity++;
            updateCartUI();
        }
    });

    $(document).on("click", ".cart-quantity-btn.decrease", function() {
        const index = parseInt($(this).data("index"));
        const item = cartItems[index];
        
        if (item.quantity > 1) {
            item.quantity--;
            updateCartUI();
        }
    });

    $(document).on("change", ".cart-quantity-input", function() {
        const index = parseInt($(this).data("index"));
        const newQuantity = parseInt($(this).val());
        const item = cartItems[index];
        
        if (newQuantity >= 1 && newQuantity <= item.stock) {
            item.quantity = newQuantity;
            updateCartUI();
        } else {
            $(this).val(item.quantity);
        }
    });

    function updateCartUI() {
        const cartContainer = $("#cartItems");
        
        if (cartItems.length === 0) {
            cartContainer.html(`
                <div class="empty-cart">
                    <div class="empty-cart-icon">🛒</div>
                    <h5>Кошик порожній</h5>
                    <p>Додайте товари зі списку вище</p>
                </div>
            `);
        } else {
            let html = "";
            cartItems.forEach((item, index) => {
                html += `
                    <div class="cart-item">
                        <div class="cart-item-header">
                            <div class="cart-item-name">${item.name}</div>
                            <button class="remove-item" data-index="${index}">×</button>
                        </div>
                        <div class="cart-item-details">
                            ${item.volume} л • ${item.price.toFixed(2)} грн за шт • 
                            ${(item.price / item.volume).toFixed(2)} грн/л
                        </div>
                        <div class="cart-quantity-controls">
                            <div class="cart-quantity-section">
                                <button class="cart-quantity-btn decrease" data-index="${index}">−</button>
                                <input type="number" class="cart-quantity-input" value="${item.quantity}" 
                                       min="1" max="${item.stock}" data-index="${index}">
                                <button class="cart-quantity-btn increase" data-index="${index}">+</button>
                            </div>
                            <div class="item-total">${(item.price * item.quantity).toFixed(2)} грн</div>
                        </div>
                    </div>
                `;
            });
            cartContainer.html(html);
        }

        updateOrderSummary();
        updateUI();
    }

    function updateOrderSummary() {
        const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);
        const totalVolume = cartItems.reduce((sum, item) => sum + (item.volume * item.quantity), 0);
        const totalAmount = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const uniqueProducts = cartItems.length;

        $("#orderSummary").html(`
            <div class="summary-row">
                <span>Унікальних товарів:</span>
                <span>${uniqueProducts}</span>
            </div>
            <div class="summary-row">
                <span>Загальна кількість:</span>
                <span>${totalItems} шт</span>
            </div>
            <div class="summary-row">
                <span>Загальний об'єм:</span>
                <span>${totalVolume.toFixed(2)} л</span>
            </div>
            <div class="summary-row">
                <span>Загальна сума:</span>
                <span>${totalAmount.toFixed(2)} грн</span>
            </div>
        `);

        // Обновляем скрытое поле для отправки
        $("#cartData").val(JSON.stringify(cartItems));
        $("#totalAmount").val(totalAmount.toFixed(2));
    }

    function updateUI() {
        const hasItems = cartItems.length > 0;
        $("#submitOrderBtn").prop("disabled", !hasItems);
        
        if (hasItems) {
            $("#orderSummary").parent().show();
        } else {
            $("#orderSummary").parent().hide();
        }
    }

    function submitOrder() {
        if (cartItems.length === 0) {
            showToast("Додайте товари до замовлення", "error");
            return;
        }

        const formData = new FormData(document.getElementById("orderForm"));
        
        // Добавляем товары в форму
        cartItems.forEach((item, index) => {
            formData.append(`items[${index}][product_id]`, item.product_id);
            formData.append(`items[${index}][container_id]`, item.container_id);
            formData.append(`items[${index}][quantity]`, item.quantity);
            formData.append(`items[${index}][price]`, item.price);
            formData.append(`items[${index}][volume]`, item.volume);
        });

        const submitBtn = $("#submitOrderBtn");
        const originalText = submitBtn.html();
        submitBtn.html('<span class="spinner"></span> Обробка...').prop("disabled", true);

        $.ajax({
            url: "' . base_url('orders/store') . '",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                showToast("Замовлення успішно створено!", "success");
                setTimeout(() => {
                    window.location.href = "' . base_url('orders') . '";
                }, 1500);
            },
            error: function(xhr) {
                let errorMessage = "Помилка при створенні замовлення";
                
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors);
                    errorMessage = errors[0];
                }
                
                showToast(errorMessage, "error");
                submitBtn.html(originalText).prop("disabled", false);
            }
        });
    }

    function showToast(message, type) {
        const toast = $(`
            <div class="toast ${type}">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
                ${message}
            </div>
        `);

        $("body").append(toast);
        
        setTimeout(() => toast.addClass("show"), 100);
        setTimeout(() => {
            toast.removeClass("show");
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    function showLoading() {
        $("#productsGrid").html(`
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Завантаження...</span>
                </div>
                <div class="mt-2 text-muted">Завантаження товарів...</div>
            </div>
        `);
    }

    function hideLoading() {
        // Загрузка скрыта через обновление контента
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
});
</script>';
?>

<div class="order-container">
    <!-- Заголовок страницы -->
    <div class="page-header">
        <h1><i class="fas fa-shopping-cart me-3"></i><?= $title ?></h1>
        <p>Оберіть товари та оформіть замовлення у декілька кліків</p>
    </div>

    <div class="row">
        <!-- Каталог товаров -->
        <div class="col-lg-8">
            <div class="section-card">
                <div class="section-header">
                    <span><i class="fas fa-boxes me-2"></i>Каталог товарів</span>
                    <span id="productCount">Завантаження...</span>
                </div>
                <div class="section-body">
                    <!-- Поиск и фильтры -->
                    <div class="search-section">
                        <div class="search-input">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="searchInput" class="form-control" 
                                   placeholder="Пошук товарів за назвою...">
                        </div>
                    </div>

                    <div class="filters-section">
                        <select id="categoryFilter" class="filter-select">
                            <option value="">Всі категорії</option>
                            <?php foreach ($categories ?? [] as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" id="resetFilters" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Скинути фільтри
                        </button>
                    </div>

                    <!-- Сетка товаров -->
                    <div class="product-grid" id="productsGrid">
                        <!-- Товары загружаются через AJAX -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Корзина и форма заказа -->
        <div class="col-lg-4">
            <div class="order-cart">
                <!-- Корзина -->
                <div class="section-card">
                    <div class="section-header">
                        <span><i class="fas fa-shopping-basket me-2"></i>Ваше замовлення</span>
                        <span id="cartCount">0 товарів</span>
                    </div>
                    <div class="section-body">
                        <div id="cartItems">
                            <div class="empty-cart">
                                <div class="empty-cart-icon">🛒</div>
                                <h5>Кошик порожній</h5>
                                <p>Додайте товари зі списку вище</p>
                            </div>
                        </div>

                        <!-- Сводка заказа -->
                        <div class="order-summary" style="display: none;">
                            <h5 class="mb-3"><i class="fas fa-calculator me-2"></i>Підсумок</h5>
                            <div id="orderSummary"></div>
                        </div>
                    </div>
                </div>

                <!-- Форма заказа -->
                <form id="orderForm" class="order-form">
                    <?= csrf_field() ?>
                    <input type="hidden" id="cartData" name="cart_data" value="">
                    <input type="hidden" id="totalAmount" name="total_amount" value="0">

                    <h5 class="mb-4"><i class="fas fa-user me-2"></i>Деталі замовлення</h5>

                    <?php if (has_role(['admin', 'sales_manager'])): ?>
                        <div class="form-group">
                            <label class="form-label">Клієнт <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-control" required>
                                <option value="">Виберіть клієнта</option>
                                <?php foreach ($customers ?? [] as $customer): ?>
                                    <option value="<?= $customer['id'] ?>">
                                        <?= $customer['first_name'] . ' ' . $customer['last_name'] ?> 
                                        (<?= $customer['email'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label">Адреса доставки <span class="text-danger">*</span></label>
                        <textarea name="shipping_address" class="form-control" rows="3" 
                                  placeholder="Введіть повну адресу доставки" required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Спосіб оплати <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-control" required>
                            <option value="">Оберіть спосіб оплати</option>
                            <option value="cash_on_delivery">Оплата при отриманні</option>
                            <option value="bank_transfer">Банківський переказ</option>
                            <option value="credit_card">Онлайн-оплата</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Додаткові коментарі</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Додаткова інформація або побажання"></textarea>
                    </div>

                    <button type="submit" id="submitOrderBtn" class="submit-order-btn" disabled>
                        <i class="fas fa-check me-2"></i>Оформити замовлення
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>