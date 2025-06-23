<?php
// app/views/products/view.php - Исправленная страница просмотра продукта
$title = $product['name'] ?? 'Деталі продукту';

// Функция для определения класса бейджа наличия
function getStockBadgeClass($quantity) {
    if ($quantity > 10) {
        return 'success';
    } elseif ($quantity > 0) {
        return 'warning';
    } else {
        return 'danger';
    }
}

// Функция для определения лучшего предложения по цене за литр
function getBestValueContainer($containers) {
    if (empty($containers)) return null;
    
    $bestValue = null;
    $bestPricePerLiter = PHP_FLOAT_MAX;
    
    foreach ($containers as $container) {
        if ($container['is_active'] && $container['stock_quantity'] > 0) {
            $pricePerLiter = $container['price'] / $container['volume'];
            if ($pricePerLiter < $bestPricePerLiter) {
                $bestPricePerLiter = $pricePerLiter;
                $bestValue = $container['id'];
            }
        }
    }
    
    return $bestValue;
}

$bestValueId = getBestValueContainer($containers ?? []);

// Проверяем наличие контейнеров
$hasAvailableContainers = false;
if (!empty($containers)) {
    foreach ($containers as $container) {
        if ($container['is_active'] && $container['stock_quantity'] > 0) {
            $hasAvailableContainers = true;
            break;
        }
    }
}

