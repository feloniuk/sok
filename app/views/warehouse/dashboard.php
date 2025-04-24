<?php
// app/views/warehouse/dashboard.php - Панель менеджера склада
$title = 'Панель керування складом';

// Подключение дополнительных CSS
$extra_css = '
<style>
    .stat-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    .stat-icon {
        font-size: 2.5rem;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
</style>';

// Подключение дополнительных JS
$extra_js = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Диаграмма запасов по категориям
        const inventoryCtx = document.getElementById("inventoryByCategoryChart").getContext("2d");
        new Chart(inventoryCtx, {
            type: "doughnut",
            data: {
                labels: ' . json_encode(array_column($warehouseStats['inventoryByCategory'] ?? [], 'category_name')) . ',
                datasets: [{
                    data: ' . json_encode(array_column($warehouseStats['inventoryByCategory'] ?? [], 'quantity')) . ',
                    backgroundColor: [
                        "#4e73df", "#1cc88a", "#36b9cc", 
                        "#f6c23e", "#e74a3b", "#5a5c69",
                        "#6610f2", "#6f42c1", "#e83e8c", 
                        "#fd7e14"
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: "Розподіл запасів за категоріями"
                    }
                },
                cutout: "60%"
            }
        });
        
        // Диаграмма запасов по складам
        const warehouseCtx = document.getElementById("inventoryByWarehouseChart").getContext("2d");
        new Chart(warehouseCtx, {
            type: "bar",
            data: {
                labels: ' . json_encode(array_column($warehouseStats['inventoryByWarehouse'] ?? [], 'warehouse_name')) . ',
                datasets: [{
                    label: "Кількість одиниць",
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
                        text: "Розподіл запасів за складами"
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
        
        // Диаграмма движений товаров
        const movementsCtx = document.getElementById("movementsChart").getContext("2d");
        new Chart(movementsCtx, {
            type: "line",
            data: {
                labels: ' . json_encode(array_column($warehouseStats['movementsByDay'] ?? [], 'day')) . ',
                datasets: [
                    {
                        label: "Надходження",
                        data: ' . json_encode(array_column($warehouseStats['movementsByDay'] ?? [], 'incoming')) . ',
                        borderColor: "#1cc88a",
                        backgroundColor: "rgba(28, 200, 138, 0.1)",
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: "Витрати",
                        data: ' . json_encode(array_map(function($item) { return abs($item['outgoing']); }, $warehouseStats['movementsByDay'] ?? [])) . ',
                        borderColor: "#e74a3b",
                        backgroundColor: "rgba(231, 74, 59, 0.1)",
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
    });
</script>';
?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-left-primary shadow h-100 py-2 stat-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Всього товарів на складі
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($warehouseStats['totalInventory'] ?? 0) ?> од.
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-icon bg-primary text-white">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-left-success shadow h-100 py-2 stat-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Загальна вартість запасів
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($warehouseStats['totalValue'] ?? 0, 2) ?> грн.
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-icon bg-success text-white">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-left-info shadow h-100 py-2 stat-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Надходження сьогодні
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($warehouseStats['todayIncoming'] ?? 0) ?> од.
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-icon bg-info text-white">
                            <i class="fas fa-arrow-circle-down"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-left-warning shadow h-100 py-2 stat-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Відвантаження сьогодні
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format(abs($warehouseStats['todayOutgoing'] ?? 0)) ?> од.
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-icon bg-warning text-white">
                            <i class="fas fa-arrow-circle-up"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Товары с низким запасом -->
    <div class="col-md-12 mb-4">
        <div class="card shadow">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Товари з низьким запасом</h6>
                <a href="<?= base_url('warehouse/add_movement') ?>" class="btn btn-sm btn-light">
                    <i class="fas fa-plus me-1"></i> Додати надходження
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Назва</th>
                                <th>Залишок</th>
                                <th>Ціна</th>
                                <th>Загальна вартість</th>
                                <th>Дії</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockProducts ?? [] as $product): ?>
                                <tr>
                                    <td><?= $product['id'] ?></td>
                                    <td><?= $product['name'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $product['stock_quantity'] <= 5 ? 'danger' : 'warning' ?>">
                                            <?= $product['stock_quantity'] ?>
                                        </span>
                                    </td>
                                    <td><?= number_format($product['price'], 2) ?> грн.</td>
                                    <td><?= number_format($product['price'] * $product['stock_quantity'], 2) ?> грн.</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= base_url('warehouse/add_movement?product_id=' . $product['id']) ?>" class="btn btn-outline-success" title="Додати надходження">
                                                <i class="fas fa-plus-circle"></i>
                                            </a>
                                            <a href="<?= base_url('products/edit/' . $product['id']) ?>" class="btn btn-outline-primary" title="Редагувати товар">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($lowStockProducts)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Всі товари мають достатній запас</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="<?= base_url('warehouse/inventory') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-boxes me-1"></i> Управління запасами
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- График запасов по категориям -->
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h6 class="m-0 font-weight-bold">Розподіл запасів за категоріями</h6>
            </div>
            <div class="card-body">
                <div class="chart-area" style="height: 300px;">
                    <canvas id="inventoryByCategoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- График запасов по складам -->
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h6 class="m-0 font-weight-bold">Розподіл запасів за складами</h6>
            </div>
            <div class="card-body">
                <div class="chart-area" style="height: 300px;">
                    <canvas id="inventoryByWarehouseChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- График движений товаров -->
    <div class="col-md-12 mb-4">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h6 class="m-0 font-weight-bold">Рух товарів за останні 14 днів</h6>
            </div>
            <div class="card-body">
                <div class="chart-area" style="height: 300px;">
                    <canvas id="movementsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Последние движения товаров -->
    <div class="col-md-12 mb-4">
        <div class="card shadow">
            <div class="card-header bg-secondary text-white">
                <h6 class="m-0 font-weight-bold">Останні операції з товарами</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Продукт</th>
                                <th>Склад</th>
                                <th>Тип операції</th>
                                <th>Кількість</th>
                                <th>Виконавець</th>
                                <th>Дата</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentMovements ?? [] as $movement): ?>
                                <tr>
                                    <td><?= $movement['id'] ?></td>
                                    <td><?= $movement['product_name'] ?></td>
                                    <td><?= $movement['warehouse_name'] ?></td>
                                    <td>
                                        <?php 
                                            $typeClass = 'secondary';
                                            $typeText = $movement['movement_type'];
                                            
                                            if ($movement['movement_type'] == 'incoming') {
                                                $typeClass = 'success';
                                                $typeText = 'Надходження';
                                            } elseif ($movement['movement_type'] == 'outgoing') {
                                                $typeClass = 'danger';
                                                $typeText = 'Витрата';
                                            } elseif ($movement['movement_type'] == 'adjustment') {
                                                $typeClass = 'warning';
                                                $typeText = 'Коригування';
                                            }
                                        ?>
                                        <span class="badge bg-<?= $typeClass ?>"><?= $typeText ?></span>
                                    </td>
                                    <td class="text-<?= $movement['quantity'] > 0 ? 'success' : 'danger' ?>">
                                        <?= $movement['quantity'] > 0 ? '+' . $movement['quantity'] : $movement['quantity'] ?>
                                    </td>
                                    <td><?= $movement['first_name'] . ' ' . $movement['last_name'] ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($movement['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentMovements)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Операцій з товарами не знайдено</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="<?= base_url('warehouse/movements') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-history me-1"></i> Переглянути всі операції
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Кнопки быстрого доступа -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <h6 class="m-0 font-weight-bold">Швидкі дії</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('warehouse/add_movement') ?>" class="btn btn-success btn-lg w-100 d-flex align-items-center justify-content-center">
                            <i class="fas fa-plus-circle me-2"></i> Надходження товару
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('warehouse/inventory') ?>" class="btn btn-primary btn-lg w-100 d-flex align-items-center justify-content-center">
                            <i class="fas fa-boxes me-2"></i> Інвентаризація
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('products/create') ?>" class="btn btn-info btn-lg w-100 d-flex align-items-center justify-content-center">
                            <i class="fas fa-box-open me-2"></i> Новий продукт
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('reports/inventory') ?>" class="btn btn-warning btn-lg w-100 d-flex align-items-center justify-content-center">
                            <i class="fas fa-file-alt me-2"></i> Звіт по запасах
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>