<?php
// app/views/warehouse/orders/view.php - Сторінка перегляду деталей замовлення для менеджера складу
$title = 'Перегляд замовлення #' . $order['order_number'];

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

// Підключення додаткових CSS стилів
$extra_css = '
<style>
    .product-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
    }
    
    .order-details {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
    }
    
    .status-badge {
        font-size: 1rem;
        padding: 5px 10px;
    }
</style>';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('warehouse') ?>">Панель складу</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('orders') ?>">Замовлення</a></li>
                <li class="breadcrumb-item active"><?= $order['order_number'] ?></li>
            </ol>
        </nav>
    </div>
    <div class="col-md-6 text-end">
        <div class="btn-group">
            <a href="<?= base_url('orders/print/' . $order['id']) ?>" class="btn btn-outline-secondary">
                <i class="fas fa-print me-1"></i> Друк замовлення
            </a>
            <?php if ($order['status'] == 'pending'): ?>
                <a href="<?= base_url('orders/process/' . $order['id']) ?>" class="btn btn-outline-primary">
                    <i class="fas fa-box me-1"></i> Обробити замовлення
                </a>
            <?php elseif ($order['status'] == 'processing'): ?>
                <a href="<?= base_url('orders/ship/' . $order['id']) ?>" class="btn btn-outline-success">
                    <i class="fas fa-truck me-1"></i> Відвантажити замовлення
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="m-0 font-weight-bold">Товари в замовленні</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 60px;"></th>
                                <th>Найменування</th>
                                <th class="text-end">Ціна</th>
                                <th class="text-center">Кількість</th>
                                <th class="text-end">Сума</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td>
                                        <img src="<?= $item['image'] ? upload_url($item['image']) : asset_url('images/no-image.jpg') ?>" 
                                             alt="<?= $item['product_name'] ?>" class="product-image">
                                    </td>
                                    <td><?= $item['product_name'] ?></td>
                                    <td class="text-end"><?= number_format($item['price'], 2) ?> грн.</td>
                                    <td class="text-center"><?= $item['quantity'] ?></td>
                                    <td class="text-end"><?= number_format($item['price'] * $item['quantity'], 2) ?> грн.</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <td colspan="4" class="text-end"><strong>Загальна сума:</strong></td>
                                <td class="text-end"><strong><?= number_format($order['total_amount'], 2) ?> грн.</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Форма зміни статусу -->
        <div class="card shadow mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="m-0 font-weight-bold">Зміна статусу замовлення</h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('orders/update_status/' . $order['id']) ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="row">
                        <div class="col-md-8">
                            <select name="status" class="form-select">
                                <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Очікує</option>
                                <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Обробляється</option>
                                <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Відправлено</option>
                                <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Доставлено</option>
                                <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Скасовано</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save me-1"></i> Оновити статус
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Інформація про замовлення -->
        <div class="card shadow mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="m-0 font-weight-bold">Інформація про замовлення</h5>
            </div>
            <div class="card-body">
                <div class="order-details mb-3">
                    <p><strong>Номер замовлення:</strong> <?= $order['order_number'] ?></p>
                    <p>
                        <strong>Статус:</strong> 
                        <span class="badge bg-<?= getStatusClass($order['status']) ?> status-badge">
                            <?= getStatusName($order['status']) ?>
                        </span>
                    </p>
                    <p><strong>Дата замовлення:</strong> <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
                    <p><strong>Спосіб оплати:</strong> 
                        <?php 
                        $paymentMethods = [
                            'credit_card' => 'Банківська карта',
                            'bank_transfer' => 'Банківський переказ',
                            'cash_on_delivery' => 'Оплата при доставці'
                        ];
                        echo $paymentMethods[$order['payment_method']] ?? $order['payment_method'];
                        ?>
                    </p>
                </div>
                
                <h6 class="fw-bold">Інформація про клієнта</h6>
                <div class="order-details mb-3">
                    <p><strong>Ім'я:</strong> <?= $order['first_name'] . ' ' . $order['last_name'] ?></p>
                    <p><strong>Email:</strong> <?= $order['email'] ?></p>
                    <p><strong>Телефон:</strong> <?= $order['phone'] ?></p>
                </div>
                
                <h6 class="fw-bold">Інформація про доставку</h6>
                <div class="order-details">
                    <p><strong>Адреса доставки:</strong><br> 
                        <?= nl2br($order['shipping_address']) ?>
                    </p>
                    <?php if (!empty($order['notes'])): ?>
                        <p><strong>Примітки:</strong><br>
                            <?= nl2br($order['notes']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>