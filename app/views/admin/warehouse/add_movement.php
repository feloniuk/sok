<?php
// app/views/admin/warehouse/add_movement.php
$title = 'Додавання руху товару';

// Дополнительные CSS стили
$extra_css = '
<style>
    .form-card {
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .product-image-preview {
        max-height: 150px;
        border-radius: 0.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .product-info {
        padding: 1rem;
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        display: none;
    }
</style>';

// Дополнительные JS скрипты
$extra_js = '
<script>
$(document).ready(function() {
    // При изменении продукта
    $("#product_id").on("change", function() {
        const productId = $(this).val();
        
        if (productId) {
            // Очистка предыдущей информации
            $("#productInfo").hide();
            
            // Получение информации о продукте и его запасах
            $.ajax({
                url: "' . base_url('admin/warehouse/get_product_stock') . '",
                type: "GET",
                data: { product_id: productId },
                dataType: "json",
                success: function(response) {
                    // Заполнение информации о продукте
                    $("#productName").text(response.name);
                    $("#productStock").text(response.stock);
                    
                    // Отображение блока информации
                    $("#productInfo").show();
                    
                    // Установка максимального значения для отгрузки
                    $("#quantity").attr("max", response.stock);
                },
                error: function() {
                    alert("Помилка при отриманні інформації про продукт");
                }
            });
        }
    });
    
    // При изменении типа движения
    $("#movement_type").on("change", function() {
        const movementType = $(this).val();
        const quantityInput = $("#quantity");
        
        if (movementType === "incoming") {
            // Для прихода не ограничиваем максимальное количество
            quantityInput.removeAttr("max");
        } else {
            // Для расхода устанавливаем максимальное количество равное текущему запасу
            const currentStock = parseInt($("#productStock").text());
            quantityInput.attr("max", currentStock);
        }
    });
    
    // Валидация формы перед отправкой
    $("#movementForm").on("submit", function(e) {
        const productId = $("#product_id").val();
        const quantity = $("#quantity").val();
        const movementType = $("#movement_type").val();
        
        if (!productId) {
            alert("Виберіть продукт");
            e.preventDefault();
            return false;
        }
        
        if (!quantity || quantity <= 0) {
            alert("Введіть коректну кількість");
            e.preventDefault();
            return false;
        }
        
        if (movementType === "outgoing") {
            const currentStock = parseInt($("#productStock").text());
            if (parseInt(quantity) > currentStock) {
                alert("Недостатньо товару на складі. Доступно: " + currentStock);
                e.preventDefault();
                return false;
            }
        }
        
        return true;
    });
    
    // Автоматически выбираем продукт, если он указан в URL
    <?php if (isset($product) && $product): ?>
        $("#product_id").val("<?= $product[\'id\'] ?>").trigger("change");
    <?php endif; ?>
});
</script>';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('admin/warehouse') ?>">Склад</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('admin/warehouse/movements') ?>">Рух товарів</a></li>
                <li class="breadcrumb-item active">Додавання руху</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card form-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-exchange-alt me-2"></i> <?= $title ?>
                </h5>
            </div>
            
            <div class="card-body">
                <!-- Информация о продукте -->
                <div id="productInfo" class="product-info">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="mb-3">Інформація про продукт</h5>
                            <p><strong>Назва:</strong> <span id="productName"></span></p>
                            <p><strong>Поточний запас:</strong> <span id="productStock"></span> шт.</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <?php if (isset($product) && $product && $product['image']): ?>
                                <img src="<?= upload_url($product['image']) ?>" alt="<?= $product['name'] ?>" class="product-image-preview">
                            <?php else: ?>
                                <img src="<?= asset_url('images/no-image.jpg') ?>" alt="Зображення продукту" class="product-image-preview" id="productImage">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <form id="movementForm" action="<?= base_url('admin/warehouse/store_movement') ?>" method="POST">
                    <?= csrf_field() ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <!-- Продукт -->
                            <div class="mb-3">
                                <label for="product_id" class="form-label">Продукт <span class="text-danger">*</span></label>
                                <select class="form-select <?= has_error('product_id') ? 'is-invalid' : '' ?>" id="product_id" name="product_id" required>
                                    <option value="">Виберіть продукт</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?= $p['id'] ?>" <?= old('product_id', $product['id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                            <?= $p['name'] ?> (<?= $p['stock_quantity'] ?> шт.)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (has_error('product_id')): ?>
                                    <div class="invalid-feedback"><?= get_error('product_id') ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Склад -->
                            <div class="mb-3">
                                <label for="warehouse_id" class="form-label">Склад <span class="text-danger">*</span></label>
                                <select class="form-select <?= has_error('warehouse_id') ? 'is-invalid' : '' ?>" id="warehouse_id" name="warehouse_id" required>
                                    <?php foreach ($warehouses as $warehouse): ?>
                                        <option value="<?= $warehouse['id'] ?>" <?= old('warehouse_id', 1) == $warehouse['id'] ? 'selected' : '' ?>>
                                            <?= $warehouse['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (has_error('warehouse_id')): ?>
                                    <div class="invalid-feedback"><?= get_error('warehouse_id') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <!-- Тип движения -->
                            <div class="mb-3">
                                <label for="movement_type" class="form-label">Тип руху <span class="text-danger">*</span></label>
                                <select class="form-select <?= has_error('movement_type') ? 'is-invalid' : '' ?>" id="movement_type" name="movement_type" required>
                                    <option value="incoming" <?= old('movement_type', 'incoming') == 'incoming' ? 'selected' : '' ?>>Надходження</option>
                                    <option value="outgoing" <?= old('movement_type', '') == 'outgoing' ? 'selected' : '' ?>>Витрата</option>
                                </select>
                                <?php if (has_error('movement_type')): ?>
                                    <div class="invalid-feedback"><?= get_error('movement_type') ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Количество -->
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Кількість <span class="text-danger">*</span></label>
                                <input type="number" class="form-control <?= has_error('quantity') ? 'is-invalid' : '' ?>" id="quantity" name="quantity" value="<?= old('quantity', 1) ?>" min="1" required>
                                <?php if (has_error('quantity')): ?>
                                    <div class="invalid-feedback"><?= get_error('quantity') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Примечание -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">Примітки</label>
                        <textarea class="form-control <?= has_error('notes') ? 'is-invalid' : '' ?>" id="notes" name="notes" rows="3"><?= old('notes', '') ?></textarea>
                        <?php if (has_error('notes')): ?>
                            <div class="invalid-feedback"><?= get_error('notes') ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?= base_url('admin/warehouse/movements') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Назад до списку
                        </a>
                        
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus-circle me-1"></i> Додати рух товару
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>