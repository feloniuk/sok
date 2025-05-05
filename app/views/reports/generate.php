<?php
// app/views/reports/generate.php
$title = 'Генерація звітів';

// Додаткові CSS стилі
$extra_css = '
<style>
    .report-card {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: none;
        margin-bottom: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .report-icon {
        font-size: 2.5rem;
        color: var(--primary);
        margin-bottom: 1rem;
    }
</style>';

// Додаткові JS скрипти
$extra_js = '
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
    
    // Залежність доступних полів від типу звіту
    $("#report_type").on("change", function() {
        const reportType = $(this).val();
        
        // Спочатку приховуємо всі неспільні поля
        $(".report-field").hide();
        
        // Відображення відповідних полів для вибраного типу звіту
        if (reportType) {
            $(".report-field-" + reportType).show();
        }
    });
});
</script>';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Головна</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('reports') ?>">Звіти</a></li>
                <li class="breadcrumb-item active">Генерація звіту</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card report-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-cog me-2"></i> Параметри звіту
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('reports/generate') ?>" method="GET">
                    <!-- Тип звіту -->
                    <div class="mb-3">
                        <label for="report_type" class="form-label">Тип звіту</label>
                        <select name="report_type" id="report_type" class="form-select" required>
                            <option value="">Виберіть тип звіту</option>
                            <option value="sales" <?= isset($filter['report_type']) && $filter['report_type'] == 'sales' ? 'selected' : '' ?>>Звіт по продажам</option>
                            <option value="products" <?= isset($filter['report_type']) && $filter['report_type'] == 'products' ? 'selected' : '' ?>>Звіт по продуктам</option>
                            <option value="customers" <?= isset($filter['report_type']) && $filter['report_type'] == 'customers' ? 'selected' : '' ?>>Звіт по клієнтам</option>
                            <option value="inventory" <?= isset($filter['report_type']) && $filter['report_type'] == 'inventory' ? 'selected' : '' ?>>Звіт по складським запасам</option>
                            <option value="orders" <?= isset($filter['report_type']) && $filter['report_type'] == 'orders' ? 'selected' : '' ?>>Звіт по замовленням</option>
                        </select>
                    </div>
                    
                    <!-- Період -->
                    <div class="mb-3">
                        <label class="form-label">Період</label>
                        <div class="row g-2">
                            <div class="col">
                                <input type="text" class="form-control datepicker" name="start_date" placeholder="З" value="<?= $filter['start_date'] ?? date('Y-m-d', strtotime('-1 month')) ?>">
                            </div>
                            <div class="col">
                                <input type="text" class="form-control datepicker" name="end_date" placeholder="По" value="<?= $filter['end_date'] ?? date('Y-m-d') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Поля для звіту по продажам і продуктам -->
                    <div class="mb-3 report-field report-field-sales report-field-products" style="display: none;">
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
                    
                    <!-- Поля для звіту по замовленням -->
                    <div class="mb-3 report-field report-field-orders" style="display: none;">
                        <label for="status" class="form-label">Статус замовлення</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Всі статуси</option>
                            <option value="pending" <?= isset($filter['status']) && $filter['status'] == 'pending' ? 'selected' : '' ?>>Очікує</option>
                            <option value="processing" <?= isset($filter['status']) && $filter['status'] == 'processing' ? 'selected' : '' ?>>Обробляється</option>
                            <option value="shipped" <?= isset($filter['status']) && $filter['status'] == 'shipped' ? 'selected' : '' ?>>Відправлено</option>
                            <option value="delivered" <?= isset($filter['status']) && $filter['status'] == 'delivered' ? 'selected' : '' ?>>Доставлено</option>
                            <option value="cancelled" <?= isset($filter['status']) && $filter['status'] == 'cancelled' ? 'selected' : '' ?>>Скасовано</option>
                        </select>
                    </div>
                    
                    <!-- Формат звіту -->
                    <div class="mb-3">
                        <label for="format" class="form-label">Формат звіту</label>
                        <select name="format" id="format" class="form-select" required>
                            <option value="html" <?= (!isset($filter['format']) || $filter['format'] == 'html') ? 'selected' : '' ?>>HTML (у браузері)</option>
                            <option value="pdf" <?= isset($filter['format']) && $filter['format'] == 'pdf' ? 'selected' : '' ?>>PDF</option>
                            <option value="excel" <?= isset($filter['format']) && $filter['format'] == 'excel' ? 'selected' : '' ?>>Excel</option>
                            <option value="csv" <?= isset($filter['format']) && $filter['format'] == 'csv' ? 'selected' : '' ?>>CSV</option>
                        </select>
                    </div>
                    
                    <!-- Кнопка генерації -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-file-export me-1"></i> Згенерувати звіт
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8 mb-4">
        <div class="card report-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-book me-2"></i> Доступні звіти
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card report-card">
                            <div class="card-body text-center">
                                <div class="report-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h5 class="card-title">Звіт по продажам</h5>
                                <p class="card-text text-muted">Аналіз продажів за період, включаючи динаміку та розподіл за категоріями</p>
                                <a href="<?= base_url('reports/generate?report_type=sales') ?>" class="btn btn-outline-primary">Вибрати</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card report-card">
                            <div class="card-body text-center">
                                <div class="report-icon">
                                    <i class="fas fa-box"></i>
                                </div>
                                <h5 class="card-title">Звіт по продуктам</h5>
                                <p class="card-text text-muted">Аналіз продуктів за обсягом продажів, виручкою і прибутком</p>
                                <a href="<?= base_url('reports/generate?report_type=products') ?>" class="btn btn-outline-primary">Вибрати</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card report-card">
                            <div class="card-body text-center">
                                <div class="report-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h5 class="card-title">Звіт по клієнтам</h5>
                                <p class="card-text text-muted">Аналіз активності клієнтів, їх замовлень і загальних витрат</p>
                                <a href="<?= base_url('reports/generate?report_type=customers') ?>" class="btn btn-outline-primary">Вибрати</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card report-card">
                            <div class="card-body text-center">
                                <div class="report-icon">
                                    <i class="fas fa-warehouse"></i>
                                </div>
                                <h5 class="card-title">Звіт по складським запасам</h5>
                                <p class="card-text text-muted">Аналіз запасів за категоріями, включаючи вартість і розподіл</p>
                                <a href="<?= base_url('reports/generate?report_type=inventory') ?>" class="btn btn-outline-primary">Вибрати</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card report-card">
                            <div class="card-body text-center">
                                <div class="report-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <h5 class="card-title">Звіт по замовленням</h5>
                                <p class="card-text text-muted">Аналіз замовлень за статусом, способом оплати і сумою</p>
                                <a href="<?= base_url('reports/generate?report_type=orders') ?>" class="btn btn-outline-primary">Вибрати</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>