// Подключение дополнительных CSS
$extra_css = '
<style>
    .product-image {
        max-height: 400px;
        width: 100%;
        object-fit: contain;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
        box-shadow: 0 2px 8px rgba(0,123,255,0.2);
    }
    
    .container-selector.selected {
        border-color: #007bff;
        background-color: #f8f9ff;
        box-shadow: 0 2px 8px rgba(0,123,255,0.3);
    }
    
    .container-selector.out-of-stock {
        opacity: 0.6;
        cursor: not-allowed;
        background-color: #f8f9fa;
    }
    
    .container-selector.out-of-stock:hover {
        border-color: #e9ecef;
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
    
    .price-display {
        font-size: 1.5rem;
        font-weight: bold;
        color: #007bff;
    }
    
    .price-per-liter {
        font-size: 0.9rem;
        color: #6c757d;
    }
    
    .stock-indicator {
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .stock-high { color: #28a745; }
    .stock-medium { color: #ffc107; }
    .stock-low { color: #dc3545; }
    
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
    }
    
    .related-product-card {
        transition: all 0.3s ease;
        height: 100%;
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .related-product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .related-product-card .card-img-top {
        height: 160px;
        object-fit: cover;
    }
    
    .order-summary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 0.5rem;
        padding: 20px;
        margin-top: 20px;
    }
</style>';

// Дополнительные JS скрипты
$extra_js = '
<script>
$(document).ready(function() {
    let selectedContainer = null;
    let selectedPrice = 0;
    let selectedVolume = 0;
    
    // Выбор объема тары
    $(".container-selector").on("click", function() {
        if ($(this).hasClass("out-of-stock")) {
            return;
        }
        
        $(".container-selector").removeClass("selected");
        $(this).addClass("selected");
        
        selectedContainer = $(this).data("container-id");
        selectedPrice = parseFloat($(this).data("price"));
        selectedVolume = parseFloat($(this).data("volume"));
        
        updatePriceDisplay();
        
        const maxQuantity = parseInt($(this).data("stock"));
        $("#quantity").attr("max", maxQuantity);
        
        if (parseInt($("#quantity").val()) > maxQuantity) {
            $("#quantity").val(maxQuantity);
        }
        
        $("#actionButtons").show();
        
        // Обновляем скрытые поля для передачи в заказ
        $("#selectedContainerId").val(selectedContainer);
        $("#selectedPrice").val(selectedPrice);
        $("#selectedVolume").val(selectedVolume);
    });
    
    $("#quantity").on("input", function() {
        updatePriceDisplay();
    });
    
    $("#increaseQty").on("click", function() {
        const input = $("#quantity");
        const current = parseInt(input.val());
        const max = parseInt(input.attr("max"));
        
        if (current < max) {
            input.val(current + 1);
            updatePriceDisplay();
        }
    });
    
    $("#decreaseQty").on("click", function() {
        const input = $("#quantity");
        const current = parseInt(input.val());
        
        if (current > 1) {
            input.val(current - 1);
            updatePriceDisplay();
        }
    });
    
    function updatePriceDisplay() {
        if (selectedContainer && selectedPrice) {
            const quantity = parseInt($("#quantity").val()) || 1;
            const totalPrice = (selectedPrice * quantity).toFixed(2);
            const totalVolume = (selectedVolume * quantity).toFixed(2);
            
            $("#totalPrice").text(totalPrice + " грн");
            $("#unitPrice").text("(" + selectedPrice.toFixed(2) + " грн за " + selectedVolume + " л)");
            $("#totalVolume").text("Загальний об\'єм: " + totalVolume + " л");
        }
    }
    
    // Обработка формы "Добавить в корзину"
    $("#addToCartForm").on("submit", function(e) {
        if (!selectedContainer) {
            e.preventDefault();
            alert("Будь ласка, оберіть об\'єм тари");
            return false;
        }
        
        const quantity = parseInt($("#quantity").val());
        if (!quantity || quantity < 1) {
            e.preventDefault();
            alert("Будь ласка, вкажіть кількість");
            return false;
        }
        
        return true;
    });
    
    // Обработка ссылки "Заказать сейчас"
    $("#orderNowBtn").on("click", function(e) {
        if (!selectedContainer) {
            e.preventDefault();
            alert("Будь ласка, оберіть об\'єм тари");
            return false;
        }
        
        const quantity = parseInt($("#quantity").val());
        if (!quantity || quantity < 1) {
            e.preventDefault();
            alert("Будь ласка, вкажіть кількість");
            return false;
        }
        
        // Перенаправляем на создание заказа с параметрами
        const url = "' . base_url('orders/create') . '?" + 
                   "container_id=" + selectedContainer + 
                   "&quantity=" + quantity +
                   "&product_id=" + "' . $product['id'] . '";
        window.location.href = url;
        
        return false;
    });
    
    // Автоматический выбор первого доступного контейнера
    const firstAvailable = $(".container-selector:not(.out-of-stock)").first();
    if (firstAvailable.length > 0) {
        firstAvailable.click();
    }
});
</script>';
?>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="position-relative">
            <?php if ($product['is_featured']): ?>
                <span class="badge bg-warning position-absolute top-0 end-0 m-3">Акція</span>
            <?php endif; ?>
            <img src="<?= $product['image'] ? upload_url($product['image']) : asset_url('images/no-image.jpg') ?>" alt="<?= $product['name'] ?>" class="product-image">
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Головна</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('products') ?>">Продукція</a></li>
                <?php if (isset($product['category_id']) && isset($product['category_name'])): ?>
                    <li class="breadcrumb-item"><a href="<?= base_url('categories/view/' . $product['category_id']) ?>"><?= $product['category_name'] ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"><?= $product['name'] ?></li>
            </ol>
        </nav>
        
        <h1 class="mb-3"><?= $product['name'] ?></h1>
        
        <div class="mb-3">
            <?php if (isset($product['category_name']) && $product['category_name']): ?>
                <span class="badge bg-primary p-2 ms-2">
                    <i class="fas fa-tag me-1"></i> <?= $product['category_name'] ?>
                </span>
            <?php endif; ?>
        </div>
        
        <div class="mb-4">
            <p class="text-muted"><?= nl2br($product['description']) ?></p>
        </div>
        
        <!-- Выбор объема тары -->
        <div class="mb-4">
            <h5 class="mb-3">
                <i class="fas fa-wine-bottle me-2"></i>
                Оберіть об'єм тари:
            </h5>
            
            <?php if ($hasAvailableContainers): ?>
                <?php foreach ($containers as $container): ?>
                    <div class="container-selector <?= $container['stock_quantity'] <= 0 ? 'out-of-stock' : '' ?>" 
                         data-container-id="<?= $container['id'] ?>" 
                         data-price="<?= $container['price'] ?>" 
                         data-volume="<?= $container['volume'] ?>"
                         data-stock="<?= $container['stock_quantity'] ?>">
                        
                        <div class="volume-badge"><?= $container['volume'] ?> л</div>
                        
                        <?php if ($container['id'] == $bestValueId): ?>
                            <div class="best-value-badge">Вигідно!</div>
                        <?php endif; ?>
                        
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="price-display"><?= number_format($container['price'], 2) ?> грн</div>
                                <div class="price-per-liter">
                                    <?= number_format($container['price'] / $container['volume'], 2) ?> грн/л
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <?php if ($container['stock_quantity'] > 0): ?>
                                    <div class="stock-indicator stock-<?= $container['stock_quantity'] > 10 ? 'high' : ($container['stock_quantity'] > 5 ? 'medium' : 'low') ?>">
                                        <i class="fas fa-check-circle me-1"></i>
                                        В наявності: <?= $container['stock_quantity'] ?> шт.
                                    </div>
                                <?php else: ?>
                                    <div class="stock-indicator stock-low">
                                        <i class="fas fa-times-circle me-1"></i>
                                        Немає в наявності
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    На жаль, цей продукт тимчасово недоступний.
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Выбор количества и кнопки действий -->
        <?php if ($hasAvailableContainers): ?>
            <div id="actionButtons" style="display: none;">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="quantity" class="form-label">Кількість:</label>
                        <div class="input-group quantity-selector">
                            <button class="btn btn-outline-secondary" type="button" id="decreaseQty">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" class="form-control text-center" id="quantity" value="1" min="1">
                            <button class="btn btn-outline-secondary" type="button" id="increaseQty">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Загальна вартість:</label>
                        <div class="price-display" id="totalPrice">0.00 грн</div>
                        <div class="price-per-liter" id="unitPrice"></div>
                        <div class="price-per-liter" id="totalVolume"></div>
                    </div>
                </div>
                
                <div class="d-grid gap-2 mb-4">
                    <?php if (is_logged_in() && has_role('customer')): ?>
                        <!-- Форма добавления в корзину (если будет корзина) -->
                        <form id="addToCartForm" action="<?= base_url('orders/add_to_cart') ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <input type="hidden" id="selectedContainerId" name="container_id" value="">
                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-cart-plus me-2"></i> Додати до кошика
                            </button>
                        </form>
                        
                        <!-- Кнопка заказа сейчас -->
                        <button type="button" id="orderNowBtn" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-shopping-cart me-2"></i> Замовити зараз
                        </button>
                    <?php elseif (!is_logged_in()): ?>
                        <a href="<?= base_url('auth/login') ?>" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-sign-in-alt me-2"></i> Увійдіть, щоб замовити
                        </a>
                    <?php endif; ?>
                    
                    <?php if (has_role(['admin', 'warehouse_manager'])): ?>
                        <div class="btn-group">
                            <a href="<?= base_url('products/edit/' . $product['id']) ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i> Редагувати
                            </a>
                            
                            <?php if (has_role('admin')): ?>
                                <a href="<?= base_url('products/delete/' . $product['id']) ?>" class="btn btn-danger confirm-delete" data-item-name="товар '<?= $product['name'] ?>'">
                                    <i class="fas fa-trash me-1"></i> Видалити
                                </a>
                            <?php endif; ?>
                            
                            <a href="<?= base_url('warehouse/add_movement?product_id=' . $product['id']) ?>" class="btn btn-info">
                                <i class="fas fa-boxes me-1"></i> Управління запасами
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Информация о продукте -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Характеристики</h5>
            </div>
            <div class="card-body">
                <ul class="product-features fa-ul">
                    <li>
                        <span class="fa-li"><i class="fas fa-check-circle text-success"></i></span>
                        <strong>Категорія:</strong> <?= $product['category_name'] ?? 'Не вказано' ?>
                    </li>
                    <li>
                        <span class="fa-li"><i class="fas fa-check-circle text-success"></i></span>
                        <strong>Натуральність:</strong> 100% натуральний продукт
                    </li>
                    <li>
                        <span class="fa-li"><i class="fas fa-check-circle text-success"></i></span>
                        <strong>Доступні об'єми:</strong> 
                        <?php
                        $availableVolumes = array_filter($containers ?? [], function($container) {
                            return $container['is_active'] && $container['stock_quantity'] > 0;
                        });
                        if (!empty($availableVolumes)) {
                            $volumes = array_map(function($container) {
                                return $container['volume'] . ' л';
                            }, $availableVolumes);
                            echo implode(', ', $volumes);
                        } else {
                            echo '1 л (базовий)';
                        }
                        ?>
                    </li>
                    <li>
                        <span class="fa-li"><i class="fas fa-check-circle text-success"></i></span>
                        <strong>Термін придатності:</strong> 30 днів
                    </li>
                    <li>
                        <span class="fa-li"><i class="fas fa-check-circle text-success"></i></span>
                        <strong>Умови зберігання:</strong> в холодному місці при температурі від +2°C до +6°C
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($relatedProducts)): ?>
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="mb-4">Подібні товари</h3>
            
            <div class="row g-4">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                    <div class="col-md-3">
                        <div class="card related-product-card h-100">
                            <img src="<?= $relatedProduct['image'] ? upload_url($relatedProduct['image']) : asset_url('images/no-image.jpg') ?>" class="card-img-top" alt="<?= $relatedProduct['name'] ?>">
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= $relatedProduct['name'] ?></h5>
                                <p class="card-text text-muted"><?= mb_substr($relatedProduct['description'], 0, 60) ?>...</p>
                                
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-primary">
                                            від <?= number_format($relatedProduct['min_price'] ?? $relatedProduct['price'], 2) ?> грн.
                                        </span>
                                        <a href="<?= base_url('products/view/' . $relatedProduct['id']) ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i> Деталі
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
$(document).ready(function() {
    let selectedContainer = null;
    
    // Выбор объема тары
    $(".container-selector").on("click", function() {
        if ($(this).hasClass("out-of-stock")) {
            return;
        }
        
        $(".container-selector").removeClass("selected");
        $(this).addClass("selected");
        
        selectedContainer = $(this).data("container-id");
        
        // Обновляем скрытое поле с ID контейнера
        $("#selectedContainerId").val(selectedContainer);
        
        // Обновляем количество в форме
        $("#cartQuantity").val($("#quantity").val());
        
        $("#actionButtons").show();
        $("#addToCartForm").show();
    });
    
    // Обновление количества в форме при изменении
    $("#quantity").on("input", function() {
        $("#cartQuantity").val($(this).val());
    });
    
    // Обработка формы добавления в корзину
    $("#addToCartForm").on("submit", function(e) {
        if (!selectedContainer) {
            e.preventDefault();
            alert("Будь ласка, оберіть об\'єм тари");
            return false;
        }
        
        const quantity = parseInt($("#quantity").val());
        if (!quantity || quantity < 1) {
            e.preventDefault();
            alert("Будь ласка, вкажіть кількість");
            return false;
        }
        
        // Обновляем количество перед отправкой
        $("#cartQuantity").val(quantity);
        
        return true;
    });
});
</script>