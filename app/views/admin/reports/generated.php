<?php
// app/views/admin/reports/generated.php
$title = $reportTitle ?? 'Звіт';

// Функции для преобразования статусов и форматирования данных
function getStatusName($status) {
    $statusNames = [
        'pending' => 'Очікує',
        'processing' => 'Обробляється',
        'shipped' => 'Відправлено',
        'delivered' => 'Доставлено',
        'cancelled' => 'Скасовано'
    ];
    return $statusNames[$status] ?? $status;
}

function getStatusClass($status) {
    $statusClasses = [
        'pending' => 'warning',
        'processing' => 'info',
        'shipped' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'danger'
    ];
    return $statusClasses[$status] ?? 'secondary';
}

// Дополнительные CSS стили для отчета
$extra_css = '
<style>
    .report-header {
        margin-bottom: 2rem;
    }
    
    .report-section {
        margin-bottom: 2rem;
    }
    
    .report-title {
        border-bottom: 2px solid #f8f9fa;
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
    }
    
    .stat-card {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: none;
        margin-bottom: 1rem;
    }
    
    .stat-icon {
        font-size: 2rem;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        margin-bottom: 20px;
    }
    
    @media print {
        body {
            font-size: 12pt;
        }
        
        .no-print {
            display: none !important;
        }
        
        .card {
            border: 1px solid #ddd;
            box-shadow: none !important;
        }
        
        .container {
            width: 100%;
            max-width: 100%;
        }
    }
</style>';

// Дополнительные JS скрипты для графиков
$extra_js = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Инициализация графиков в зависимости от типа отчета
    const reportType = "' . ($reportType ?? '') . '";
    
    if (reportType === "sales" && document.getElementById("salesChart")) {
        const salesCtx = document.getElementById("salesChart").getContext("2d");
        new Chart(salesCtx, {
            type: "line",
            data: {
                labels: ' . (isset($reportData['daily']) ? json_encode(array_column($reportData['daily'], 'date')) : '[]') . ',
                datasets: [{
                    label: "Виручка",
                    data: ' . (isset($reportData['daily']) ? json_encode(array_column($reportData['daily'], 'revenue')) : '[]') . ',
                    borderColor: "#4e73df",
                    backgroundColor: "rgba(78, 115, 223, 0.1)",
                    tension: 0.3,
                    fill: true
                }, {
                    label: "Прибуток",
                    data: ' . (isset($reportData['daily']) ? json_encode(array_column($reportData['daily'], 'profit')) : '[]') . ',
                    borderColor: "#1cc88a",
                    backgroundColor: "rgba(28, 200, 138, 0.1)",
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: "Сума (грн)"
                        }
                    }
                }
            }
        });
        
        const categoryCtx = document.getElementById("categoryChart").getContext("2d");
        new Chart(categoryCtx, {
            type: "doughnut",
            data: {
                labels: ' . (isset($reportData['categories']) ? json_encode(array_column($reportData['categories'], 'category_name')) : '[]') . ',
                datasets: [{
                    data: ' . (isset($reportData['categories']) ? json_encode(array_column($reportData['categories'], 'revenue')) : '[]') . ',
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
                    legend: {
                        position: "right"
                    }
                }
            }
        });
        
        const productCtx = document.getElementById("productChart").getContext("2d");
        new Chart(productCtx, {
            type: "bar",
            data: {
                labels: ' . (isset($reportData['products']) ? json_encode(array_column($reportData['products'], 'product_name')) : '[]') . ',
                datasets: [{
                    label: "Кількість продажів",
                    data: ' . (isset($reportData['products']) ? json_encode(array_column($reportData['products'], 'total_sold')) : '[]') . ',
                    backgroundColor: "#4e73df"
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: "Кількість"
                        }
                    }
                }
            }
        });
    }
    
    // Функционал печати отчета
    $("#printReport").on("click", function() {
        window.print();
    });
});
</script>';
?>

<div class="row mb-4 no-print">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Головна</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('reports') ?>">Звіти</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('reports/generate') ?>">Генерація звіту</a></li>
                <li class="breadcrumb-item active"><?= $reportTitle ?></li>
            </ol>
        </nav>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <button id="printReport" class="btn btn-outline-primary">
                <i class="fas fa-print me-1"></i> Друк
            </button>
            <?php if (isset($filter['format']) && $filter['format'] != 'html'): ?>
                <a href="<?= current_url() ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-download me-1"></i> Завантажити
                </a>
            <?php endif; ?>
            <a href="<?= base_url('reports/generate') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-cog me-1"></i> Змінити параметри
            </a>
        </div>
    </div>
