<?php
// app/views/reports/index.php - Головна сторінка звітів
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Статистика за останній місяць</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Загальний обсяг продажів
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= number_format($monthlyStats['sales'] ?? 0, 2) ?> грн
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
                                            Рентабельність
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= number_format($monthlyStats['profit'] ?? 0, 2) ?>%
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-percentage fa-2x text-gray-300"></i>
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
                                            Кількість замовлень
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= number_format($monthlyStats['orders'] ?? 0) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
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
                                            Нові клієнти
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= number_format($monthlyStats['customers'] ?? 0) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Типи звітів</h6>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="<?= base_url('reports/sales') ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-chart-line me-2 text-primary"></i>
                            <strong>Звіт з продажів</strong>
                            <p class="mb-0 text-muted small">Детальний аналіз продажів за період</p>
                        </div>
                        <span class="badge bg-primary rounded-pill">
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </a>
                    <a href="<?= base_url('reports/products') ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-box me-2 text-success"></i>
                            <strong>Звіт з товарів</strong>
                            <p class="mb-0 text-muted small">Аналіз продажів по товарах</p>
                        </div>
                        <span class="badge bg-success rounded-pill">
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </a>
                    <a href="<?= base_url('reports/generate?report_type=customers') ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-users me-2 text-info"></i>
                            <strong>Звіт по клієнтах</strong>
                            <p class="mb-0 text-muted small">Аналіз активності клієнтів</p>
                        </div>
                        <span class="badge bg-info rounded-pill">
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </a>
                    <a href="<?= base_url('reports/generate?report_type=orders') ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-shopping-cart me-2 text-warning"></i>
                            <strong>Звіт по замовленнях</strong>
                            <p class="mb-0 text-muted small">Статистика по замовленнях</p>
                        </div>
                        <span class="badge bg-warning rounded-pill">
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Генерація нового звіту</h6>
            </div>
            <div class="card-body">
                <p>Створіть новий звіт з налаштованими параметрами та експортуйте його у потрібному форматі.</p>
                <div class="text-center my-4">
                    <a href="<?= base_url('reports/generate') ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-file-export me-2"></i> Створити звіт
                    </a>
                </div>
                <hr>
                <h6 class="font-weight-bold">Доступні формати експорту:</h6>
                <div class="row text-center mt-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <i class="fas fa-file-csv fa-3x text-success"></i>
                        </div>
                        <p class="mb-0">CSV</p>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <i class="fas fa-file-excel fa-3x text-primary"></i>
                        </div>
                        <p class="mb-0">Excel</p>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <i class="fas fa-file-pdf fa-3x text-danger"></i>
                        </div>
                        <p class="mb-0">PDF</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($recentReports)): ?>
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Останні звіти</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Назва</th>
                                <th>Тип</th>
                                <th>Дата створення</th>
                                <th>Створив</th>
                                <th>Дії</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentReports as $report): ?>
                                <tr>
                                    <td><?= $report['name'] ?></td>
                                    <td><?= $report['type'] ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($report['created_at'])) ?></td>
                                    <td><?= $report['created_by'] ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= $report['url'] ?>" class="btn btn-info" title="Перегляд">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= $report['download_url'] ?>" class="btn btn-success" title="Завантажити">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
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
<?php endif; ?>