<?php
// app/views/admin/orders/view.php
$title = 'Замовлення #' . $order['order_number'];

// Функції для форматування
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

function getPaymentMethodName($method) {
    $methodNames = [
        'credit_card' => 'Кредитна карта',
        'bank_transfer' => 'Банківський переказ',
        'cash_on_delivery' => 'Накладений платіж'
    ];
    return $methodNames[$method] ?? $method;
}

// Додаткові CSS стилі
$extra_css = '
<style>
    .order-card {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: none;
        margin-bottom: 20px;
    }
    
    .order-status {
        font-size: 1.25rem;
        font-weight: bold;
    }
    
    .order-items-table img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
    }
    
    .customer-info, .order-info {
        margin-bottom: 1rem;
    }
    
    .customer-info p, .order-info p {
        margin-bottom: 0.5rem;
    }
    
    @media print {
        .no-print {
            display: none !important;
        }
        
        body {
            font-size: 12pt;
        }
        
        .container {
            width: 100%;
            max-width: 100%;
        }
        
        .order-card {
            border: 1px solid #ddd;
            box-shadow: none !important;
        }
    }
</style>';

// Додаткові JS скрипти
$extra_js = '
<script>
    $(document).ready(function() {
        // Зміна статусу замовлення
        $("#updateStatusForm").on("submit", function(e) {
            const newStatus = $("#orderStatus").val();
            
            if (!confirm("Ви впевнені, що хочете змінити статус замовлення на \"" + $("#orderStatus option:selected").text() + "\"?")) {
                e.preventDefault();
                return false;
            }
            
            return true;
        });
        
        // Друк замовлення
        $("#printOrder").on("click", function() {
            window.print();
        });
    });
</script>';
?>

