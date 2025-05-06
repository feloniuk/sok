<?php
// app/views/admin/orders/index.php - Список замовлень для адміністратора

$title = 'Управління замовленнями';

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

// Функції для перетворення статусів
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

// Додаткові CSS стилі
$extra_css = '
<style>
    .filter-card {
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
});
</script>';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <p class="text-muted">Загальна кількість замовлень: <?= $pagination['total_items'] ?? 0 ?></p>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?= base_url('admin/orders/create') ?>" class="btn btn-success">
            <i class="fas fa-plus-circle me-1"></i> Створити замовлення
        </a>
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
                <form id="filterForm" action="<?= base_url('admin/orders') ?>" method="GET">
                    <!-- Статус замовлення -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Статус</label>
                        <select class="form-select filter-control" id="status" name="status">
                            <option value="">Всі статуси</option>
                            <option value="pending" <?= isset($_GET['status']) && $_GET['status'] == 'pending' ? 'selected' : '' ?>>Очікує</option>
                            <option value="processing" <?= isset($_GET['status']) && $_GET['status'] == 'processing' ? 'selected' : '' ?>>Обробляється</option>
                            <option value="shipped" <?= isset($_GET['status']) && $_GET['status'] == 'shipped' ? 'selected' : '' ?>>Відправлено</option>
                            <option value="delivered" <?= isset($_GET['status']) && $_GET['status'] == 'delivered' ? 'selected' : '' ?>>Доставлено</option>
                            <option value="cancelled" <?= isset($_GET['status']) && $_GET['status'] == 'cancelled' ? 'selected' : '' ?>>Скасовано</option>
                        </select>
                    </div>
                    
                    <!-- Клієнт -->
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Клієнт</label>
                        <select class="form-select filter-control" id="customer_id" name="customer_id">
                            <option value="">Всі клієнти</option>
                            <?php foreach ($customers ?? [] as $customer): ?>
                                <option value="<?= $customer['id'] ?>" <?= isset($_GET['customer_id']) && $_GET['customer_id'] == $customer['id'] ? 'selected' : '' ?>>
                                    <?= $customer['first_name'] . ' ' . $customer['last_name'] ?> (<?= $customer['email'] ?>)
                                </option>
                            <?php endforeach; ?>
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
                    
                    <!-- Номер замовлення -->
                    <div class="mb-3">
                        <label for="order_number" class="form-label">Номер замовлення</label>
                        <input type="text" class="form-control filter-control" id="order_number" name="order_number" value="<?= $_GET['order_number'] ?? '' ?>" placeholder="Введіть номер">
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
    
    <!-- Список замовлень -->
    <div class="col-md-9">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Список замовлень</h6>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-export me-1"></i> Експорт
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= base_url('admin/orders/export?format=csv') . '&' . http_build_query($_GET) ?>">CSV</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('admin/orders/export?format=excel') . '&' . http_build_query($_GET) ?>">Excel</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('admin/orders/export?format=pdf') . '&' . http_build_query($_GET) ?>">PDF</a></li>
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
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Номер</th>
                                    <th>Клієнт</th>
                                    <th>Сума</th>
                                    <th>Статус</th>
                                    <th>Дата</th>
                                    <th>Дії</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?= $order['order_number'] ?></td>
                                        <td><?= $order['first_name'] . ' ' . $order['last_name'] ?></td>
                                        <td><?= number_format($order['total_amount'], 2) ?> грн.</td>
                                        <td>
                                            <span class="badge bg-<?= getStatusClass($order['status']) ?>">
                                                <?= getStatusName($order['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('admin/orders/view/' . $order['id']) ?>" class="btn btn-outline-primary" title="Перегляд">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($order['status'] != 'delivered' && $order['status'] != 'cancelled'): ?>
                                                    <a href="<?= base_url('admin/orders/edit/' . $order['id']) ?>" class="btn btn-outline-warning" title="Редагування">
                                                        <i class="fas fa-edit"></i>
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