<?php
// app/views/reports/sales.php - Сторінка звіту по продажах

// Додаткові JS скрипти для графіків
$extra_js = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Графік щоденних продажів
    const dailySalesCtx = document.getElementById("dailySalesChart").getContext("2d");
    new Chart(dailySalesCtx, {
        type: "line",
        data: {
            labels: ' . json_encode(array_column($salesData["daily"] ?? [], "date")) . ',
            datasets: [
                {
                    label: "Виручка",
                    data: ' . json_encode(array_column($salesData["daily"] ?? [], "revenue")) . ',
                    borderColor: "#4e73df",
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    tension: 0.3,
                    fill: true
                },
                {
                    label: "Прибуток",
                    data: ' . json_encode(array_column($salesData["daily"] ?? [], "profit")) . ',
                    borderColor: "#1cc88a",
                    backgroundColor: "rgba(28, 200, 138, 0.05)",
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: "Динаміка продажів"
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || "";
                            if (label) {
                                label += ": ";
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat("uk-UA", {
                                    style: "currency",
                                    currency: "UAH"
                                }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: "Дата"
                    }
                },
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

    // Графік по категоріях
    const categorySalesCtx = document.getElementById("categorySalesChart").getContext("2d");
    new Chart(categorySalesCtx, {
        type: "doughnut",
        data: {
            labels: ' . json_encode(array_column($salesData["categories"] ?? [], "category_name")) . ',
            datasets: [{
                data: ' . json_encode(array_column($salesData["categories"] ?? [], "revenue")) . ',
                backgroundColor: [
                    "#4e73df", "#1cc88a", "#36b9cc", "#f6c23e", "#e74a3b", 
                    "#6f42c1", "#20c9a6", "#5a5c69", "#858796", "#f8f9fc"
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: "Продажі за категоріями"
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || "";
                            if (label) {
                                label += ": ";
                            }
                            if (context.parsed !== null) {
                                label += new Intl.NumberFormat("uk-UA", {
                                    style: "currency",
                                    currency: "UAH"
                                }).format(context.parsed);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Графік топ продуктів
    const topProductsCtx = document.getElementById("topProductsChart").getContext("2d");
    new Chart(topProductsCtx, {
        type: "bar",
        data: {
            labels: ' . json_encode(array_column($salesData["products"] ?? [], "product_name")) . ',
            datasets: [{
                label: "Виручка",
                data: ' . json_encode(array_column($salesData["products"] ?? [], "revenue")) . ',
                backgroundColor: "#4e73df"
            }]
        },
        options: {
            responsive: true,
            indexAxis: "y",
            plugins: {
                title: {
                    display: true,
                    text: "Топ-10 продуктів за виручкою"
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || "";
                            if (label) {
                                label += ": ";
                            }
                            if (context.parsed.x !== null) {
                                label += new Intl.NumberFormat("uk-UA", {
                                    style: "currency",
                                    currency: "UAH"
                                }).format(context.parsed.x);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: "Виручка (грн)"
                    }
                }
            }
        }
    });
});
</script>
';
?>

<div class="mb-4">
    <!-- Фільтри звіту -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Фільтри звіту продажів</h6>
        </div>
        <div class="card-body">
            <form method="get" action="<?= base_url('reports/sales') ?>" class="row g-3">
                <div class="col-md-2">
                    <label for="period" class="form-label">Період</label>
                    <select name="period" id="period" class="form-select">
                        <option value="week" <?= ($filter['period'] ?? '') == 'week' ? 'selected' : '' ?>>Тиждень</option>
                        <option value="month" <?= ($filter['period'] ?? '') == 'month' ? 'selected' : '' ?>>Місяць</option>
                        <option value="quarter" <?= ($filter['period'] ?? '') == 'quarter' ? 'selected' : '' ?>>Квартал</option>
                        <option value="year" <?= ($filter['period'] ?? '') == 'year' ? 'selected' : '' ?>>Рік</option>
                        <option value="custom" <?= ($filter['period'] ?? '') == 'custom' ? 'selected' : '' ?>>Власний</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Початкова дата</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?= $filter['start_date'] ?? '' ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Кінцева дата</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?= $filter['end_date'] ?? '' ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="category_id" class="form-label">Категорія</label>
                    <select name="category_id" id="category_id" class="form-select">
                        <option value="">Всі категорії</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= ($filter['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                <?= $category['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-1">
                    <label class="form-label d-block">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Загальні показники -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
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

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
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

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Кількість проданих одиниць
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($salesData['totals']['total_quantity'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Середня рентабельність
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

    <!-- Графіки -->
    <div class="row">
        <!-- Графік щоденних продажів -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Динаміка продажів за період</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="dailySalesChart"></canvas>
                    </div>
                </div>
                <div class="card-footer small text-muted">
                    Період: <?= date('d.m.Y', strtotime($filter['start_date'])) ?> - <?= date('d.m.Y', strtotime($filter['end_date'])) ?>
                </div>
            </div>
        </div>

        <!-- Графік по категоріях -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Продажі за категоріями</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="categorySalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Графік топ продуктів -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Топ-10 продуктів за виручкою</h6>
                </div>
                <div class="card-body">
                    <div style="height: 400px;">
                        <canvas id="topProductsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Таблиця по категоріях -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Продажі за категоріями</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Категорія</th>
                                    <th>Виручка</th>
                                    <th>Частка</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($salesData['categories'] ?? [] as $category): ?>
                                    <tr>
                                        <td><?= $category['category_name'] ?></td>
                                        <td class="text-end"><?= number_format($category['revenue'], 2) ?> грн</td>
                                        <td class="text-end">
                                            <?php 
                                            $percent = 0;
                                            if (isset($salesData['totals']['total_revenue']) && $salesData['totals']['total_revenue'] > 0) {
                                                $percent = ($category['revenue'] / $salesData['totals']['total_revenue']) * 100;
                                            }
                                            echo number_format($percent, 2) . '%';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($salesData['categories'])): ?>
                                    <tr>
                                        <td colspan="3" class="text-center">Немає даних про продажі за категоріями</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <?php if (!empty($salesData['categories'])): ?>
                                <tfoot>
                                    <tr>
                                        <th>Всього</th>
                                        <th class="text-end"><?= number_format($salesData['totals']['total_revenue'] ?? 0, 2) ?> грн</th>
                                        <th class="text-end">100%</th>
                                    </tr>
                                </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблиця продуктів -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Детальні дані по продуктах</h6>
            <div>
                <a href="<?= base_url('reports/generate?report_type=sales&format=csv&' . http_build_query($filter)) ?>" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-csv"></i> Експорт CSV
                </a>
                <a href="<?= base_url('reports/generate?report_type=sales&format=excel&' . http_build_query($filter)) ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-file-excel"></i> Експорт Excel
                </a>
                <a href="<?= base_url('reports/generate?report_type=sales&format=pdf&' . http_build_query($filter)) ?>" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-file-pdf"></i> Експорт PDF
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Продукт</th>
                            <th>Категорія</th>
                            <th class="text-end">Кількість</th>
                            <th class="text-end">Виручка</th>
                            <th class="text-end">Прибуток</th>
                            <th class="text-end">Рентабельність</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($salesData['products'] ?? [] as $product): ?>
                            <tr>
                                <td><?= $product['product_name'] ?></td>
                                <td><?= isset($product['category_name']) ? $product['category_name'] : '-' ?></td>
                                <td class="text-end"><?= number_format($product['quantity']) ?></td>
                                <td class="text-end"><?= number_format($product['revenue'], 2) ?> грн</td>
                                <td class="text-end"><?= number_format($product['profit'], 2) ?> грн</td>
                                <td class="text-end">
                                    <?php 
                                    $marginPercent = 0;
                                    if ($product['revenue'] > 0) {
                                        $marginPercent = ($product['profit'] / $product['revenue']) * 100;
                                    }
                                    echo number_format($marginPercent, 2) . '%';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($salesData['products'])): ?>
                            <tr>
                                <td colspan="6" class="text-center">Немає даних про продажі товарів за вказаний період</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($salesData['products'])): ?>
                        <tfoot>
                            <tr class="table-primary fw-bold">
                                <td colspan="2">Всього</td>
                                <td class="text-end"><?= number_format($salesData['totals']['total_quantity'] ?? 0) ?></td>
                                <td class="text-end"><?= number_format($salesData['totals']['total_revenue'] ?? 0, 2) ?> грн</td>
                                <td class="text-end"><?= number_format($salesData['totals']['total_profit'] ?? 0, 2) ?> грн</td>
                                <td class="text-end">
                                    <?php 
                                    $totalMargin = 0;
                                    if (isset($salesData['totals']['total_revenue']) && $salesData['totals']['total_revenue'] > 0) {
                                        $totalMargin = ($salesData['totals']['total_profit'] / $salesData['totals']['total_revenue']) * 100;
                                    }
                                    echo number_format($totalMargin, 2) . '%';
                                    ?>
                                </td>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>