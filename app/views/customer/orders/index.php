<?php
// app/views/customer/orders/index.php - Сторінка списку замовлень клієнта
$title = 'Мої замовлення';

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
    
    .order-card {
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .order-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
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
            <a href="<?= base_url('orders/create') ?>" class="d-none d-sm-inline-block btn btn-success shadow-sm">
                <i class="fas fa-plus-circle fa-sm text-white-50 me-1"></i> Створити нове замовлення
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Фільтри -->
    <div class="col-md-3 mb-4">
        <div class="card filter-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Фільтри</h5>
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
        
        <!-- Статистика замовлень -->
        <div class="card filter-card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Статистика</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Всього замовлень
                        <span class="badge bg-primary rounded-pill"><?= $pagination['total_items'] ?? 0 ?></span>
                    </li>
                    <?php
                    // Розрахунок статистики за статусами
                    $statusCounts = [];
                    foreach ($orders ?? [] as $order) {
                        if (!isset($statusCounts[$order['status']])) {
                            $statusCounts[$order['status']] = 0;
                        }
                        $statusCounts[$order['status']]++;
                    }
                    
                    // Відображення статистики за статусами
                    foreach ($statusCounts as $status => $count):
                    ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= getStatusName($status) ?>
                            <span class="badge bg-<?= getStatusClass($status) ?> rounded-pill"><?= $count ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Список замовлень -->
    <div class="col-md-9">
        <?php if (empty($orders)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> У вас ще немає замовлень. Створіть своє перше замовлення зараз!
            </div>
            <div class="text-center py-5">
                <img src="<?= asset_url('images/empty-orders.svg') ?>" alt="Немає замовлень" style="max-width: 200px; margin-bottom: 20px;">
                <h4>Ваш список замовлень порожній</h4>
                <p class="text-muted">Перегляньте наш каталог та зробіть замовлення</p>
                <a href="<?= base_url('products') ?>" class="btn btn-primary mt-3">
                    <i class="fas fa-shopping-basket me-1"></i> Перейти до каталогу
                </a>
            </div>
        <?php else: ?>
            <!-- Мобільний варіант (картки) -->
            <div class="d-md-none">
                <?php foreach ($orders as $order): ?>
                    <div class="card order-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="order-id"><?= $order['order_number'] ?></span>
                            <span class="badge bg-<?= getStatusClass($order['status']) ?>">
                                <?= getStatusName($order['status']) ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-6 text-muted">Дата:</div>
                                <div class="col-6"><?= date('d.m.Y', strtotime($order['created_at'])) ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6 text-muted">Сума:</div>
                                <div class="col-6 fw-bold"><?= number_format($order['total_amount'], 2) ?> грн.</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6 text-muted">Оплата:</div>
                                <div class="col-6"><?= getPaymentMethodName($order['payment_method']) ?></div>
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <a href="<?= base_url('orders/view/' . $order['id']) ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i> Деталі
                                </a>
                                
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('orders/print/' . $order['id']) ?>" class="btn btn-outline-secondary" title="Друкувати">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <?php if ($order['status'] == 'pending'): ?>
                                        <a href="<?= base_url('orders/cancel/' . $order['id']) ?>" class="btn btn-outline-danger confirm-delete" data-item-name="замовлення <?= $order['order_number'] ?>" title="Скасувати">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Десктопний варіант (таблиця) -->
            <div class="d-none d-md-block card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Історія замовлень</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover order-table">
                            <thead class="table-light">
                                <tr>
                                    <th>№ замовлення</th>
                                    <th>Дата</th>
                                    <th>Сума</th>
                                    <th>Статус</th>
                                    <th>Спосіб оплати</th>
                                    <th>Дії</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td class="order-id"><?= $order['order_number'] ?></td>
                                        <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                        <td><?= number_format($order['total_amount'], 2) ?> грн.</td>
                                        <td class="order-status">
                                            <span class="badge bg-<?= getStatusClass($order['status']) ?>">
                                                <?= getStatusName($order['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= getPaymentMethodName($order['payment_method']) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('orders/view/' . $order['id']) ?>" class="btn btn-outline-primary" title="Переглянути">
                                                    <i class="fas fa-eye"></i>
                                                </a>
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
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>