</div>

<!-- Заголовок отчета -->
<div class="report-header">
    <h1 class="h2"><?= $reportTitle ?></h1>
    <p class="text-muted">
        Період: <?= date('d.m.Y', strtotime($filter['start_date'] ?? date('Y-m-d', strtotime('-1 month')))) ?> - 
        <?= date('d.m.Y', strtotime($filter['end_date'] ?? date('Y-m-d'))) ?>
        <?php if (isset($filter['category_id']) && !empty($filter['category_id'])): ?>
            <?php 
            $categoryName = 'Всі категорії';
            foreach ($categories as $category) {
                if ($category['id'] == $filter['category_id']) {
                    $categoryName = $category['name'];
                    break;
                }
            }
            ?>
            | Категорія: <?= $categoryName ?>
        <?php endif; ?>
        <?php if (isset($filter['status']) && !empty($filter['status'])): ?>
            | Статус: <?= getStatusName($filter['status']) ?>
        <?php endif; ?>
    </p>
</div>

<?php if ($reportType == 'sales'): ?>
<!-- Отчет по продажам -->
<div class="report-section">
    <h3 class="report-title">Загальні показники</h3>
    
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Загальна кількість
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($reportData['totals']['total_quantity'] ?? 0) ?> шт.
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-icon bg-primary text-white">
                                <i class="fas fa-boxes"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Загальна виручка
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($reportData['totals']['total_revenue'] ?? 0, 2) ?> грн.
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
        
        <div class="col-md-3 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Загальний прибуток
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($reportData['totals']['total_profit'] ?? 0, 2) ?> грн.
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-icon bg-info text-white">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Маржа прибутку
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                $revenue = $reportData['totals']['total_revenue'] ?? 0;
                                $profit = $reportData['totals']['total_profit'] ?? 0;
                                $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0;
                                echo $margin . '%';
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-icon bg-warning text-white">
                                <i class="fas fa-percentage"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="report-section">
    <h3 class="report-title">Динаміка продажів</h3>
    
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    Продажі за категоріями
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 250px;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    Топ продуктів за кількістю продажів
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 250px;">
                        <canvas id="productChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="report-section">
    <h3 class="report-title">Деталі продажів за продуктами</h3>
    
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Продукт</th>
                    <th>Категорія</th>
                    <th class="text-end">Продано</th>
                    <th class="text-end">Виручка</th>
                    <th class="text-end">Прибуток</th>
                    <th class="text-end">Маржа</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reportData['products'] ?? [] as $product): ?>
                    <tr>
                        <td><?= $product['product_name'] ?></td>
                        <td><?= $product['category_name'] ?? 'Не вказано' ?></td>
                        <td class="text-end"><?= number_format($product['total_sold'] ?? $product['quantity'] ?? 0) ?> шт.</td>
                        <td class="text-end"><?= number_format($product['total_revenue'] ?? $product['revenue'] ?? 0, 2) ?> грн.</td>
                        <td class="text-end"><?= number_format($product['total_profit'] ?? $product['profit'] ?? 0, 2) ?> грн.</td>
                        <td class="text-end">
                            <?php 
                            $productRevenue = $product['total_revenue'] ?? $product['revenue'] ?? 0;
                            $productProfit = $product['total_profit'] ?? $product['profit'] ?? 0;
                            $productMargin = $productRevenue > 0 ? round(($productProfit / $productRevenue) * 100, 2) : 0;
                            echo $productMargin . '%';
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($reportType == 'products'): ?>
<!-- Отчет по продуктам -->
<!-- Код для отчета по продуктам -->

<?php elseif ($reportType == 'customers'): ?>
<!-- Отчет по клиентам -->
<!-- Код для отчета по клиентам -->

<?php elseif ($reportType == 'inventory'): ?>
<!-- Отчет по складским запасам -->
<!-- Код для отчета по складским запасам -->

<?php elseif ($reportType == 'orders'): ?>
<!-- Отчет по заказам -->
<!-- Код для отчета по заказам -->

<?php else: ?>
<!-- Если тип отчета не определен -->
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i> Выберите тип отчета для генерации.
</div>
<?php endif; ?>

<!-- Подвал отчета -->
<div class="report-footer mt-5 pt-2 text-muted">
    <p>Звіт згенеровано: <?= date('d.m.Y H:i:s') ?></p>
    <p>Користувач: <?= get_current_user_name() ?></p>
</div>