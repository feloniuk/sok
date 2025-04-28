<?php
// app/views/orders/index.php - Сторінка списку замовлень
$title = 'Список замовлень';

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

// Функція для перетворення статусів
function getStatusName($status) {
    $statusNames = [
        'pending' => 'Очікує обробки',
        'processing' => 'В обробці',
        'shipped' => 'Відправлено',
        'delivered' => 'Доставлено',
        'cancelled' => 'Скасовано'
    ];
    return $statusNames[$status] ?? $status;
}

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

// Функція для перетворення методів оплати
function getPaymentMethodName($method) {
    $methodNames = [
        'credit_card' => 'Кредитна картка',
        'bank_transfer' => 'Банківський переказ',
        'cash_on_delivery' => 'Оплата при отриманні'
    ];
    return $methodNames[$method] ?? $method;
}

// Підключення додаткових CSS
$extra_css = '
<style>
    .filter-card {
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .order-table th, .order-table td {
        vertical-align: middle;
    }
    
    .order-id {
        font-weight: bold;
        white-space: nowrap;
    }
    
    .order-status {
        white-space: nowrap;
    }
</style>';

// Підключення додаткових JS
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
        
        // Фільтрація замовлень при зміні параметрів
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
    });
</script>';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
            <?php if (is_logged_in() && has_role(['admin', 'sales_manager', 'customer'])): ?>
                <a href="<?= base_url('orders/create') ?>" class="d-none d-sm-inline-block btn btn-success shadow-sm">
                    <i class="fas fa-plus-circle fa-sm text-white-50 me-1"></i> Створити нове замовлення
                </a>
            <?php endif; ?>
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
                <form id="filterForm" action="<?= base_url('orders') ?>" method="GET">
                    <!-- Пошук за номером -->
                    <div class="mb-3">
                        <label for="order_number" class="form-label">Номер замовлення</label>
                        <input type="text" class="form-control filter-control" id="order_number" name="order_number" value="<?= $_GET['order_number'] ?? '' ?>" placeholder="Введіть номер замовлення...">
                    </div>
                    
                    <!-- Статус -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Статус</label>
                        <select class="form-select filter-control" id="status" name="status">
                            <option value="">Всі статуси</option>
                            <option value="pending" <?= isset($_GET['status']) && $_GET['status'] == 'pending' ? 'selected' : '' ?>>Очікує обробки</option>
                            <option value="processing" <?= isset($_GET['status']) && $_GET['status'] == 'processing' ? 'selected' : '' ?>>В обробці</option>
                            <option value="shipped" <?= isset($_GET['status']) && $_GET['status'] == 'shipped' ? 'selected' : '' ?>>Відправлено</option>
                            <option value="delivered" <?= isset($_GET['status']) && $_GET['status'] == 'delivered' ? 'selected' : '' ?>>Доставлено</option>
                            <option value="cancelled" <?= isset($_GET['status']) && $_GET['status'] == 'cancelled' ? 'selected' : '' ?>>Скасовано</option>
                        </select>
                    </div>
                    
                    <?php if (has_role(['admin', 'sales_manager']) && !empty($customers)): ?>
                    <!-- Клієнт (тільки для адміна і менеджера) -->
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Клієнт</label>
                        <select class="form-select filter-control" id="customer_id" name="customer_id">
                            <option value="">Всі клієнти</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= $customer['id'] ?>" <?= isset($_GET['customer_id']) && $_GET['customer_id'] == $customer['id'] ? 'selected' : '' ?>>
                                    <?= $customer['first_name'] . ' ' . $customer['last_name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Період -->
                    <div class="mb-3">
                        <label class="form-label">Період</label>
                        <div class="row g-2">
                            <div class="col">
                                <input type="text" class="form-control filter-control datepicker" name="date_from" placeholder="З" value="<?= $_GET['date_from'] ?? '' ?>">
                            </div>
                            <div class="col">
                                <input type="text" class="form-control filter-control datepicker" name="date_to" placeholder="По" value="<?= $_GET['date_to'] ?? '' ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Кнопки -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Застосувати фільтри
                        </button>
                        <button type="button" id="resetFilters" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Скинути фільтри
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Таблиця замовлень -->
    <div class="col-md-9">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Замовлення</h6>
                <div class="dropdown">
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-export me-1"></i> Експорт
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                        <li><a class="dropdown-item" href="<?= base_url('reports/export_orders?format=csv') . '&' . http_build_query($_GET) ?>">CSV</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('reports/export_orders?format=excel') . '&' . http_build_query($_GET) ?>">Excel</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('reports/export_orders?format=pdf') . '&' . http_build_query($_GET) ?>">PDF</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Замовлення не знайдені. Спробуйте змінити параметри фільтрації.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover order-table">
                            <thead class="table-light">
                                <tr>
                                    <th>№ замовлення</th>
                                    <th>Клієнт</th>
                                    <th>Сума</th>
                                    <th>Спосіб оплати</th>
                                    <th>Статус</th>
                                    <th>Дата</th>
                                    <th>Дії</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td class="order-id"><?= $order['order_number'] ?></td>
                                        <td><?= $order['first_name'] . ' ' . $order['last_name'] ?></td>
                                        <td><?= number_format($order['total_amount'], 2) ?> грн.</td>
                                        <td><?= getPaymentMethodName($order['payment_method']) ?></td>
                                        <td class="order-status">
                                            <span class="badge bg-<?= getStatusClass($order['status']) ?>">
                                                <?= getStatusName($order['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('orders/view/' . $order['id']) ?>" class="btn btn-outline-primary" title="Переглянути">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if (has_role(['admin', 'sales_manager']) && $order['status'] == 'pending'): ?>
                                                    <a href="<?= base_url('orders/update_status/' . $order['id']) ?>" class="btn btn-outline-info" title="Обробити">
                                                        <i class="fas fa-cog"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?= base_url('orders/print/' . $order['id']) ?>" class="btn btn-outline-secondary" title="Друкувати">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                <?php if ($order['status'] == 'pending'): ?>
                                                    <a href="<?= base_url('orders/cancel/' . $order['id']) ?>" class="btn btn-outline-danger confirm-delete" data-item-name="замовлення <?= $order['order_number'] ?>" title="Скасувати">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
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
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>