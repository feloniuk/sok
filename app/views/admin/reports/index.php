<?php
// app/views/admin/reports/index.php
$title = 'Звіти';

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
        font-size: 3rem;
        color: var(--primary);
        margin-bottom: 1rem;
    }
    
    .quick-stats {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: none;
        margin-bottom: 20px;
    }
    
    .quick-stat-item {
        padding: 1.5rem;
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .quick-stat-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: var(--primary);
        margin-bottom: 0.5rem;
    }
    
    .quick-stat-label {
        color: var(--gray);
        margin-bottom: 0;
    }
</style>';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h2 mb-0"><?= $title ?></h1>
        <p class="text-muted">Аналітика продажів, замовлень, клієнтів та складу</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?= base_url('reports/generate') ?>" class="btn btn-success">
            <i class="fas fa-file-export me-1"></i> Згенерувати звіт
        </a>
    </div>
</div>

<div class="row">
    <!-- Швидка статистика -->
    <div class="col-md-4 mb-4">
        <div class="card quick-stats">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i> Швидка статистика
                </h5>
            </div>
            <div class="card-body">
                <div class="quick-stat-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="quick-stat-value"><?= number_format($monthlyStats['sales'] ?? 0, 2) ?> грн.</div>
                            <p class="quick-stat-label">Продажі за місяць</p>
                        </div>
                        <div>
                            <i class="fas fa-chart-line fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
                
                <div class="quick-stat-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="quick-stat-value"><?= $monthlyStats['orders'] ?? 0 ?></div>
                            <p class="quick-stat-label">Замовлень за місяць</p>
                        </div>
                        <div>
                            <i class="fas fa-shopping-cart fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
                
                <div class="quick-stat-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="quick-stat-value"><?= $monthlyStats['customers'] ?? 0 ?></div>
                            <p class="quick-stat-label">Нових клієнтів за місяць</p>
                        </div>
                        <div>
                            <i class="fas fa-users fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
                
                <div class="quick-stat-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="quick-stat-value"><?= $monthlyStats['profit'] ?? 0 ?> %</div>
                            <p class="quick-stat-label">Маржа прибутку</p>
                        </div>
                        <div>
                            <i class="fas fa-percentage fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="<?= base_url('reports/sales') ?>" class="btn btn-outline-primary">
                        <i class="fas fa-eye me-1"></i> Докладніше
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Список доступних звітів -->
    <div class="col-md-8">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card report-card">
                    <div class="card-body text-center">
                        <div class="report-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4 class="card-title">Звіт по продажам</h4>
                        <p class="card-text">Детальний аналіз продажів за період, включаючи динаміку, розподіл за категоріями і топ продуктів</p>
                        <div class="mt-4">
                            <a href="<?= base_url('reports/sales') ?>" class="btn btn-primary">
                                <i class="fas fa-eye me-1"></i> Переглянути
                            </a>
                            <a href="<?= base_url('reports/generate?report_type=sales') ?>" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-cog me-1"></i> Налаштувати
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card report-card">
                    <div class="card-body text-center">
                        <div class="report-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <h4 class="card-title">Звіт по продуктам</h4>
                        <p class="card-text">Аналіз продуктів за обсягом продажів, виручкою і прибутком. Виявлення найбільш популярних продуктів</p>
                        <div class="mt-4">
                            <a href="<?= base_url('reports/products') ?>" class="btn btn-primary">
                                <i class="fas fa-eye me-1"></i> Переглянути
                            </a>
                            <a href="<?= base_url('reports/generate?report_type=products') ?>" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-cog me-1"></i> Налаштувати
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card report-card">
                    <div class="card-body text-center">
                        <div class="report-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4 class="card-title">Звіт по клієнтам</h4>
                        <p class="card-text">Аналіз активності клієнтів, їх замовлень, загальних витрат і визначення найбільш цінних клієнтів</p>
                        <div class="mt-4">
                            <a href="<?= base_url('reports/customers') ?>" class="btn btn-primary">
                                <i class="fas fa-eye me-1"></i> Переглянути
                            </a>
                            <a href="<?= base_url('reports/generate?report_type=customers') ?>" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-cog me-1"></i> Налаштувати
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card report-card">
                    <div class="card-body text-center">
                        <div class="report-icon">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <h4 class="card-title">Звіт по запасам</h4>
                        <p class="card-text">Аналіз складських запасів за категоріями, включаючи вартість, розподіл і динаміку руху товарів</p>
                        <div class="mt-4">
                            <a href="<?= base_url('reports/inventory') ?>" class="btn btn-primary">
                                <i class="fas fa-eye me-1"></i> Переглянути
                            </a>
                            <a href="<?= base_url('reports/generate?report_type=inventory') ?>" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-cog me-1"></i> Налаштувати
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card report-card">
                    <div class="card-body text-center">
                        <div class="report-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h4 class="card-title">Звіт по замовленням</h4>
                        <p class="card-text">Аналіз замовлень за статусом, способом оплати і сумою. Динаміка замовлень в часі</p>
                        <div class="mt-4">
                            <a href="<?= base_url('reports/orders') ?>" class="btn btn-primary">
                                <i class="fas fa-eye me-1"></i> Переглянути
                            </a>
                            <a href="<?= base_url('reports/generate?report_type=orders') ?>" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-cog me-1"></i> Налаштувати
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card report-card">
                    <div class="card-body text-center">
                        <div class="report-icon">
                            <i class="fas fa-file-export"></i>
                        </div>
                        <h4 class="card-title">Нестандартний звіт</h4>
                        <p class="card-text">Створення нестандартного звіту з гнучкими параметрами і можливістю експорту в різні формати</p>
                        <div class="mt-4">
                            <a href="<?= base_url('reports/generate') ?>" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-1"></i> Створити
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Розшарені звіти -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card report-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i> Нещодавні звіти
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Назва</th>
                                <th>Тип</th>
                                <th>Створено</th>
                                <th>Автор</th>
                                <th>Дії</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentReports)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Немає нещодавніх звітів</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentReports as $report): ?>
                                    <tr>
                                        <td><?= $report['title'] ?></td>
                                        <td>
                                            <?php
                                            $typeLabels = [
                                                'sales' => 'Продажі',
                                                'products' => 'Продукти',
                                                'customers' => 'Клієнти',
                                                'inventory' => 'Склад',
                                                'orders' => 'Замовлення'
                                            ];
                                            $typeIcons = [
                                                'sales' => 'chart-line',
                                                'products' => 'box',
                                                'customers' => 'users',
                                                'inventory' => 'warehouse',
                                                'orders' => 'shopping-cart'
                                            ];
                                            ?>
                                            <i class="fas fa-<?= $typeIcons[$report['type']] ?? 'file' ?> me-1"></i>
                                            <?= $typeLabels[$report['type']] ?? $report['type'] ?>
                                        </td>
                                        <td><?= date('d.m.Y H:i', strtotime($report['created_at'])) ?></td>
                                        <td><?= $report['created_by'] ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url("reports/{$report['type']}?id={$report['id']}") ?>" class="btn btn-outline-primary" title="Переглянути">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= base_url("reports/download/{$report['id']}") ?>" class="btn btn-outline-secondary" title="Завантажити">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
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