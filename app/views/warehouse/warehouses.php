<?php
// app/views/warehouse/warehouses.php - Сторінка управління складами
$title = 'Управління складами';

// Підключення додаткових CSS
$extra_css = '
<style>
    .warehouse-card {
        transition: all 0.3s ease;
        overflow: hidden;
        height: 100%;
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .warehouse-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .warehouse-header {
        background-color: #f8f9fa;
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
    }
    
    .warehouse-icon {
        font-size: 2rem;
        color: #3498db;
        margin-right: 0.5rem;
    }
</style>';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
        <p class="text-muted">Управління складами та приміщеннями для зберігання товарів</p>
    </div>
    <?php if (has_role('admin')): ?>
        <div class="col-md-4 text-end">
            <a href="<?= base_url('warehouse/create_warehouse') ?>" class="btn btn-success">
                <i class="fas fa-plus-circle me-1"></i> Додати новий склад
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="row">
    <?php if (empty($warehouses)): ?>
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> Склади ще не створені.
            </div>
            <?php if (has_role('admin')): ?>
                <p class="text-center">
                    <a href="<?= base_url('warehouse/create_warehouse') ?>" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i> Створити перший склад
                    </a>
                </p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach ($warehouses as $warehouse): ?>
            <div class="col-lg-6 mb-4">
                <div class="card warehouse-card">
                    <div class="warehouse-header d-flex align-items-center">
                        <div class="warehouse-icon">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <div>
                            <h5 class="mb-0"><?= $warehouse['name'] ?></h5>
                            <small class="text-muted">
                                <?= $warehouse['manager_id'] ? 'Менеджер: ' . $warehouse['first_name'] . ' ' . $warehouse['last_name'] : 'Менеджер не призначений' ?>
                            </small>
                        </div>
                    </div>
                    <div class="card-body">
                        <p><strong>Адреса:</strong> <?= $warehouse['address'] ?></p>
                        
                        <div class="d-flex justify-content-between mt-3">
                            <a href="<?= base_url('warehouse/view_warehouse_inventory/' . $warehouse['id']) ?>" class="btn btn-info">
                                <i class="fas fa-boxes me-1"></i> Перегляд товарів
                            </a>
                            
                            <?php if (has_role('admin')): ?>
                                <div class="btn-group">
                                    <a href="<?= base_url('warehouse/edit_warehouse/' . $warehouse['id']) ?>" class="btn btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= base_url('warehouse/delete_warehouse/' . $warehouse['id']) ?>" class="btn btn-danger confirm-delete" data-item-name="склад '<?= $warehouse['name'] ?>'">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="card shadow mt-4">
    <div class="card-header bg-primary text-white">
        <h5 class="m-0 font-weight-bold">Швидкі дії</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-3">
                <a href="<?= base_url('warehouse/transfer_products') ?>" class="btn btn-info btn-lg w-100 d-flex align-items-center justify-content-center">
                    <i class="fas fa-exchange-alt me-2"></i> Перенесення товарів
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="<?= base_url('warehouse/inventory') ?>" class="btn btn-primary btn-lg w-100 d-flex align-items-center justify-content-center">
                    <i class="fas fa-clipboard-list me-2"></i> Інвентаризація
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="<?= base_url('warehouse/stocktaking') ?>" class="btn btn-warning btn-lg w-100 d-flex align-items-center justify-content-center">
                    <i class="fas fa-tasks me-2"></i> Перевірка наявності
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="<?= base_url('warehouse/reports') ?>" class="btn btn-success btn-lg w-100 d-flex align-items-center justify-content-center">
                    <i class="fas fa-chart-bar me-2"></i> Звіти по складах
                </a>
            </div>
        </div>
    </div>
</div>