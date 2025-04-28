<?php
// app/views/reports/sales.php - Сторінка звіту продажів
$title = 'Звіт по продажах';

// Підключення додаткових CSS
$extra_css = '
<style>
    .report-card {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: none;
        margin-bottom: 20px;
    }
    
    .stat-card {
        border-radius: 0.5rem;
        border: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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

// Підключення додаткових JS
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
    
    // Графік продажів по днях
    const dailySalesCtx = document.getElementById("dailySalesChart").getContext("2d");
    new Chart(dailySalesCtx, {
        type: "line",
        data: {
            labels: ' . json_encode(array_column($salesData['daily'] ?? [], 'date')) . ',
            datasets: [
                {
                    label: "Виручка",
                    data: ' . json_encode(array_column($salesData['daily'] ?? [], 'revenue')) . ',
                    borderColor: "#4e73df",
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    tension: 0.3,
                    fill: true
                },
                {
                    label: "Прибуток",
                    data: ' . json_encode(array_column($salesData['daily'] ?? [], 'profit')) . ',
                    borderColor: "#1cc88a",
                    backgroundColor: "rgba(28, 200, 138, 0.05)",
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
                    text: "Динаміка продажів за період"
                }
            },
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
    
    // Графік продажів за категоріями
    const categorySalesCtx = document.getElementById("categorySalesChart").getContext("2d");
    new Chart(categorySalesCtx, {
        type: "pie",
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
                title: {
                    display: true,
                    text: "Продажі за категоріями"
                },
                legend: {
                    position: "right"
                }
            }
        }
    });
    
    // Графік топових продуктів
    const topProductsCtx = document.getElementById("topProductsChart").getContext("2d");
    new Chart(topProductsCtx, {
        type: "bar",
        data: {
            labels: ' . json_encode(array_column($salesData['products'] ?? [], 'product_name')) . ',
            datasets: [{
                label: "Кількість проданих одиниць",
                data: ' . json_encode(array_column($salesData['products'] ?? [], 'quantity')) . ',
                backgroundColor: "#4e73df"
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: "y",
            plugins: {
                title: {
                    display: true,
                    text: "Топ продуктів за кількістю продажів"
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: "Кількість одиниць"
                    }
                }
            }
        }
    });
    
    // Зміна періоду звіту
    $(".period-btn").on("click", function(e) {
        e.preventDefault();
        
        const period = $(this).data("period");
        let startDate = "";
        let endDate = "";
        
        // Визначення дат на основі обраного періоду
        const today = new Date();
        
        switch (period) {
            case "week":
                const lastWeek = new Date();
                lastWeek.setDate(lastWeek.getDate() - 7);
                startDate = formatDate(lastWeek);
                endDate = formatDate(today);
                break;
                
            case "month":
                const lastMonth = new Date();
                lastMonth.setMonth(lastMonth.getMonth() - 1);
                startDate = formatDate(lastMonth);
                endDate = formatDate(today);
                break;
                
            case "quarter":
                const lastQuarter = new Date();
                lastQuarter.setMonth(lastQuarter.getMonth() - 3);
                startDate = formatDate(lastQuarter);
                endDate = formatDate(today);
                break;
                
            case "year":
                const lastYear = new Date();
                lastYear.setFullYear(lastYear.getFullYear() - 1);
                startDate = formatDate(lastYear);
                endDate = formatDate(today);
                break;
        }
        
        // Оновлення полів форми
        $("#start_date").val(startDate);
        $("#end_date").val(endDate);
        
        // Відправка форми
        $("#filterForm").submit();
    });
    
    // Форматування дати у формат YYYY-MM-DD
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");
        return `${year}-${month}-${day}`;
    }
});
</script>';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h2 mb-0"><?= $title ?></h1>
        <p class="text-muted">
            Звіт за період: <?= !empty($filter['start_date']) ? date('d.m.Y', strtotime($filter['start_date'])) : 'початок' ?> - 
            <?= !empty($filter['end_date']) ? date('d.m.Y', strtotime($filter['end_date'])) : 'кінець' ?>
        </p>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-file-export me-1"></i> Експорт
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?= base_url('reports/export_sales?format=csv&' . http_build_query($filter)) ?>">CSV</a></li>
                <li><a class="dropdown-item" href="<?= base_url('reports/export_sales?format=excel&' . http_build_query($filter)) ?>">Excel</a></li>
                <li><a class="dropdown-item" href="<?= base_url('reports/export_sales?format=pdf&' . http_build_query($filter)) ?>">PDF</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card report-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Фільтри</h5>
            </div>
            <div class="card-body">
                <form id="filterForm" action="<?= base_url('reports/sales') ?>" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Початкова дата</label>
                        <input type="text" class="form-control datepicker" id="start_date" name="start_date" value="<?= $filter['start_date'] ?? '' ?>" placeholder="YYYY-MM-DD">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">Кінцева дата</label>
                        <input type="text" class="form-control datepicker" id="end_date" name="end_date" value="<?= $filter['end_date'] ?? '' ?>" placeholder="YYYY-MM-DD">
                    </div>
                    <div class="col-md-3">
                        <label for="category_id" class="form-label">Категорія</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">Всі категорії</option>
                            <?php foreach ($categories ?? [] as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= isset($filter['category_id']) && $filter['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                    <?= $category['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="period" class="form-label">Період</label>
                        <div class="input-group">
                            <select class="form-select" id="period" name="period">
                                <option value="custom" <?= (!isset($filter['period']) || $filter['period'] == 'custom') ? 'selected' : '' ?>>Вибір дат</option>
                                <option value="week" <?= isset($filter['period']) && $filter['period'] == 'week' ? 'selected' : '' ?>>Тиждень</option>
                                <option value="month" <?= isset($filter['period']) && $filter['period'] == 'month' ? 'selected' : '' ?>>Місяць</option>
                                <option value="quarter" <?= isset($filter['period']) && $filter['period'] == 'quarter' ? 'selected' : '' ?>>Квартал</option>
                                <option value="year" <?= isset($filter['period']) && $filter['period'] == 'year' ? 'selected' : '' ?>>Рік</option>
                            </select>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i> Застосувати
                            </button>
                        </div>
                    </div>
                </form>
                
                <div class="mt-3">
                    <div class="btn-group btn-group-sm">
                        <a href="#" class="btn btn-outline-secondary period-btn" data-period="week">Тиждень</a>
                        <a href="#" class="btn btn-outline-secondary period-btn" data-period="month">Місяць</a>
                        <a href="#" class="btn btn-outline-secondary period-btn" data-period="quarter">Квартал</a>
                        <a href="#" class="btn btn-outline-secondary period-btn" data-period="year">Рік</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Загальна виручка
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($salesData['totals']['total_revenue'] ?? 0, 2) ?> грн
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-left-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Загальний прибуток
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($salesData['totals']['total_profit'] ?? 0, 2) ?> грн
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-left-info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Кількість проданих товарів
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($salesData['totals']['total_quantity'] ?? 0) ?> од.
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-left-warning h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Маржа прибутку
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php
                            $profitMargin = 0;
                            if (isset($salesData['totals']['total_revenue']) && $salesData['totals']['total_revenue'] > 0) {
                                $profitMargin = ($salesData['totals']['total_profit'] / $salesData['totals']['total_revenue']) * 100;
                            }
                            echo number_format($profitMargin, 2) . '%';
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

<div class="row mb-4">
    <div class="col-md-8">
        <div class="card report-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Динаміка продажів</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="dailySalesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card report-card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Розподіл за категоріями</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="categorySalesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card report-card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Топ продуктів за продажами</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card report-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Деталі продажів за період</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Кількість проданих одиниць</th>
                                <th>Виручка (грн)</th>
                                <th>Собівартість (грн)</th>
                                <th>Прибуток (грн)</th>
                                <th>Маржа (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($salesData['daily'] ?? [] as $day): ?>
                                <tr>
                                    <td><?= date('d.m.Y', strtotime($day['date'])) ?></td>
                                    <td><?= number_format($day['quantity']) ?></td>
                                    <td><?= number_format($day['revenue'], 2) ?></td>
                                    <td><?= number_format($day['revenue'] - $day['profit'], 2) ?></td>
                                    <td><?= number_format($day['profit'], 2) ?></td>
                                    <td>
                                        <?php
                                        $margin = 0;
                                        if ($day['revenue'] > 0) {
                                            $margin = ($day['profit'] / $day['revenue']) * 100;
                                        }
                                        echo number_format($margin, 2) . '%';
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <th>Всього:</th>
                                <th><?= number_format($salesData['totals']['total_quantity'] ?? 0) ?></th>
                                <th><?= number_format($salesData['totals']['total_revenue'] ?? 0, 2) ?></th>
                                <th><?= number_format(($salesData['totals']['total_revenue'] ?? 0) - ($salesData['totals']['total_profit'] ?? 0), 2) ?></th>
                                <th><?= number_format($salesData['totals']['total_profit'] ?? 0, 2) ?></th>
                                <th>
                                    <?php
                                    $totalMargin = 0;
                                    if (isset($salesData['totals']['total_revenue']) && $salesData['totals']['total_revenue'] > 0) {
                                        $totalMargin = ($salesData['totals']['total_profit'] / $salesData['totals']['total_revenue']) * 100;
                                    }
                                    echo number_format($totalMargin, 2) . '%';
                                    ?>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>