<div class="row mb-4 no-print">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Головна</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('orders') ?>">Замовлення</a></li>
                <li class="breadcrumb-item active"><?= $order['order_number'] ?></li>
            </ol>
        </nav>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <button id="printOrder" class="btn btn-outline-primary">
                <i class="fas fa-print me-1"></i> Друк
            </button>
            <a href="<?= base_url('orders') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> До списку
            </a>
            <?php if ($order['status'] != 'cancelled' && $order['status'] != 'delivered'): ?>
                <a href="<?= base_url('orders/cancel/' . $order['id']) ?>" class="btn btn-danger confirm-action" data-action-message="скасувати замовлення">
                    <i class="fas fa-ban me-1"></i> Скасувати
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Інформація про замовлення -->
        <div class="card order-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Інформація про замовлення</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="order-info">
                            <p><strong>Замовлення:</strong> #<?= $order['order_number'] ?></p>
                            <p><strong>Дата:</strong> <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
                            <p><strong>Статус:</strong> <span class="badge bg-<?= getStatusClass($order['status']) ?>"><?= getStatusName($order['status']) ?></span></p>
                            <p><strong>Спосіб оплати:</strong> <?= getPaymentMethodName($order['payment_method']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="customer-info">
                            <p><strong>Клієнт:</strong> <?= $order['first_name'] ?> <?= $order['last_name'] ?></p>
                            <p><strong>Email:</strong> <?= $order['email'] ?></p>
                            <p><strong>Телефон:</strong> <?= $order['phone'] ?? 'Не вказано' ?></p>
                            <p><strong>Адреса доставки:</strong> <?= $order['shipping_address'] ?></p>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($order['notes'])): ?>
                    <div class="mb-4">
                        <h6 class="mb-2">Примітки до замовлення:</h6>
                        <div class="p-3 bg-light rounded">
                            <?= $order['notes'] ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="no-print">
                    <h6 class="mb-2">Зміна статусу замовлення:</h6>
                    <form id="updateStatusForm" action="<?= base_url('orders/update_status/' . $order['id']) ?>" method="POST" class="row g-3">
                        <?= csrf_field() ?>
                        <div class="col-md-8">
                            <select name="status" id="orderStatus" class="form-select">
                                <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Очікує</option>
                                <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Обробляється</option>
                                <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Відправлено</option>
                                <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Доставлено</option>
                                <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Скасовано</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">Оновити статус</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Товари замовлення -->
        <div class="card order-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Товари замовлення</h5>
            </div>
            <div class="card-body">
                <!-- Обновление таблицы товаров в admin/orders/view.php -->
                <div class="table-responsive">
                    <table class="table table-hover order-items-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;"></th>
                                <th>Товар</th>
                                <th class="text-end">Об'єм</th>
                                <th class="text-end">Ціна за од.</th>
                                <th class="text-end">Кількість</th>
                                <th class="text-end">Сума</th>
                                <th class="no-print"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td>
                                        <img src="<?= $item['image'] ? upload_url($item['image']) : asset_url('images/no-image.jpg') ?>" 
                                            alt="<?= $item['product_name'] ?>">
                                    </td>
                                    <td>
                                        <?= $item['product_name'] ?>
                                        <?php if (!empty($item['container_id'])): ?>
                                            <br><small class="text-muted">ID тари: #<?= $item['container_id'] ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?= number_format($item['volume'] ?? 1, 2) ?> л
                                    </td>
                                    <td class="text-end">
                                        <?= number_format($item['price'], 2) ?> грн.
                                        <br><small class="text-muted"><?= number_format($item['price'] / ($item['volume'] ?? 1), 2) ?> грн/л</small>
                                    </td>
                                    <td class="text-end"><?= $item['quantity'] ?></td>
                                    <td class="text-end">
                                        <?= number_format($item['price'] * $item['quantity'], 2) ?> грн.
                                        <br><small class="text-muted"><?= number_format(($item['volume'] ?? 1) * $item['quantity'], 2) ?> л</small>
                                    </td>
                                    <td class="no-print">
                                        <a href="<?= base_url('products/view/' . $item['product_id']) ?>" 
                                        class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-active">
                                <td colspan="3" class="text-end"><strong>Загальна сума:</strong></td>
                                <td class="text-end">
                                    <?php 
                                    $totalVolume = 0;
                                    foreach ($orderItems as $item) {
                                        $totalVolume += ($item['volume'] ?? 1) * $item['quantity'];
                                    }
                                    ?>
                                    <strong><?= number_format($totalVolume, 2) ?> л</strong>
                                </td>
                                <td></td>
                                <td class="text-end"><strong><?= number_format($order['total_amount'], 2) ?> грн.</strong></td>
                                <td class="no-print"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Історія замовлення -->
        <div class="card order-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Історія замовлення</h5>
            </div>
            <div class="card-body">
                <!-- Тут буде відображатися історія змін статусів замовлення -->
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <span>Створення замовлення</span>
                            <span class="text-muted"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></span>
                        </div>
                        <div class="badge bg-<?= getStatusClass('pending') ?> mt-1">
                            <?= getStatusName('pending') ?>
                        </div>
                    </li>
                    
                    <?php 
                    // В реальному проекті тут потрібно додати код для відображення реальної історії замовлення
                    // з бази даних. Зараз це просто заглушка для демонстрації.
                    
                    // Приклад даних
                    $statusHistoryDemo = [];
                    
                    if ($order['status'] != 'pending') {
                        $statusHistoryDemo[] = [
                            'status' => 'processing',
                            'date' => date('Y-m-d H:i:s', strtotime($order['created_at'] . ' +2 hours')),
                            'user' => 'Admin User'
                        ];
                    }
                    
                    if ($order['status'] == 'shipped' || $order['status'] == 'delivered' || $order['status'] == 'cancelled') {
                        $statusHistoryDemo[] = [
                            'status' => 'shipped',
                            'date' => date('Y-m-d H:i:s', strtotime($order['created_at'] . ' +1 day')),
                            'user' => 'Admin User'
                        ];
                    }
                    
                    if ($order['status'] == 'delivered' || $order['status'] == 'cancelled') {
                        $statusHistoryDemo[] = [
                            'status' => $order['status'],
                            'date' => date('Y-m-d H:i:s', strtotime($order['created_at'] . ' +3 days')),
                            'user' => 'Admin User'
                        ];
                    }
                    
                    foreach ($statusHistoryDemo as $history): 
                    ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>Зміна статусу</span>
                                <span class="text-muted"><?= date('d.m.Y H:i', strtotime($history['date'])) ?></span>
                            </div>
                            <div class="badge bg-<?= getStatusClass($history['status']) ?> mt-1">
                                <?= getStatusName($history['status']) ?>
                            </div>
                            <div class="small text-muted mt-1">
                                Автор: <?= $history['user'] ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <!-- Інформація про клієнта -->
        <div class="card order-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Інформація про клієнта</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <img src="<?= asset_url('images/user-profile.png') ?>" alt="User Avatar" class="rounded-circle img-thumbnail" style="width: 100px;">
                    <h5 class="mt-2"><?= $order['first_name'] ?> <?= $order['last_name'] ?></h5>
                    <p class="text-muted">Клієнт</p>
                </div>
                
                <div class="mb-3">
                    <p><strong>Email:</strong> <?= $order['email'] ?></p>
                    <p><strong>Телефон:</strong> <?= $order['phone'] ?? 'Не вказано' ?></p>
                </div>
                
                <div class="d-grid gap-2 no-print">
                    <a href="<?= base_url('users/view/' . $order['customer_id']) ?>" class="btn btn-outline-primary">
                        <i class="fas fa-user me-1"></i> Профіль клієнта
                    </a>
                    <a href="<?= base_url('orders?customer_id=' . $order['customer_id']) ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-shopping-cart me-1"></i> Всі замовлення клієнта
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Нижня панель дій (тільки для друку) -->
<div class="mt-4 d-print-none no-print">
    <div class="card order-card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h5 class="mb-2">Примітка для друку</h5>
                    <p class="mb-0">При друку буде відображено тільки інформацію про замовлення та товари. Панель управління та кнопки дій будуть приховані.</p>
                </div>
                <div class="col-md-4 text-end">
                    <button id="printOrderBottom" class="btn btn-primary" onclick="window.print();">
                        <i class="fas fa-print me-1"></i> Друкувати замовлення
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>