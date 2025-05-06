<?php
// app/views/warehouse/warehouse_inventory.php - Сторінка перегляду товарів на конкретному складі
$title = 'Товари на складі: ' . ($warehouse['name'] ?? '');

// Підключення додаткових CSS
$extra_css = '
<style>
    .inventory-table th, .inventory-table td {
        vertical-align: middle;
    }
    
    .stock-light {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
    
    .stock-high {
        background-color: #28a745;
    }
    
    .stock-medium {
        background-color: #ffc107;
    }
    
    .stock-low {
        background-color: #dc3545;
    }
    
    .product-image-mini {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
    }
</style>';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('warehouse') ?>">Панель складу</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('warehouse/warehouses') ?>">Склади</a></li>
                <li class="breadcrumb-item active"><?= $warehouse['name'] ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
        <p class="text-muted"><?= $warehouse['address'] ?></p>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <a href="<?= base_url('warehouse/add_movement') ?>" class="btn btn-success">
                <i class="fas fa-plus-circle me-1"></i> Додати товар
            </a>
            <a href="<?= base_url('warehouse/transfer_products') ?>" class="btn btn-info">
                <i class="fas fa-exchange-alt me-1"></i> Перенести товари
            </a>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold">Наявні товари</h6>
        <div class="dropdown">
            <button class="btn btn-light btn-sm dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-file-export me-1"></i> Експорт
            </button>
            <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                <li><a class="dropdown-item" href="<?= base_url('warehouse/export_inventory?warehouse_id=' . $warehouse['id'] . '&format=csv') ?>">CSV</a></li>
                <li><a class="dropdown-item" href="<?= base_url('warehouse/export_inventory?warehouse_id=' . $warehouse['id'] . '&format=excel') ?>">Excel</a></li>
                <li><a class="dropdown-item" href="<?= base_url('warehouse/export_inventory?warehouse_id=' . $warehouse['id'] . '&format=pdf') ?>">PDF</a></li>
            </ul>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($inventory)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> На цьому складі немає товарів.
            </div>
            <p class="text-center">
                <a href="<?= base_url('warehouse/add_movement?warehouse_id=' . $warehouse['id']) ?>" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-1"></i> Додати перший товар
                </a>
            </p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover inventory-table">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Фото</th>
                            <th>Назва</th>
                            <th>Категорія</th>
                            <th>Ціна (грн)</th>
                            <th>Кількість</th>
                            <th>Вартість</th>
                            <th>Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalQuantity = 0;
                        $totalValue = 0;
                        
                        foreach ($inventory as $item): 
                            $totalQuantity += $item['quantity'];
                            $totalValue += $item['price'] * $item['quantity'];
                        ?>
                            <tr>
                                <td><?= $item['product_id'] ?></td>
                                <td class="text-center">
                                    <img src="<?= $item['image'] ? upload_url($item['image']) : asset_url('images/no-image.jpg') ?>" 
                                         alt="<?= $item['product_name'] ?>" class="product-image-mini">
                                </td>
                                <td><?= $item['product_name'] ?></td>
                                <td><?= $item['category_name'] ? $item['category_name'] : '-' ?></td>
                                <td class="text-end"><?= number_format($item['price'], 2) ?></td>
                                <td class="text-center">
                                    <?php 
                                    $stockClass = 'stock-high';
                                    if ($item['quantity'] <= 5) {
                                        $stockClass = 'stock-low';
                                    } elseif ($item['quantity'] <= 10) {
                                        $stockClass = 'stock-medium';
                                    }
                                    ?>
                                    <span class="stock-light <?= $stockClass ?>"></span>
                                    <strong><?= $item['quantity'] ?></strong>
                                </td>
                                <td class="text-end"><?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('warehouse/add_movement?product_id=' . $item['product_id'] . '&warehouse_id=' . $warehouse['id']) ?>" class="btn btn-primary" title="Додати кількість">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                        <button type="button" class="btn btn-warning adjust-quantity" 
                                                data-bs-toggle="modal" data-bs-target="#adjustQuantityModal"
                                                data-product-id="<?= $item['product_id'] ?>" 
                                                data-product-name="<?= $item['product_name'] ?>"
                                                data-warehouse-id="<?= $warehouse['id'] ?>"
                                                data-quantity="<?= $item['quantity'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="<?= base_url('products/view/' . $item['product_id']) ?>" class="btn btn-info" title="Деталі товару">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-primary fw-bold">
                            <td colspan="5" class="text-end">Всього:</td>
                            <td class="text-center"><?= $totalQuantity ?></td>
                            <td class="text-end"><?= number_format($totalValue, 2) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Модальне вікно коригування кількості -->
<div class="modal fade" id="adjustQuantityModal" tabindex="-1" aria-labelledby="adjustQuantityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adjustQuantityModalLabel">Коригування кількості</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('warehouse/adjust_inventory') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" id="product_id" name="product_id">
                    <input type="hidden" id="warehouse_id" name="warehouse_id" value="<?= $warehouse['id'] ?>">
                    
                    <p>Коригування кількості товару: <strong id="product_name"></strong></p>
                    
                    <div class="mb-3">
                        <label for="new_quantity" class="form-label">Нова кількість</label>
                        <input type="number" class="form-control" id="new_quantity" name="new_quantity" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Примітки</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Вкажіть причину коригування"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                    <button type="submit" class="btn btn-primary">Зберегти</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Підключення додаткових JavaScript -->
<?php $extra_js = '
<script>
    $(document).ready(function() {
        // Ініціалізація модального вікна коригування кількості
        $(".adjust-quantity").on("click", function() {
            const productId = $(this).data("product-id");
            const productName = $(this).data("product-name");
            const warehouseId = $(this).data("warehouse-id");
            const quantity = $(this).data("quantity");
            
            $("#product_id").val(productId);
            $("#warehouse_id").val(warehouseId);
            $("#product_name").text(productName);
            $("#new_quantity").val(quantity);
        });
    });
</script>
'; ?>