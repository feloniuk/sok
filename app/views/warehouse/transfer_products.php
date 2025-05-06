<?php
// app/views/warehouse/transfer_products.php - Форма перенесення товарів між складами
$title = 'Перенесення товарів між складами';

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
    
    .transfer-icon {
        font-size: 2rem;
        color: #3498db;
    }
</style>';

// Підключення додаткових JS
$extra_js = '
<script>
    $(document).ready(function() {
        // Ініціалізація автозаповнення для вибору продукту
        $("#product_search").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "' . base_url("products/search") . '",
                    dataType: "json",
                    data: {
                        term: request.term
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
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
                
                // Оновлення максимальної кількості
                $("#maxQuantity").val(ui.item.stock);
                $("#quantity").attr("max", ui.item.stock);
                $("#quantityHelp").text(`Максимальна доступна кількість: ${ui.item.stock} шт.`);
                
                // Перевірка і оновлення складів, де є цей товар
                checkProductWarehouses(ui.item.id);
            }
        });
        
        // Функція для перевірки наявності товару на складах
        function checkProductWarehouses(productId) {
            $.ajax({
                url: "' . base_url("warehouse/getProductStock") . '",
                method: "GET",
                data: {
                    product_id: productId
                },
                dataType: "json",
                success: function(response) {
                    // В реальному додатку тут був би код для оновлення списку доступних складів
                    // Наразі просто дозволяємо вибрати з усіх складів
                }
            });
        }
        
        // Валідація вибору різних складів
        $("#to_warehouse_id").on("change", function() {
            const fromWarehouseId = $("#from_warehouse_id").val();
            const toWarehouseId = $(this).val();
            
            if (fromWarehouseId && toWarehouseId && fromWarehouseId === toWarehouseId) {
                alert("Склад-джерело і склад-приймач не можуть бути однаковими");
                $(this).val("");
            }
        });
        
        $("#from_warehouse_id").on("change", function() {
            const fromWarehouseId = $(this).val();
            const toWarehouseId = $("#to_warehouse_id").val();
            
            if (fromWarehouseId && toWarehouseId && fromWarehouseId === toWarehouseId) {
                alert("Склад-джерело і склад-приймач не можуть бути однаковими");
                $("#to_warehouse_id").val("");
            }
        });
    });
</script>';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('warehouse') ?>">Панель складу</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('warehouse/warehouses') ?>">Склади</a></li>
                <li class="breadcrumb-item active">Перенесення товарів</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card form-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-exchange-alt me-2"></i> Перенесення товарів між складами
                </h5>
            </div>
            
            <div class="card-body">
                <form action="<?= base_url('warehouse/store_transfer') ?>" method="POST" class="needs-validation" novalidate>
                    <?= csrf_field() ?>
                    
                    <!-- Вибір продукту -->
                    <div class="mb-4">
                        <label for="product_search" class="form-label">Вибір товару для перенесення <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= has_error('product_id') ? 'is-invalid' : '' ?>" id="product_search" 
                               placeholder="Почніть вводити назву товару..." required>
                        <input type="hidden" id="product_id" name="product_id" value="<?= old('product_id') ?>">
                        <?php if (has_error('product_id')): ?>
                            <div class="invalid-feedback"><?= get_error('product_id') ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Інформація про вибраний продукт -->
                    <div id="product_info" style="display: none;"></div>
                    <input type="hidden" id="maxQuantity" value="0">
                    
                    <div class="row mb-4">
                        <div class="col-md-5">
                            <!-- Склад-джерело -->
                            <div class="mb-3">
                                <label for="from_warehouse_id" class="form-label">Склад-джерело <span class="text-danger">*</span></label>
                                <select class="form-select <?= has_error('from_warehouse_id') ? 'is-invalid' : '' ?>" id="from_warehouse_id" name="from_warehouse_id" required>
                                    <option value="">Виберіть склад-джерело</option>
                                    <?php foreach ($warehouses ?? [] as $warehouse): ?>
                                        <option value="<?= $warehouse['id'] ?>" <?= old('from_warehouse_id') == $warehouse['id'] ? 'selected' : '' ?>>
                                            <?= $warehouse['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (has_error('from_warehouse_id')): ?>
                                    <div class="invalid-feedback"><?= get_error('from_warehouse_id') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-center justify-content-center">
                            <div class="transfer-icon">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </div>
                        
                        <div class="col-md-5">
                            <!-- Склад-приймач -->
                            <div class="mb-3">
                                <label for="to_warehouse_id" class="form-label">Склад-приймач <span class="text-danger">*</span></label>
                                <select class="form-select <?= has_error('to_warehouse_id') ? 'is-invalid' : '' ?>" id="to_warehouse_id" name="to_warehouse_id" required>
                                    <option value="">Виберіть склад-приймач</option>
                                    <?php foreach ($warehouses ?? [] as $warehouse): ?>
                                        <option value="<?= $warehouse['id'] ?>" <?= old('to_warehouse_id') == $warehouse['id'] ? 'selected' : '' ?>>
                                            <?= $warehouse['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (has_error('to_warehouse_id')): ?>
                                    <div class="invalid-feedback"><?= get_error('to_warehouse_id') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Кількість -->
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Кількість для перенесення <span class="text-danger">*</span></label>
                        <input type="number" class="form-control <?= has_error('quantity') ? 'is-invalid' : '' ?>" id="quantity" name="quantity" min="1" max="1000" value="<?= old('quantity', 1) ?>" required>
                        <?php if (has_error('quantity')): ?>
                            <div class="invalid-feedback"><?= get_error('quantity') ?></div>
                        <?php endif; ?>
                        <div id="quantityHelp" class="form-text">Вкажіть кількість товару для перенесення.</div>
                    </div>
                    
                    <!-- Примітки -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">Примітки</label>
                        <textarea class="form-control <?= has_error('notes') ? 'is-invalid' : '' ?>" id="notes" name="notes" rows="3" placeholder="Додаткова інформація про перенесення..."><?= old('notes') ?></textarea>
                        <?php if (has_error('notes')): ?>
                            <div class="