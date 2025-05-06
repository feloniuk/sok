<?php
// app/views/reports/generate.php - Сторінка генерації звіту
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Генерація звіту</h6>
            </div>
            <div class="card-body">
                <form method="get" action="<?= base_url('reports/generate') ?>">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="report_type" class="form-label">Тип звіту</label>
                                <select name="report_type" id="report_type" class="form-select" required>
                                    <option value="">Оберіть тип звіту</option>
                                    <option value="sales" <?= ($reportType ?? '') == 'sales' ? 'selected' : '' ?>>Звіт з продажів</option>
                                    <option value="products" <?= ($reportType ?? '') == 'products' ? 'selected' : '' ?>>Звіт з товарів</option>
                                    <option value="customers" <?= ($reportType ?? '') == 'customers' ? 'selected' : '' ?>>Звіт по клієнтах</option>
                                    <option value="orders" <?= ($reportType ?? '') == 'orders' ? 'selected' : '' ?>>Звіт по замовленнях</option>
                                    <option value="inventory" <?= ($reportType ?? '') == 'inventory' ? 'selected' : '' ?>>Звіт по складських запасах</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Початкова дата</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="<?= $filters['start_date'] ?? date('Y-m-d', strtotime('-30 days')) ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="end_date" class="form-label">Кінцева дата</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="<?= $filters['end_date'] ?? date('Y-m-d') ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Категорія</label>
                                <select name="category_id" id="category_id" class="form-select">
                                    <option value="">Всі категорії</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                            <?= $category['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Статус замовлення (для звітів по замовленнях)</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">Всі статуси</option>
                                    <option value="pending" <?= ($filters['status'] ?? '') == 'pending' ? 'selected' : '' ?>>Очікує</option>
                                    <option value="processing" <?= ($filters['status'] ?? '') == 'processing' ? 'selected' : '' ?>>Обробляється</option>
                                    <option value="shipped" <?= ($filters['status'] ?? '') == 'shipped' ? 'selected' : '' ?>>Відправлено</option>
                                    <option value="delivered" <?= ($filters['status'] ?? '') == 'delivered' ? 'selected' : '' ?>>Доставлено</option>
                                    <option value="cancelled" <?= ($filters['status'] ?? '') == 'cancelled' ? 'selected' : '' ?>>Скасовано</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="format" class="form-label">Формат звіту</label>
                                <select name="format" id="format" class="form-select">
                                    <option value="html" <?= ($format ?? '') == 'html' ? 'selected' : '' ?>>HTML (перегляд у браузері)</option>
                                    <option value="csv" <?= ($format ?? '') == 'csv' ? 'selected' : '' ?>>CSV</option>
                                    <option value="excel" <?= ($format ?? '') == 'excel' ? 'selected' : '' ?>>Excel</option>
                                    <option value="pdf" <?= ($format ?? '') == 'pdf' ? 'selected' : '' ?>>PDF</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-file-export me-2"></i> Згенерувати звіт
                        </button>
                        <a href="<?= base_url('reports') ?>" class="btn btn-outline-secondary btn-lg ms-2">
                            <i class="fas fa-arrow-left me-2"></i> Назад
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Інформація про типи звітів -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Інформація про доступні типи звітів</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-chart-line text-primary me-2"></i> Звіт з продажів
                                </h5>
                                <p class="card-text">Детальний аналіз продажів за обраний період. Включає інформацію про виручку, прибуток, динаміку продажів, розподіл за категоріями та топ продукти.</p>
                                <ul>
                                    <li>Динаміка продажів за період</li>
                                    <li>Розподіл продажів за категоріями</li>
                                    <li>Топ продуктів за виручкою</li>
                                    <li>Детальні дані по продуктах</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-box text-success me-2"></i> Звіт з товарів
                                </h5>
                                <p class="card-text">Аналіз продажів товарів за обраний період. Включає інформацію про кількість проданих товарів, виручку, прибуток, рентабельність.</p>
                                <ul>
                                    <li>Розподіл кількості продажів за продуктами</li>
                                    <li>Виручка за продуктами</li>
                                    <li>Рентабельність продуктів</li>
                                    <li>Детальна статистика по кожному продукту</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-users text-info me-2"></i> Звіт по клієнтах
                                </h5>
                                <p class="card-text">Аналіз активності клієнтів за обраний період. Включає інформацію про кількість замовлень, суму витрат, середній чек.</p>
                                <ul>
                                    <li>Топ клієнтів за сумою витрат</li>
                                    <li>Кількість замовлень кожного клієнта</li>
                                    <li>Середній чек клієнтів</li>
                                    <li>Дати першого та останнього замовлення</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-shopping-cart text-warning me-2"></i> Звіт по замовленнях
                                </h5>
                                <p class="card-text">Статистика замовлень за обраний період. Включає інформацію про кількість замовлень, статуси, суми, середній чек.</p>
                                <ul>
                                    <li>Динаміка кількості замовлень за період</li>
                                    <li>Розподіл замовлень за статусами</li>
                                    <li>Детальна інформація по кожному замовленню</li>
                                    <li>Загальні показники (сума, середній чек)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-warehouse text-danger me-2"></i> Звіт по складських запасах
                                </h5>
                                <p class="card-text">Звіт про поточні запаси на складі. Включає інформацію про наявність, вартість запасів, товари з низьким запасом.</p>
                                <ul>
                                    <li>Загальна кількість та вартість запасів</li>
                                    <li>Розподіл запасів за категоріями</li>
                                    <li>Детальна інформація по кожному товару</li>
                                    <li>Список товарів з низьким запасом</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>