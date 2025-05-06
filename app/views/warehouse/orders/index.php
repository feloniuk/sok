<?php
// app/views/warehouse/orders/index.php - Сторінка списку замовлень для менеджера складу
$title = 'Управління замовленнями';

// Функція для перетворення статусів замовлень
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
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3 mb-0 text-gray-800">Управління замовленнями</h1>
        <p class="text-muted">Перегляд та обробка замовлень для відвантаження</p>
    </div>
</div>

<div class="row">
    <!-- Фільтри -->
    <div class="col-md-3 mb-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="m-0">Фільтри</h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('orders') ?>" method="GET">
                    <!-- Статус замовлення -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Статус замовлення</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Всі статуси</option>
                            <option value="pending" <?= isset($_GET['status']) && $_GET['status'] == 'pending' ? 'selected' : '' ?>>Очікує</option>
                            <option value="processing" <?= isset($_GET['status']) && $_GET['status'] == 'processing' ? 'selected' : '' ?>>Обробляється</option>
                            <option value="shipped" <?= isset($_GET['status']) && $_GET['status'] == 'shipped' ? 'selected' : '' ?>>Відправлено</option>
                            <option value="delivered" <?= isset($_GET['status']) && $_GET['status'] == 'delivered' ? 'selected' : '' ?>>Доставлено</option>
                            <option value="cancelled" <?= isset($_GET['status']) && $_GET['status'] == 'cancelled' ? 'selected' : '' ?>>Скасовано</option>
                        </select>
                    </div>
                    
                    <!-- Номер замовлення -->
                    <div class="mb-3">
                        <label for="order_number" class="form-label">Номер замовлення</label>
                        <input type="text" class="form-control" id="order_number" name="order_number" 
                               value="<?= $_GET['order_number'] ?? '' ?>" placeholder="Введіть номер...">
                    </div>
                    
                    <!-- Період -->
                    <div class="mb-3">
                        <label class="form-label">Період</label>
                        <div class="row g-2">
                            <div class="col">
                                <input type="date" class="form-control" name="date_from" 
                                       value="<?= $_GET['date_from'] ?? '' ?>" placeholder="З">
                            </div>
                            <div class="col">
                                <input type="date" class="form-control" name="date_to" 
                                       value="<?= $_GET['date_to'] ?? '' ?>" placeholder="По">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Кнопки -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Застосувати
                        </button>
                        <a href="<?= base_url('orders') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Скинути
                        </a>
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
            </div>
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Замовлення не знайдені за вказаними критеріями.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>№ замовлення</th>
                                    <th>Клієнт</th>
                                    <th>Статус</th>
                                    <th>Сума</th>
                                    <th>Дата</th>
                                    <th>Дії</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?= $order['order_number'] ?></td>
                                        <td><?= $order['first_name'] . ' ' . $order['last_name'] ?></td>
                                        <td>
                                            <span class="badge bg-<?= getStatusClass($order['status']) ?>">
                                                <?= getStatusName($order['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= number_format($order['total_amount'], 2) ?> грн.</td>
                                        <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('orders/view/' . $order['id']) ?>" class="btn btn-info" title="Перегляд">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($order['status'] == 'pending'): ?>
                                                    <a href="<?= base_url('orders/process/' . $order['id']) ?>" class="btn btn-primary" title="Обробити">
                                                        <i class="fas fa-box"></i>
                                                    </a>
                                                <?php elseif ($order['status'] == 'processing'): ?>
                                                    <a href="<?= base_url('orders/ship/' . $order['id']) ?>" class="btn btn-success" title="Відвантажити">
                                                        <i class="fas fa-truck"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?= base_url('orders/print/' . $order['id']) ?>" class="btn btn-secondary" title="Друк">
                                                    <i class="fas fa-print"></i>
                                                </a>
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

<script>
    const baseUrl = "<?= base_url() ?>";
</script>