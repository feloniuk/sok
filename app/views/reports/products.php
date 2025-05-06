<?php
// app/views/reports/products.php - Сторінка звіту по продуктах

// Додаткові JS скрипти для графіків
$extra_js = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Кругова діаграма кількості проданих товарів
    const quantityChartCtx = document.getElementById("quantityPieChart").getContext("2d");
    new Chart(quantityChartCtx, {
        type: "pie",
        data: {
            labels: ' . json_encode(array_column(array_slice($productsData['products'] ?? [], 0, 10), 'product_name')) . ',
            datasets: [{
                data: ' . json_encode(array_column(array_slice($productsData['products'] ?? [], 0, 10), 'quantity')) . ',
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
                    text: "Топ-10 продуктів за кількістю продажів"
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || "";
                            if (label) {
                                label += ": ";
                            }
                            if (context.parsed !== null) {
                                label += context.parsed + " шт. (" + 
                                    new Intl.NumberFormat("uk-UA", {
                                        style: "percent",
                                        minimumFractionDigits: 1,
                                        maximumFractionDigits: 1
                                    }).format(context.parsed / ' . ($productsData['totals']['quantity'] ?? 1) . ') + ")";
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Діаграма виручки за продуктами
    const revenueChartCtx = document.getElementById("revenueBarChart").getContext("2d");
    new Chart(revenueChartCtx, {
        type: "bar",
        data: {
            labels: ' . json_encode(array_column(array_slice($productsData['products'] ?? [], 0, 10), 'product_name')) . ',
            datasets: [{
                label: "Виручка",
                data: ' . json_encode(array_column(array_slice($productsData['products'] ?? [], 0, 10), 'revenue')) . ',
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

    // Діаграма рентабельності продуктів
    const profitChartCtx = document.getElementById("profitBarChart").getContext("2d");
    new Chart(profitChartCtx, {
        type: "bar",
        data: {
            labels: ' . json_encode(array_column(array_slice($productsData['products'] ?? [], 0, 10), 'product_name')) . ',
            datasets: [{
                label: "Рентабельність",
                data: ' . json_encode(array_map(function($product) {
                    return $product['revenue'] > 0 ? ($product['profit'] / $product['revenue']) * 100 : 0;
                }, array_slice($productsData['products'] ?? [], 0, 10))) . ',
                backgroundColor: "#1cc88a"
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: "Рентабельність продуктів"
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || "";
                            if (label) {
                                label += ": ";
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y.toFixed(2) + "%";
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: "Рентабельність (%)"
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
            <h6 class="m-0 font-weight-bold text-primary">Фільтри звіту по продуктах</h6>
        </div>
        <div class="card-body">
            <form method="get" action="<?= base_url('reports/products') ?>" class="row g-3">
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

                <div class="col-md-10">
                    <label for="sort" class="form-label">Сортування</label>
                    <select name="sort" id="sort" class="form-select">
                        <option value="quantity_desc" <?= ($filter['sort'] ?? '') == 'quantity_desc' ? 'selected' : '' ?>>За кількістю (спадання)</option>
                        <option value="quantity_asc" <?= ($filter['sort'] ?? '') == 'quantity_asc' ? 'selected' : '' ?>>За кількістю (зростання)</option>
                        <option value="revenue_desc" <?= ($filter['sort'] ?? '') == 'revenue_desc' ? 'selected' : '' ?>>За виручкою (спадання)</option>
                        <option value="revenue_asc" <?= ($filter['sort'] ?? '') == 'revenue_asc' ? 'selected' : '' ?>>За виручкою (зростання)</option>
                        <option value="profit_desc" <?= ($filter['sort'] ?? '') == 'profit_desc' ? 'selected' : '' ?>>За прибутком (спадання)</option>
                        <option value="profit_asc" <?= ($filter['sort'] ?? '') == 'profit_asc' ? 'selected' : '' ?>>За прибутком (зростання)</option>
                        <option value="name_asc" <?= ($filter['sort'] ?? '') == 'name_asc' ? 'selected' : '' ?>>За назвою (А-Я)</option>
                        <option value="name_desc" <?= ($filter['sort'] ?? '') == 'name_desc' ? 'selected' : '' ?>>За назвою (Я-А)</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label d-block">&nbsp;</label>
                    <a href="<?= base_url('reports/products') ?>" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-redo"></i> Скинути
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Загальні показники -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Загальна кількість проданих товарів
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($productsData['totals']['quantity'] ?? 0) ?> шт.
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Загальна виручка
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($productsData['totals']['revenue'] ?? 0, 2) ?> грн
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Загальний прибуток
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($productsData['totals']['profit'] ?? 0, 2) ?> грн
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Графіки -->
    <div class="row">
        <!-- Кругова діаграма кількості проданих товарів -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Розподіл кількості продажів за продуктами</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="quantityPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Гістограма виручки за продуктами -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Виручка за продуктами</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="revenueBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Діаграма рентабельності продуктів -->
        <div class="col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Рентабельність продуктів</h6>
                </div>
                <div class="card-body">
                    <div style="height: 400px;">
                        <canvas id="profitBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблиця продуктів -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Детальна статистика по продуктах</h6>
            <div>
                <a href="<?= base_url('reports/generate?report_type=products&format=csv&' . http_build_query($filter)) ?>" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-csv"></i> Експорт CSV
                </a>
                <a href="<?= base_url('reports/generate?report_type=products&format=excel&' . http_build_query($filter)) ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-file-excel"></i> Експорт Excel
                </a>
                <a href="<?= base_url('reports/generate?report_type=products&format=pdf&' . http_build_query($filter)) ?>" class="btn btn-sm btn-outline-danger">
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
                            <th class="text-end">Ціна (грн)</th>
                            <th class="text-end">Залишок</th>
                            <th class="text-end">Продано</th>
                            <th class="text-end">Доля (%)</th>
                            <th class="text-end">Виручка (грн)</th>
                            <th class="text-end">Прибуток (грн)</th>
                            <th class="text-end">Рентабельність (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productsData['products'] ?? [] as $product): ?>
                            <tr>
                                <td><?= $product['product_name'] ?></td>
                                <td><?= $product['category_name'] ?? '-' ?></td>
                                <td class="text-end"><?= number_format($product['price'], 2) ?></td>
                                <td class="text-end"><?= number_format($product['stock']) ?></td>
                                <td class="text-end"><?= number_format($product['quantity']) ?></td>
                                <td class="text-end">
                                    <?php 
                                    $percent = 0;
                                    if (isset($productsData['totals']['quantity']) && $productsData['totals']['quantity'] > 0) {
                                        $percent = ($product['quantity'] / $productsData['totals']['quantity']) * 100;
                                    }
                                    echo number_format($percent, 2);
                                    ?>
                                </td>
                                <td class="text-end"><?= number_format($product['revenue'], 2) ?></td>
                                <td class="text-end"><?= number_format($product['profit'], 2) ?></td>
                                <td class="text-end">
                                    <?php 
                                    $marginPercent = 0;
                                    if ($product['revenue'] > 0) {
                                        $marginPercent = ($product['profit'] / $product['revenue']) * 100;
                                    }
                                    echo number_format($marginPercent, 2);
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($productsData['products'])): ?>
                            <tr>
                                <td colspan="9" class="text-center">Немає даних про продажі товарів за вказаний період</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($productsData['products'])): ?>
                        <tfoot>
                            <tr class="table-primary fw-bold">
                                <td colspan="4">Всього</td>
                                <td class="text-end"><?= number_format($productsData['totals']['quantity'] ?? 0) ?></td>
                                <td class="text-end">100.00</td>
                                <td class="text-end"><?= number_format($productsData['totals']['revenue'] ?? 0, 2) ?></td>
                                <td class="text-end"><?= number_format($productsData['totals']['profit'] ?? 0, 2) ?></td>
                                <td class="text-end">
                                    <?php 
                                    $totalMargin = 0;
                                    if (isset($productsData['totals']['revenue']) && $productsData['totals']['revenue'] > 0) {
                                        $totalMargin = ($productsData['totals']['profit'] / $productsData['totals']['revenue']) * 100;
                                    }
                                    echo number_format($totalMargin, 2);
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