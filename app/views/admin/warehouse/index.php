<?php
// app/views/admin/warehouse/index.php
$title = 'Управління складом';

// Дополнительные CSS стили
$extra_css = '
<style>
    .dashboard-card {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: none;
        margin-bottom: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .stat-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        color: var(--primary);
    }
    
    .stat-value {
        font-size: 1.75rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: var(--gray);
    }
    
    .inventory-table img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        margin-bottom: 20px;
    }
</style>';

// Дополнительные JS скрипты
$extra_js = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // График движения товаров
    const movementsCtx = document.getElementById("movementsChart").getContext("2d");
    new Chart(movementsCtx, {
        type: "line",
        data: {
            labels: ' . json_encode(array_column($warehouseStats['movementsByDay'] ?? [], 'day')) . ',
            datasets: [
                {
                    label: "Прихід",
                    data: ' . json_encode(array_column($warehouseStats['movementsByDay'] ?? [], 'incoming')) . ',
                    borderColor: "#28a745",
                    backgroundColor: "rgba(40, 167, 69, 0.1)",
                    tension: 0.3,
                    fill: true
                },
                {
                    label: "Витрати",
                    data: ' . json_encode(array_column($warehouseStats['movementsByDay'] ?? [], 'outgoing')) . ',
                    borderColor: "#dc3545",
                    backgroundColor: "rgba(220, 53, 69, 0.1)",
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: "Рух товарів за останні 14 днів"
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: "Кількість одиниць"
                    }
                }
            }
        }
    });
    
    // График запасов по категориям
    const categoryInventoryCtx = document.getElementById("categoryInventoryChart").getContext("2d");
    new Chart(categoryInventoryCtx, {
        type: "doughnut",
        data: {
            labels: ' . json_encode(array_column($warehouseStats['inventoryByCategory'] ?? [], 'category_name')) . ',
            datasets: [{
                data: ' . json_encode(array_column($warehouseStats['inventoryByCategory'] ?? [], 'quantity')) . ',
                backgroundColor: [
                    "#4e73df", "#1cc88a", "#36b9cc", "#f6c23e", "#e74a3b", 
                    "#5a5c69", "#6610f2", "#6f42c1", "#e83e8c", "#fd7e14"
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: "Запаси за категоріями"
                },
                legend: {
                    position: "right"
                }
            }
        }
    });
    
    // График запасов по складам
    const warehouseInventoryCtx = document.getElementById("warehouseInventoryChart").getContext("2d");
    new Chart(warehouseInventoryCtx, {
        type: "bar",
        data: {
            labels: ' . json_encode(array_column($warehouseStats['inventoryByWarehouse'] ?? [], 'warehouse_name')) . ',
            datasets: [{
                label: "Кількість товарів",
                data: ' . json_encode(array_column($warehouseStats['inventoryByWarehouse'] ?? [], 'quantity')) . ',
                backgroundColor: "#4e73df"
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: "Запаси за складами"
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: "Кількість одиниць"
                    }
                }
            }
        }
    });
});
</script>';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h2 mb-0"><?= $title ?></h1>
        <p class="text-muted">Загальний огляд стану складу та руху товарів</p>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <a href="<?= base_url('admin/warehouse/inventory') ?>" class="btn btn-primary">
                <i class="fas fa-boxes me-1"></i> Інвентаризація
            </a>
            <a href="<?= base_url('admin/warehouse/add_movement') ?>" class="btn btn-success">
                <i class="fas fa-exchange-alt me-1"></i> Додати рух
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="dashboard-card">
            <div class="card-body text-center">
                <div class="stat-icon">
                    <i class="fas fa-box text-primary"></i>
                </div>
                <div class="stat-value"><?= number_format($warehouseStats['totalInventory'] ?? 0) ?></div>
                <div class="stat-label">Загальна кількість товарів</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card">
            <div class="card-body text-center">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave text-success"></i>
                </div>
                <div class="stat-value"><?= number_format($warehouseStats['totalValue'] ?? 0, 2) ?> грн</div>
                <div class="stat-label">Загальна вартість запасів</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card">
            <div class="card-body text-center">
                <div class="stat-icon">
                    <i class="fas fa-arrow-circle-up text-info"></i>
                </div>
                <div class="stat-value"><?= number_format($warehouseStats['todayIncoming'] ?? 0) ?></div>
                <div class="stat-label">Надходження сьогодні</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card">
            <div class="card-body text-center">
                <div class="stat-icon">
                    <i class="fas fa-arrow-circle-down text-danger"></i>
                </div>
                <div class="stat-value"><?= number_format($warehouseStats['todayOutgoing'] ?? 0) ?></div>
                <div class="stat-label">Витрати сьогодні</div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="dashboard-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Динаміка руху товарів</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="movementsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Запаси за категоріями</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="categoryInventoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="dashboard-card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Запаси за складами</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="warehouseInventoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="dashboard-card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Продукти з низьким запасом</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover inventory-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;"></th>
                                <th>Продукт</th>
                                <th>Категорія</th>
                                <th>Запаси</th>
                                <th>Дії</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Получение продуктов с низким запасом (менее 10 единиц)
                            $lowStockProducts = [];
                            $productModel = new Product();
                            $lowStockProducts = $productModel->getLowStockProducts(10);
                            
                            if (empty($lowStockProducts)): 
                            ?>
                                <tr>
                                    <td colspan="5" class="text-center">Немає продуктів з низьким запасом</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($lowStockProducts as $product): ?>
                                    <tr>
                                        <td>
                                            <img src="<?= $product['image'] ? upload_url($product['image']) : asset_url('images/no-image.jpg') ?>" alt="<?= $product['name'] ?>">
                                        </td>
                                        <td><?= $product['name'] ?></td>
                                        <td>
                                            <?php
                                            $categoryName = 'Немає категорії';
                                            if (!empty($product['category_id'])) {
                                                $categoryModel = new Category();
                                                $category = $categoryModel->getById($product['category_id']);
                                                $categoryName = $category ? $category['name'] : 'Немає категорії';
                                            }
                                            echo $categoryName;
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $product['stock_quantity'] > 5 ? 'warning' : 'danger' ?>">
                                                <?= $product['stock_quantity'] ?> шт.
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('admin/warehouse/add_movement?product_id=' . $product['id']) ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-plus-circle"></i> Додати
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="dashboard-card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Останні рухи товарів</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Продукт</th>
                                <th>Склад</th>
                                <th>Тип руху</th>
                                <th>Кількість</th>
                                <th>Примітки</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Получение последних движений товаров
                            $recentMovements = [];
                            $inventoryMovementModel = new InventoryMovement();
                            $recentMovements = $inventoryMovementModel->getRecent(10);
                            
                            if (empty($recentMovements)): 
                            ?>
                                <tr>
                                    <td colspan="6" class="text-center">Немає даних про рух товарів</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentMovements as $movement): ?>
                                    <tr>
                                        <td><?= date('d.m.Y H:i', strtotime($movement['created_at'])) ?></td>
                                        <td><?= $movement['product_name'] ?></td>
                                        <td><?= $movement['warehouse_name'] ?></td>
                                        <td>
                                            <?php if ($movement['movement_type'] == 'incoming'): ?>
                                                <span class="badge bg-success">Надходження</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Витрата</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= abs($movement['quantity']) ?> шт.</td>
                                        <td><?= $movement['notes'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="<?= base_url('admin/warehouse/movements') ?>" class="btn btn-outline-primary">
                        <i class="fas fa-list me-1"></i> Всі рухи товарів
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>