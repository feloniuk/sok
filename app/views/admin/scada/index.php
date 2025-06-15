<?php
// app/views/admin/scada/index.php - SCADA дані
$title = '';

// Функції для фільтрації та пагінації
function buildFilterUrl($newParams = []) {
    $currentParams = $_GET;
    $params = array_merge($currentParams, $newParams);
    
    // Видалення порожніх параметрів
    foreach ($params as $key => $value) {
        if ($value === '' || $value === null) {
            unset($params[$key]);
        }
    }
    
    return '?' . http_build_query($params);
}

// Додаткові CSS стилі
$extra_css = '
<style>
    .scada-card {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: none;
        margin-bottom: 20px;
    }
    
    .stat-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .parameter-value {
        font-weight: bold;
        color: #007bff;
    }
    
    .chart-container {
        position: relative;
        height: 400px;
        margin-bottom: 20px;
    }
    
    .filter-card {
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
</style>';

// Додаткові JS скрипти
$extra_js = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    
    // Фільтрація при зміні параметрів
    $(".filter-control").on("change", function() {
        $("#filterForm").submit();
    });
    
    // Скидання фільтрів
    $("#resetFilters").on("click", function() {
        $(".filter-control").each(function() {
            if ($(this).is("select")) {
                $(this).val("");
            } else {
                $(this).val("");
            }
        });
        $("#filterForm").submit();
    });
    
    // Ініціалізація графіка
    const chartData = ' . json_encode($chartData) . ';
    const ctx = document.getElementById("scadaChart").getContext("2d");
    
    // Підготовка даних для графіка
    const dataPoints = [];
    const parameterGroups = {};
    
    // Групуємо дані по параметрах
    chartData.forEach(function(item) {
        const timeLabel = item.Dates + " " + item.Times;
        const value = parseFloat(item.Parameter);
        
        if (!parameterGroups[item.Name]) {
            parameterGroups[item.Name] = [];
        }
        
        parameterGroups[item.Name].push({
            x: timeLabel,
            y: value
        });
    });
    
    // Створюємо datasets для кожного параметра
    const datasets = [];
    let colorIndex = 0;
    const colors = ["#4e73df", "#1cc88a", "#36b9cc", "#f6c23e", "#e74a3b", "#5a5c69", "#6610f2", "#6f42c1", "#e83e8c", "#fd7e14"];
    
    Object.keys(parameterGroups).forEach(function(paramName) {
        datasets.push({
            label: paramName,
            data: parameterGroups[paramName].slice(-50), // Останні 50 точок
            borderColor: colors[colorIndex % colors.length],
            backgroundColor: "transparent",
            tension: 0.3,
            pointRadius: 2,
            pointHoverRadius: 4
        });
        colorIndex++;
    });
    
    let chart = null;
    
    // Створюємо графік тільки якщо є дані
    if (datasets.length > 0) {
        chart = new Chart(ctx, {
            type: "line",
            data: {
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: "Динаміка параметрів SCADA"
                    },
                    legend: {
                        position: "top"
                    }
                },
                scales: {
                    x: {
                        type: "category",
                        title: {
                            display: true,
                            text: "Час"
                        },
                        ticks: {
                            maxTicksLimit: 10
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: "Значення"
                        }
                    }
                },
                animation: {
                    duration: 500
                }
            }
        });
    } else {
        // Якщо немає даних, показуємо повідомлення
        ctx.font = "16px Arial";
        ctx.fillStyle = "#666";
        ctx.textAlign = "center";
        ctx.fillText("Немає даних для відображення", ctx.canvas.width / 2, ctx.canvas.height / 2);
    }
    
    // Автооновлення даних кожні 30 секунд (тільки якщо графік створено)
    if (chart) {
        setInterval(function() {
            loadLatestData();
        }, 30000);
    }
    
    // Функція завантаження останніх даних
    function loadLatestData() {
        if (!chart) return;
        
        $.ajax({
            url: "' . base_url('admin/scada/get_chart_data') . '",
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    updateChart(response.data, response.parameter);
                }
            },
            error: function() {
                console.log("Помилка завантаження даних");
            }
        });
    }
    
    // Оновлення графіка
    function updateChart(newData, parameterName) {
        if (!chart || !chart.data.datasets.length) return;
        
        // Знаходимо dataset для вказаного параметра
        let targetDataset = null;
        chart.data.datasets.forEach(function(dataset) {
            if (dataset.label === parameterName) {
                targetDataset = dataset;
            }
        });
        
        if (!targetDataset) return;
        
        // Додаємо нові точки
        newData.forEach(function(item) {
            const timeLabel = item.label;
            const value = parseFloat(item.value);
            
            targetDataset.data.push({
                x: timeLabel,
                y: value
            });
            
            // Залишаємо тільки останні 50 точок
            if (targetDataset.data.length > 50) {
                targetDataset.data.shift();
            }
        });
        
        chart.update("none");
    }
    
    // Обробка кнопки оновлення графіка
    $("#refreshChart").on("click", function() {
        const $btn = $(this);
        const $icon = $btn.find("i");
        
        // Анімація обертання
        $icon.addClass("fa-spin");
        $btn.prop("disabled", true);
        
        // Перезавантаження сторінки для оновлення всіх даних
        setTimeout(function() {
            location.reload();
        }, 500);
    });
    
    // Обробка форми очищення даних
    $("#cleanupForm").on("submit", function(e) {
        const daysToKeep = $("#days_to_keep").val();
        if (!confirm("Ви впевнені, що хочете видалити всі дані старіше " + daysToKeep + " днів? Цю дію не можна буде скасувати.")) {
            e.preventDefault();
            return false;
        }
    });
});
</script>';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h2 mb-0">SCADA Дані</h1>
        <p class="text-muted">Система збору та відображення даних промислового обладнання</p>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <a href="<?= base_url('admin/scada/export_csv') . buildFilterUrl() ?>" class="btn btn-outline-success">
                <i class="fas fa-download me-1"></i> Експорт CSV
            </a>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cleanupModal">
                <i class="fas fa-trash me-1"></i> Очистити дані
            </button>
        </div>
    </div>
