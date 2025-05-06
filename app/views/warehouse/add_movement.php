<?php
// app/views/warehouse/add_movement.php - Сторінка додавання руху товару
$title = 'Додавання руху товару';

// Підключення додаткових CSS
$extra_css = '
<style>
    .form-card {
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .product-info-card {
        border-radius: 0.5rem;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .product-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 0.25rem;
    }
</style>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
';

// Підключення додаткових JS
$extra_js = '
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
    $(document).ready(function() {
        // Ініціалізація автозаповнення для вибору продукту
        $("#product_search").autocomplete({
            source: "' . base_url("products/search") . '",
            minLength: 2,
            select: function(event, ui) {
                $("#product_id").val(ui.item.id);
                $("#product_info").html(`
                    <div class="card product-info-card mt-3 mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <img src="${ui.item.image}" alt="${ui.item.value}" class="product-image me-3">
                                <div>
                                    <h5 class="card-title mb-1">${ui.item.value}</h5>
                                    <p class="card-text mb-1">
                                        <strong>Ціна:</strong> ${parseFloat(ui.item.price).toFixed(2)} грн.
                                    </p>
                                    <p class="card-text mb-0">
                                        <strong>Наявність:</strong> ${ui.item.stock} шт.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                $("#product_info").show();
                
                // Оновлення максимальної кількості для відвантаження
                $("#maxQuantity").val(ui.item.stock);
            }
        });
        
        // Зміна типу руху товару
        $("#movement_type").on("change", function() {
            const type = $(this).val();
            
            if (type === "outgoing") {
                $("#quantityLabel").text("Кількість (буде списано):");
                const maxQuantity = $("#maxQuantity").val();
                $("#quantity").attr("max", maxQuantity);
                $("#quantityHelp").text(`Максимальна кількість для списання: ${maxQuantity} шт.`);
            } else {
                $("#quantityLabel").text("Кількість:");
                $("#quantity").removeAttr("max");
                $("#quantityHelp").text("Вкажіть кількість товару.");
            }
        });
    });
</script>';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card form-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-boxes me-2"></i> Додавання руху товару
                </h5>
            </div>
            
            <div class="card-body">
                <form action="<?= base_url('warehouse/store_movement') ?>" method="POST" class="needs-validation" novalidate>
                    <?= csrf_field() ?>
                    
                    <!-- Вибір продукту -->
                    <div class="mb-3">
                        <label for="product_search" class="form-label">Пошук продукту <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= has_error('product_id') ? 'is-invalid' : '' ?>" id="product_search" 
                               placeholder="Почніть вводити назву продукту..." 
                               value="<?= isset($product) ? $product['name'] : '' ?>" 
                               required>
                        <input type="hidden" id="product_id" name="product_id" value="<?= isset($product) ? $product['id'] : '' ?>">
                        <?php if (has_error('product_id')): ?>
                            <div class="invalid-feedback"><?= get_error('product_id') ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Інформація про вибраний продукт -->
                    <div id="product_info" style="<?= isset($product) ? '' : 'display: none;' ?>">
                        <?php if (isset($product)): ?>
                            <div class="card product-info-card mt-3 mb-3">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <img src="<?= $product['image'] ? upload_url($product['image']) : asset_url('images/no-image.jpg') ?>" alt="<?= $product['name'] ?>" class="product-image me-3">
                                        <div>
                                            <h5 class="card-title mb-1"><?= $product['name'] ?></h5>
                                            <p class="card-text mb-1">
                                                <strong>Ціна:</strong> <?= number_format($product['price'], 2) ?> грн.
                                            </p>
                                            <p class="card-text mb-0">
                                                <strong>Наявність:</strong> <span id="availableStock"><?= $product['stock_quantity'] ?> шт.</span>
                                                <input type="hidden" id="maxQuantity" value="<?= $product['stock_quantity'] ?>">
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Тип руху -->
                            <div class="mb-3">
                                <label for="movement_type" class="form-label">Тип руху <span class="text-danger">*</span></label>
                                <select class="form-select <?= has_error('movement_type') ? 'is-invalid' : '' ?>" id="movement_type" name="movement_type" required>
                                    <option value="incoming" selected>Надходження</option>
                                    <option value="outgoing">Витрата</option>
                                    <option value="adjustment">Коригування (інвентаризація)</option>
                                </select>
                                <?php if (has_error('movement_type')): ?>
                                    <div class="invalid-feedback"><?= get_error('movement_type') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <!-- Склад -->
                            <div class="mb-3">
                                <label for="warehouse_id" class="form-label">Склад <span class="text-danger">*</span></label>
                                <select class="form-select <?= has_error('warehouse_id') ? 'is-invalid' : '' ?>" id="warehouse_id" name="warehouse_id" required>
                                    <?php foreach ($warehouses ?? [] as $warehouse): ?>
                                        <option value="<?= $warehouse['id'] ?>"><?= $warehouse['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (has_error('warehouse_id')): ?>
                                    <div class="invalid-feedback"><?= get_error('warehouse_id') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Кількість -->
                    <div class="mb-3">
                        <label for="quantity" id="quantityLabel" class="form-label">Кількість <span class="text-danger">*</span></label>
                        <input type="number" class="form-control <?= has_error('quantity') ? 'is-invalid' : '' ?>" id="quantity" name="quantity" min="1" step="1" required>
                        <?php if (has_error('quantity')): ?>
                            <div class="invalid-feedback"><?= get_error('quantity') ?></div>
                        <?php endif; ?>
                        <div id="quantityHelp" class="form-text">Вкажіть кількість товару.</div>
                    </div>
                    
                    <!-- Примітки -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">Примітки</label>
                        <textarea class="form-control <?= has_error('notes') ? 'is-invalid' : '' ?>" id="notes" name="notes" rows="3" placeholder="Додаткова інформація про рух товару..."><?= old('notes') ?></textarea>
                        <?php if (has_error('notes')): ?>
                            <div class="invalid-feedback"><?= get_error('notes') ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?= base_url('warehouse/movements') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Назад до списку рухів
                        </a>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Зберегти
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>