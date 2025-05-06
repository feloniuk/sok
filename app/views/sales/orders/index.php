<?php
// app/views/sales/orders/index.php - Список замовлень для менеджера продажів

// Визначення статусів замовлень
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

// Визначення класів для статусів
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
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Замовлення</h6>
        <a href="<?= base_url('orders/create') ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-plus-circle"></i> Створити замовлення
        </a>
    </div>
    <div class="card-body">
        <!-- Фільтри -->
        <form method="get" class="mb-4">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label for="status">Статус</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Всі статуси</option>
                        <option value="pending" <?= isset($filters['status']) && $filters['status'] == 'pending' ? 'selected' : '' ?>>Очікує</option>
                        <option value="processing" <?= isset($filters['status']) && $filters['status'] == 'processing' ? 'selected' : '' ?>>Обробляється</option>
                        <option value="shipped" <?= isset($filters['status']) && $filters['status'] == 'shipped' ? 'selected' : '' ?>>Відправлено</option>
                        <option value="delivered" <?= isset($filters['status']) && $filters['status'] == 'delivered' ? 'selected' : '' ?>>Доставлено</option>
                        <option value="cancelled" <?= isset($filters['status']) && $filters['status'] == 'cancelled' ? 'selected' : '' ?>>Скасовано</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label for="customer_id">Клієнт</label>
                    <select name="customer_id" id="customer_id" class="form-select">
                        <option value="">Всі клієнти</option>
                        <?php foreach ($customers ?? [] as $customer): ?>
                            <option value="<?= $customer['id'] ?>" <?= isset($filters['customer_id']) && $filters['customer_id'] == $customer['id'] ? 'selected' : '' ?>>
                                <?= $customer['first_name'] . ' ' . $customer['last_name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label for="date_from">З дати</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="<?= $filters['date_from'] ?? '' ?>">
                </div>
                <div class="col-md-2 mb-2">
                    <label for="date_to">По дату</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="<?= $filters['date_to'] ?? '' ?>">
                </div>
                <div class="col-md-2 mb-2">
                    <label for="order_number">№ замовлення</label>
                    <input type="text" name="order_number" id="order_number" class="form-control" value="<?= $filters['order_number'] ?? '' ?>">
                </div>
                <div class="col-md-12 mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Фільтрувати
                    </button>
                    <a href="<?= base_url('orders') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i> Скинути
                    </a>
                </div>
            </div>
        </form>

        <!-- Таблиця замовлень -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>№ замовлення</th>
                        <th>Клієнт</th>
                        <th>Сума</th>
                        <th>Статус</th>
                        <th>Спосіб оплати</th>
                        <th>Дата</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['order_number'] ?></td>
                            <td><?= $order['first_name'] . ' ' . $order['last_name'] ?></td>
                            <td><?= number_format($order['total_amount'], 2) ?> грн</td>
                            <td>
                                <span class="badge bg-<?= getStatusClass($order['status']) ?>">
                                    <?= getStatusName($order['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $paymentMethods = [
                                    'credit_card' => 'Кредитна картка',
                                    'bank_transfer' => 'Банківський переказ',
                                    'cash_on_delivery' => 'Накладений платіж'
                                ];
                                echo $paymentMethods[$order['payment_method']] ?? $order['payment_method'];
                                ?>
                            </td>
                            <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('orders/view/' . $order['id']) ?>" class="btn btn-info" title="Перегляд">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= base_url('orders/print/' . $order['id']) ?>" class="btn btn-secondary" title="Друк" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <?php if ($order['status'] == 'pending'): ?>
                                        <form action="<?= base_url('orders/update_status/' . $order['id']) ?>" method="post" style="display: inline;">
                                            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
                                            <input type="hidden" name="status" value="processing">
                                            <button type="submit" class="btn btn-primary" title="Прийняти в обробку">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if (in_array($order['status'], ['pending', 'processing'])): ?>
                                        <a href="<?= base_url('orders/cancel/' . $order['id']) ?>" class="btn btn-danger" title="Скасувати" 
                                           onclick="return confirm('Ви впевнені, що хочете скасувати це замовлення?');">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Замовлень не знайдено</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Пагінація -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($pagination['current_page'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= base_url('orders?page=' . ($pagination['current_page'] - 1)) ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="<?= base_url('orders?page=' . $i) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= base_url('orders?page=' . ($pagination['current_page'] + 1)) ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>