</div>

<!-- Статистика -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card scada-card">
            <div class="card-body text-center">
                <h2 class="h1 mb-2 text-primary"><?= number_format($stats['total_records'] ?? 0) ?></h2>
                <p class="text-muted mb-0">Всього записів</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card scada-card">
            <div class="card-body text-center">
                <h2 class="h1 mb-2 text-success"><?= $stats['unique_parameters'] ?? 0 ?></h2>
                <p class="text-muted mb-0">Унікальних параметрів</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card scada-card">
            <div class="card-body text-center">
                <h2 class="h1 mb-2 text-info"><?= number_format($stats['avg_value'] ?? 0, 2) ?></h2>
                <p class="text-muted mb-0">Середнє значення</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card scada-card">
            <div class="card-body text-center">
                <h2 class="h1 mb-2 text-warning"><?= number_format($stats['max_value'] ?? 0, 2) ?></h2>
                <p class="text-muted mb-0">Максимальне значення</p>
            </div>
        </div>
    </div>
</div>

<!-- Графік -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card scada-card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i> Графік параметрів в реальному часі
                </h5>
                <button type="button" class="btn btn-light btn-sm" id="refreshChart" title="Оновити графік">
                    <i class="fas fa-sync-alt"></i> Оновити
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($chartData)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <p class="mb-0">Немає даних для відображення графіка. Спробуйте змінити фільтри або перевірте наявність даних у системі.</p>
                    </div>
                <?php else: ?>
                    <div class="chart-container">
                        <canvas id="scadaChart"></canvas>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Фільтри -->
    <div class="col-md-3 mb-4">
        <div class="card filter-card">
            <div class="card-header bg-primary text-white">
                <h5 class="m-0">Фільтри</h5>
            </div>
            <div class="card-body">
                <form id="filterForm" action="<?= base_url('admin/scada') ?>" method="GET">
                    <!-- Параметр -->
                    <div class="mb-3">
                        <label for="name_filter" class="form-label">Параметр</label>
                        <select class="form-select filter-control" id="name_filter" name="name_filter">
                            <option value="">Всі параметри</option>
                            <?php foreach ($parameters as $param): ?>
                                <option value="<?= $param['Name'] ?>" <?= ($filters['name_filter'] == $param['Name']) ? 'selected' : '' ?>>
                                    <?= $param['Name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Період -->
                    <div class="mb-3">
                        <label class="form-label">Період</label>
                        <div class="row g-2">
                            <div class="col">
                                <input type="date" class="form-control filter-control" name="date_from" placeholder="З дати" value="<?= $filters['date_from'] ?? '' ?>">
                                <small class="form-text text-muted">З дати</small>
                            </div>
                            <div class="col">
                                <input type="date" class="form-control filter-control" name="date_to" placeholder="До дати" value="<?= $filters['date_to'] ?? '' ?>">
                                <small class="form-text text-muted">До дати</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Кількість записів на сторінці -->
                    <div class="mb-3">
                        <label for="per_page" class="form-label">Записів на сторінці</label>
                        <select class="form-select filter-control" id="per_page" name="per_page">
                            <option value="25" <?= ($filters['per_page'] == 25) ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= ($filters['per_page'] == 50) ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= ($filters['per_page'] == 100) ? 'selected' : '' ?>>100</option>
                            <option value="200" <?= ($filters['per_page'] == 200) ? 'selected' : '' ?>>200</option>
                        </select>
                    </div>
                    
                    <!-- Кнопки -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Застосувати
                        </button>
                        <button type="button" id="resetFilters" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Скинути
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Таблиця даних -->
    <div class="col-md-9">
        <div class="card scada-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Дані SCADA</h5>
            </div>
            <div class="card-body">
                <?php if (empty($scadaData)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Дані не знайдені. Спробуйте змінити параметри фільтрації.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Параметр</th>
                                    <th>Значення</th>
                                    <th>Дата</th>
                                    <th>Час</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($scadaData as $row): ?>
                                    <tr>
                                        <td><?= $row['ID'] ?></td>
                                        <td><?= $row['Name'] ?></td>
                                        <td class="parameter-value"><?= number_format($row['Parameter'], 2) ?></td>
                                        <td><?= $row['Dates'] ?></td>
                                        <td><?= $row['Times'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Пагінація -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildFilterUrl(['page' => 1]) ?>">
                                            <i class="fas fa-angle-double-left"></i>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildFilterUrl(['page' => $pagination['current_page'] - 1]) ?>">
                                            <i class="fas fa-angle-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php
                                $startPage = max(1, $pagination['current_page'] - 2);
                                $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                                
                                for ($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                    <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= buildFilterUrl(['page' => $i]) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildFilterUrl(['page' => $pagination['current_page'] + 1]) ?>">
                                            <i class="fas fa-angle-right"></i>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildFilterUrl(['page' => $pagination['total_pages']]) ?>">
                                            <i class="fas fa-angle-double-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                Показано <?= count($scadaData) ?> з <?= number_format($pagination['total_items']) ?> записів
                            </small>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Модальне вікно для очищення даних -->
<div class="modal fade" id="cleanupModal" tabindex="-1" aria-labelledby="cleanupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cleanupModalLabel">Очищення даних</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cleanupForm" action="<?= base_url('admin/scada/cleanup') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Увага!</strong> Ця дія видалить всі дані старіше вказаної кількості днів. Дану операцію неможливо скасувати.
                    </div>
                    
                    <div class="mb-3">
                        <label for="days_to_keep" class="form-label">Залишити дані за останні (днів)</label>
                        <input type="number" class="form-control" id="days_to_keep" name="days_to_keep" value="30" min="1" max="365" required>
                        <div class="form-text">Рекомендується залишати дані принаймні за останні 30 днів</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                    <button type="submit" class="btn btn-danger">Видалити дані</button>
                </div>
            </form>
        </div>
    </div>
</div>