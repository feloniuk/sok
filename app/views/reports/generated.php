<?php
// app/views/reports/generated.php - Сторінка відображення згенерованого звіту

// Функція для форматування статусу замовлення
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

// Функція для отримання класу бейджа статусу
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

// Функція для форматування методу оплати
function getPaymentMethodName($method) {
    $methods = [
        'credit_card' => 'Кредитна картка',
        'bank_transfer' => 'Банківський переказ',
        'cash_on_delivery' => 'Накладений платіж'
    ];
    return $methods[$method] ?? $method;
}
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary"><?= $reportTitle ?></h6>
        <div>
            <a href="<?= base_url('reports/generate?report_type=' . $reportType . '&format=csv&' . http_build_query($filter)) ?>" class="btn btn-sm btn-outline-success">
                <i class="fas fa-file-csv"></i> Експорт CSV
            </a>
            <a href="<?= base_url('reports/generate?report_type=' . $reportType . '&format=excel&' . http_build_query($filter)) ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-file-excel"></i> Експорт Excel
            </a>
            <a href="<?= base_url('reports/generate?report_type=' . $reportType . '&format=pdf&' . http_build_query($filter)) ?>" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-file-pdf"></i> Експорт PDF
            </a>
            <a href="<?= base_url('reports/generate') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-cog"></i> Змінити параметри
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="mb-4">
            <div class="alert alert-info">
                <strong>Параметри звіту:</strong>
                <ul class="mb-0">
                    <li><strong>Тип звіту:</strong> <?= $reportTitle ?></li>
                    <li><strong>Період:</strong> <?= date('d.m.Y', strtotime($filter['start_date'])) ?> - <?= date('d.m.Y', strtotime($filter['end_date'])) ?></li>
                    <?php if (!empty($filter['category_id'])): ?>
                        <?php foreach ($categories as $category): ?>
                            <?php if ($category['id'] == $filter['category_id']): ?>
                                <li><strong>Категорія:</strong> <?= $category['name'] ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!empty($filter['status'])): ?>
                        <li><strong>Статус замовлення:</strong> <?= getStatusName($filter['status']) ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <?php if ($reportType == 'sales'): ?>
            <!-- Звіт по продажах -->
            <h4 class="mb-3">Загальні показники</h4>
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Загальна виручка</h5>
                            <h3 class="text-primary"><?= number_format($reportData['totals']['total_revenue'] ?? 0, 2) ?> грн</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Загальний прибуток</h5>
                            <h3 class="text-success"><?= number_format($reportData['totals']['total_profit'] ?? 0, 2) ?> грн</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Кількість проданих</h5>
                            <h3 class="text-info"><?= number_format($reportData['totals']['total_quantity'] ?? 0) ?> шт.</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Рентабельність</h5>
                            <?php
                            $profitMargin = 0;
                            if (isset($reportData['totals']['total_revenue']) && $reportData['totals']['total_revenue'] > 0) {
                                $profitMargin = ($reportData['totals']['total_profit'] / $reportData['totals']['total_revenue']) * 100;
                            }
                            ?>
                            <h3 class="text-warning"><?= number_format($profitMargin, 2) ?>%</h3>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="mb-3">Продажі за категоріями</h4>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Категорія</th>
                            <th class="text-end">Кількість</th>
                            <th class="text-end">Виручка (грн)</th>
                            <th class="text-end">Прибуток (грн)</th>
                            <th class="text-end">Рентабельність (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData['categories'] ?? [] as $category): ?>
                            <tr>
                                <td><?= $category['category_name'] ?></td>
                                <td class="text-end"><?= number_format($category['quantity']) ?></td>
                                <td class="text-end"><?= number_format($category['revenue'], 2) ?></td>
                                <td class="text-end"><?= number_format($category['profit'], 2) ?></td>
                                <td class="text-end">
                                    <?php
                                    $catMargin = 0;
                                    if ($category['revenue'] > 0) {
                                        $catMargin = ($category['profit'] / $category['revenue']) * 100;
                                    }
                                    echo number_format($catMargin, 2);
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($reportData['categories'])): ?>
                            <tr>
                                <td colspan="5" class="text-center">Немає даних</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h4 class="mb-3">Топ-10 продуктів за виручкою</h4>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Продукт</th>
                            <th class="text-end">Кількість</th>
                            <th class="text-end">Виручка (грн)</th>
                            <th class="text-end">Прибуток (грн)</th>
                            <th class="text-end">Частка від загальних продажів (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $productCount = 0;
                        foreach ($reportData['products'] ?? [] as $product): 
                            if ($productCount++ >= 10) break;
                        ?>
                            <tr>
                                <td><?= $product['product_name'] ?></td>
                                <td class="text-end"><?= number_format($product['quantity']) ?></td>
                                <td class="text-end"><?= number_format($product['revenue'], 2) ?></td>
                                <td class="text-end"><?= number_format($product['profit'], 2) ?></td>
                                <td class="text-end">
                                    <?php
                                    $sharePercent = 0;
                                    if (isset($reportData['totals']['total_revenue']) && $reportData['totals']['total_revenue'] > 0) {
                                        $sharePercent = ($product['revenue'] / $reportData['totals']['total_revenue']) * 100;
                                    }
                                    echo number_format($sharePercent, 2);
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($reportData['products'])): ?>
                            <tr>
                                <td colspan="5" class="text-center">Немає даних</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h4 class="mb-3">Щоденні продажі</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Дата</th>
                            <th class="text-end">Кількість</th>
                            <th class="text-end">Виручка (грн)</th>
                            <th class="text-end">Прибуток (грн)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData['daily'] ?? [] as $day): ?>
                            <tr>
                                <td><?= date('d.m.Y', strtotime($day['date'])) ?></td>
                                <td class="text-end"><?= number_format($day['quantity']) ?></td>
                                <td class="text-end"><?= number_format($day['revenue'], 2) ?></td>
                                <td class="text-end"><?= number_format($day['profit'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($reportData['daily'])): ?>
                            <tr>
                                <td colspan="4" class="text-center">Немає даних</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($reportType == 'products'): ?>
            <!-- Звіт по продуктах -->
            <h4 class="mb-3">Загальні показники</h4>
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Загальна кількість проданих товарів</h5>
                            <h3 class="text-primary"><?= number_format($reportData['totals']['quantity'] ?? 0) ?> шт.</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Загальна виручка</h5>
                            <h3 class="text-success"><?= number_format($reportData['totals']['revenue'] ?? 0, 2) ?> грн</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Загальний прибуток</h5>
                            <h3 class="text-info"><?= number_format($reportData['totals']['profit'] ?? 0, 2) ?> грн</h3>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="mb-3">Продукти за обраний період</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Продукт</th>
                            <th>Категорія</th>
                            <th class="text-end">Ціна (грн)</th>
                            <th class="text-end">Залишок</th>
                            <th class="text-end">Продано</th>
                            <th class="text-end">Виручка (грн)</th>
                            <th class="text-end">Прибуток (грн)</th>
                            <th class="text-end">Рентабельність (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData['products'] ?? [] as $product): ?>
                            <tr>
                                <td><?= $product['product_name'] ?></td>
                                <td><?= $product['category_name'] ?? '-' ?></td>
                                <td class="text-end"><?= number_format($product['price'], 2) ?></td>
                                <td class="text-end"><?= number_format($product['stock']) ?></td>
                                <td class="text-end"><?= number_format($product['quantity']) ?></td>
                                <td class="text-end"><?= number_format($product['revenue'], 2) ?></td>
                                <td class="text-end"><?= number_format($product['profit'], 2) ?></td>
                                <td class="text-end">
                                    <?php
                                    $prodMargin = 0;
                                    if ($product['revenue'] > 0) {
                                        $prodMargin = ($product['profit'] / $product['revenue']) * 100;
                                    }
                                    echo number_format($prodMargin, 2);
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($reportData['products'])): ?>
                            <tr>
                                <td colspan="8" class="text-center">Немає даних</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($reportData['products'])): ?>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="4">Всього</td>
                                <td class="text-end"><?= number_format($reportData['totals']['quantity'] ?? 0) ?></td>
                                <td class="text-end"><?= number_format($reportData['totals']['revenue'] ?? 0, 2) ?></td>
                                <td class="text-end"><?= number_format($reportData['totals']['profit'] ?? 0, 2) ?></td>
                                <td class="text-end">
                                    <?php
                                    $totalMargin = 0;
                                    if (isset($reportData['totals']['revenue']) && $reportData['totals']['revenue'] > 0) {
                                        $totalMargin = ($reportData['totals']['profit'] / $reportData['totals']['revenue']) * 100;
                                    }
                                    echo number_format($totalMargin, 2);
                                    ?>
                                </td>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>

        <?php elseif ($reportType == 'orders'): ?>
            <!-- Звіт по замовленнях -->
            <h4 class="mb-3">Загальні показники</h4>
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Загальна кількість замовлень</h5>
                            <h3 class="text-primary"><?= number_format($reportData['totals']['total_orders'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Загальна сума</h5>
                            <h3 class="text-success"><?= number_format($reportData['totals']['total_amount'] ?? 0, 2) ?> грн</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Середній чек</h5>
                            <h3 class="text-info"><?= number_format($reportData['totals']['average_order'] ?? 0, 2) ?> грн</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Унікальних клієнтів</h5>
                            <h3 class="text-warning"><?= number_format($reportData['totals']['unique_customers'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="mb-3">Розподіл замовлень за статусами</h4>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Статус</th>
                            <th class="text-end">Кількість</th>
                            <th class="text-end">Сума (грн)</th>
                            <th class="text-end">% від загальної кількості</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData['status'] ?? [] as $status): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-<?= getStatusClass($status['status']) ?>">
                                        <?= getStatusName($status['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end"><?= number_format($status['count']) ?></td>
                                <td class="text-end"><?= number_format($status['total_amount'], 2) ?></td>
                                <td class="text-end">
                                    <?php
                                    $statPercent = 0;
                                    if (isset($reportData['totals']['total_orders']) && $reportData['totals']['total_orders'] > 0) {
                                        $statPercent = ($status['count'] / $reportData['totals']['total_orders']) * 100;
                                    }
                                    echo number_format($statPercent, 2);
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($reportData['status'])): ?>
                            <tr>
                                <td colspan="4" class="text-center">Немає даних</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h4 class="mb-3">Щоденна динаміка замовлень</h4>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Дата</th>
                            <th class="text-end">Кількість замовлень</th>
                            <th class="text-end">Сума (грн)</th>
                            <th class="text-end">Середній чек (грн)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData['daily'] ?? [] as $day): ?>
                            <tr>
                                <td><?= date('d.m.Y', strtotime($day['date'])) ?></td>
                                <td class="text-end"><?= number_format($day['count']) ?></td>
                                <td class="text-end"><?= number_format($day['total_amount'], 2) ?></td>
                                <td class="text-end">
                                    <?php
                                    $avgOrder = 0;
                                    if ($day['count'] > 0) {
                                        $avgOrder = $day['total_amount'] / $day['count'];
                                    }
                                    echo number_format($avgOrder, 2);
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($reportData['daily'])): ?>
                            <tr>
                                <td colspan="4" class="text-center">Немає даних</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h4 class="mb-3">Деталі замовлень</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>№ замовлення</th>
                            <th>Клієнт</th>
                            <th>Статус</th>
                            <th>Метод оплати</th>
                            <th class="text-end">Сума (грн)</th>
                            <th>Дата</th>
                            <th>Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData['orders'] ?? [] as $order): ?>
                            <tr>
                                <td><?= $order['order_number'] ?></td>
                                <td><?= $order['customer_name'] ?></td>
                                <td>
                                    <span class="badge bg-<?= getStatusClass($order['status']) ?>">
                                        <?= getStatusName($order['status']) ?>
                                    </span>
                                </td>
                                <td><?= getPaymentMethodName($order['payment_method']) ?></td>
                                <td class="text-end"><?= number_format($order['total_amount'], 2) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <a href="<?= base_url('orders/view/' . $order['id']) ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($reportData['orders'])): ?>
                            <tr>
                                <td colspan="7" class="text-center">Немає даних</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($reportType == 'customers'): ?>
            <!-- Звіт по клієнтах -->
            <h4 class="mb-3">Загальні показники</h4>
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Всього клієнтів</h5>
                            <h3 class="text-primary"><?= number_format($reportData['totals']['total_customers'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Активних клієнтів</h5>
                            <h3 class="text-success"><?= number_format($reportData['totals']['total_active_customers'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Всього замовлень</h5>
                            <h3 class="text-info"><?= number_format($reportData['totals']['total_orders'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Середній чек</h5>
                            <h3 class="text-warning"><?= number_format($reportData['totals']['average_order'] ?? 0, 2) ?> грн</h3>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="mb-3">Дані по клієнтах</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Клієнт</th>
                            <th>Email</th>
                            <th>Телефон</th>
                            <th class="text-end">Кількість замовлень</th>
                            <th class="text-end">Загальна сума (грн)</th>
                            <th class="text-end">Середній чек (грн)</th>
                            <th>Перше замовлення</th>
                            <th>Останнє замовлення</th>
                            <th>Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData['customers'] ?? [] as $customer): ?>
                            <tr>
                                <td><?= $customer['first_name'] . ' ' . $customer['last_name'] ?></td>
                                <td><?= $customer['email'] ?></td>
                                <td><?= $customer['phone'] ?></td>
                                <td class="text-end"><?= number_format($customer['order_count']) ?></td>
                                <td class="text-end"><?= number_format($customer['total_amount'], 2) ?></td>
                                <td class="text-end"><?= number_format($customer['average_order'], 2) ?></td>
                                <td><?= date('d.m.Y', strtotime($customer['first_order_date'])) ?></td>
                                <td><?= date('d.m.Y', strtotime($customer['last_order_date'])) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('orders?customer_id=' . $customer['id']) ?>" class="btn btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= base_url('orders/create?customer_id=' . $customer['id']) ?>" class="btn btn-success">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($reportData['customers'])): ?>
                            <tr>
                                <td colspan="9" class="text-center">Немає даних</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($reportType == 'inventory'): ?>
            <!-- Звіт по складських запасах -->
            <h4 class="mb-3">Загальні показники</h4>
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Загальна кількість товарів</h5>
                            <h3 class="text-primary"><?= number_format($reportData['totals']['total_products'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Загальна кількість одиниць</h5>
                            <h3 class="text-success"><?= number_format($reportData['totals']['total_quantity'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Загальна вартість запасів</h5>
                            <h3 class="text-info"><?= number_format($reportData['totals']['total_value'] ?? 0, 2) ?> грн</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Товарів з низьким запасом</h5>
                            <h3 class="text-danger"><?= number_format($reportData['totals']['low_stock_products'] ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="mb-3">Розподіл запасів за категоріями</h4>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Категорія</th>
                            <th class="text-end">Кількість товарів</th>
                            <th class="text-end">Загальна кількість</th>
                            <th class="text-end">Загальна вартість (грн)</th>
                            <th class="text-end">% від загальної вартості</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData['categories'] ?? [] as $category): ?>
                            <tr>
                                <td><?= $category['name'] ?></td>
                                <td class="text-end"><?= number_format($category['product_count']) ?></td>
                                <td class="text-end"><?= number_format($category['total_quantity']) ?></td>
                                <td class="text-end"><?= number_format($category['total_value'], 2) ?></td>
                                <td class="text-end">
                                    <?php
                                    $catPercent = 0;
                                    if (isset($reportData['totals']['total_value']) && $reportData['totals']['total_value'] > 0) {
                                        $catPercent = ($category['total_value'] / $reportData['totals']['total_value']) * 100;
                                    }
                                    echo number_format($catPercent, 2);
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($reportData['categories'])): ?>
                            <tr>
                                <td colspan="5" class="text-center">Немає даних</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h4 class="mb-3">Список товарів на складі</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Назва товару</th>
                            <th>Категорія</th>
                            <th class="text-end">Ціна (грн)</th>
                            <th class="text-end">Кількість</th>
                            <th class="text-end">Загальна вартість (грн)</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData['inventory'] ?? [] as $product): ?>
                            <tr>
                                <td><?= $product['id'] ?></td>
                                <td><?= $product['name'] ?></td>
                                <td><?= $product['category_name'] ?? '-' ?></td>
                                <td class="text-end"><?= number_format($product['price'], 2) ?></td>
                                <td class="text-end"><?= number_format($product['stock_quantity']) ?></td>
                                <td class="text-end"><?= number_format($product['total_value'], 2) ?></td>
                                <td>
                                    <?php if ($product['stock_quantity'] <= 10): ?>
                                        <span class="badge bg-danger">Низький запас</span>
                                    <?php elseif ($product['stock_quantity'] <= 20): ?>
                                        <span class="badge bg-warning">Середній запас</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Достатній запас</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($reportData['inventory'])): ?>
                            <tr>
                                <td colspan="7" class="text-center">Немає даних</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <div class="alert alert-warning">
                <h4 class="alert-heading">Неправильний тип звіту</h4>
                <p>Вказаний тип звіту не підтримується системою. Будь ласка, оберіть інший тип звіту.</p>
                <hr>
                <p class="mb-0">
                    <a href="<?= base_url('reports/generate') ?>" class="btn btn-warning">
                        <i class="fas fa-arrow-left me-2"></i> Повернутися до вибору звіту
                    </a>
                </p>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-footer py-3">
        <div class="row">
            <div class="col-md-6">
                <small class="text-muted">
                    Звіт сформовано: <?= date('d.m.Y H:i:s') ?>
                </small>
            </div>
            <div class="col-md-6 text-end">
                <small class="text-muted">
                    Користувач: <?= get_current_user_name() ?>
                </small>
            </div>
        </div>
    </div>
</div>