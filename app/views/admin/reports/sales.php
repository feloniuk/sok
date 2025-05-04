<?php
// app/views/admin/reports/sales.php
$title = 'Звіт по продажам';

// Додаткові CSS стилі
$extra_css = '
<style>
    .filter-card {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: none;
        margin-bottom: 20px;
    }
    
    .stat-card {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        margin-bottom: 20px;
    }
</style>';

// Додаткові JS скрипти
$extra_js = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Ініціалізація вибору дати
    $(".datepicker").datepicker({
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        clearBtn: true,
        language: "uk",
        autoclose: true,
        todayHighlight: true
    });
    
    // Ініціалізація графіків
    const salesCtx = document.getElementById("salesChart").getContext("2d");
    const salesChart = new Chart(salesCtx, {
        type: "line",
        data: {
            labels: ' . json_encode(array_column($salesData['daily'] ?? [], 'date')) . ',
            datasets: [{
                label: "Виручка",
                data: ' . json_encode(array_column($salesData['daily'] ?? [], 'revenue')) . ',
                borderColor: "#4e73df",
                backgroundColor: "rgba(78, 115, 223, 0.1)",
                tension: 0.3,
                fill: true
            }, {
                label: "Прибуток",
                data: ' . json_encode(array_column($salesData['daily'] ?? [], 'profit')) . ',
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
            labels: ' . json_encode(array_column($salesData['categories'] ?? [], 'category_name')) . ',
            datasets: [{
                data: ' . json_encode(array_column($salesData['categories'] ?? [], 'revenue')) . ',
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
            labels: ' . json_encode(array_column($salesData['products'] ?? [], 'product_name')) . ',
            datasets: [{
                label: "Кількість продажів",
                data: ' . json_encode(array_column($salesData['products'] ?? [], 'quantity')) . ',
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
    
    // Зміна періоду
    $(".period-selector").on("click", function(e) {
        e.preventDefault();
        const period = $(this).data("period");
        
        // Встановлення відповідних дат
        let startDate, endDate;
        const today = new Date();
        endDate = today.toISOString().split("T")[0]; // Сьогодні
        
        switch (period) {
            case "week":
                const lastWeek = new Date();
                lastWeek.setDate(today.getDate() - 7);
                startDate = lastWeek.toISOString().split("T")[0];
                break;
            case "month":
                const lastMonth = new Date();
                lastMonth.setMonth(today.getMonth() - 1);
                startDate = lastMonth.toISOString().split("T")[0];
                break;
            case "quarter":
                const lastQuarter = new Date();
                lastQuarter.setMonth(today.getMonth() - 3);
                startDate = lastQuarter.toISOString().split("T")[0];
                break;
            case "year":
                const lastYear = new Date();
                lastYear.setFullYear(today.getFullYear() - 1);
                startDate = lastYear.toISOString().split("T")[0];
                break;
        }
        
        // Оновлення полів дати
        $("#start_date").val(startDate);
        $("#end_date").val(endDate);
        
        // Автоматичне відправлення форми
        $("#filterForm").submit();
    });
    
    // Друк звіту
    $("#printReport").on("click", function() {
        window.print();
    });
});
</script>';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Головна</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('reports') ?>">Звіти</a></li>
                <li class="breadcrumb-item active"><?= $title ?></li>
            </ol>
        </nav>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <button id="printReport" class="btn btn-outline-primary">
                <i class="fas fa-print me-1"></i> Друк
            </button>
            <a href="<?= base_url('reports/generate?report_type=sales') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-download me-1"></i> Експорт
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Фільтри -->
    <div class="col-md-3 mb-4">
        <div class="card filter-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Фільтри</h5>
            </div>
            <div class="card-body">
                <form id="filterForm" action="<?= base_url('reports/sales') ?>" method="GET">
                    <!-- Період (швидкий вибір) -->
                    <div class="mb-3">
                        <label class="form-label">Швидкий вибір періоду</label>
                        <div class="btn-group w-100">
                            <button type="button" class="btn btn-outline-primary period-selector" data-period="week">Тиждень</button>
                            <button type="button" class="btn btn-outline-primary period-selector" data-period="month">Місяць</button>
                            <button type="button" class="btn btn-outline-primary period-selector" data-period="quarter">Квартал</button>
                            <button type="button" class="btn btn-outline-primary period-selector" data-period="year">Рік</button>
                        </div>
                    </div>
                    
                    <!-- Період (ручний вибір) -->
                    <div class="mb-3">
                        <label class="form-label">Період</label>
                        <div class="row g-2">
                            <div class="col">
                                <input type="text" class="form-control datepicker" id="start_date" name="start_date" placeholder="З" value="<?= $filter['start_date'] ?? date('Y-m-d', strtotime('-1 month')) ?>">
                            </div>
                            <div class="col">
                                <input type="text" class="form-control datepicker" id="end_date" name="end_date" placeholder="По" value="<?= $filter['end_date'] ?? date('Y-m-d') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Категорія -->
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Категорія</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">Всі категорії</option>
                            <?php foreach ($categories ?? [] as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= isset($filter['category_id']) && $filter['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                    <?= $category['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Кнопки -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Застосувати
                        </button>
                        <a href="<?= base_url('reports/sales') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Скинути
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Результати -->
    <div class="col-md-9">
        <!-- Заголовок звіту -->
        <div class="alert alert-info">
            <div class="d-flex">
                <div class="me-3">
                    <i class="fas fa-info-circle fa-2x"></i>
                </div>
                <div>
                    <h5 class="alert-heading">Інформація про звіт</h5>
                    <p class="mb-0">
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
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Загальні показники -->
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
                                    <?= number_format($salesData['totals']['total_quantity'] ?? 0) ?> шт.
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-boxes fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4"><div class="col-md-3 mb-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Загальна виручка
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($salesData['totals']['total_revenue'] ?? 0, 2) ?> грн.
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
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
                                    <?= number_format($salesData['totals']['total_profit'] ?? 0, 2) ?> грн.
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                                    $revenue = $salesData['totals']['total_revenue'] ?? 0;
                                    $profit = $salesData['totals']['total_profit'] ?? 0;
                                    $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0;
                                    echo $margin . '%';
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-percentage fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Графіки -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="m-0 font-weight-bold text-primary">Динаміка продажів</h5>
                    </div>
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
                        <h5 class="m-0 font-weight-bold text-primary">Продажі за категоріями</h5>
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
                        <h5 class="m-0 font-weight-bold text-primary">Топ продуктів за кількістю продажів</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 250px;">
                            <canvas id="productChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Таблиця з деталями -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="m-0 font-weight-bold text-primary">Деталі продажів за категоріями</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Категорія</th>
                                <th class="text-end">Кількість</th>
                                <th class="text-end">Виручка</th>
                                <th class="text-end">Прибуток</th>
                                <th class="text-end">Маржа</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($salesData['categories'] ?? [] as $category): ?>
                                <tr>
                                    <td><?= $category['category_name'] ?></td>
                                    <td class="text-end"><?= number_format($category['quantity']) ?> шт.</td>
                                    <td class="text-end"><?= number_format($category['revenue'], 2) ?> грн.</td>
                                    <td class="text-end"><?= number_format($category['profit'], 2) ?> грн.</td>
                                    <td class="text-end">
                                        <?php 
                                        $categoryRevenue = $category['revenue'];
                                        $categoryProfit = $category['profit'];
                                        $categoryMargin = $categoryRevenue > 0 ? round(($categoryProfit / $categoryRevenue) * 100, 2) : 0;
                                        echo $categoryMargin . '%';
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="m-0 font-weight-bold text-primary">Деталі продажів за продуктами</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Продукт</th>
                                <th class="text-end">Кількість</th>
                                <th class="text-end">Виручка</th>
                                <th class="text-end">Прибуток</th>
                                <th class="text-end">Маржа</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($salesData['products'] ?? [] as $product): ?>
                                <tr>
                                    <td><?= $product['product_name'] ?></td>
                                    <td class="text-end"><?= number_format($product['quantity']) ?> шт.</td>
                                    <td class="text-end"><?= number_format($product['revenue'], 2) ?> грн.</td>
                                    <td class="text-end"><?= number_format($product['profit'], 2) ?> грн.</td>
                                    <td class="text-end">
                                        <?php 
                                        $productRevenue = $product['revenue'];
                                        $productProfit = $product['profit'];
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
        </div>
    </div>
</div>