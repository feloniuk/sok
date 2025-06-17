<?php
// app/views/orders/view.php - Обновленная страница просмотра заказа с поддержкой контейнеров
$title = 'Замовлення №' . $order['order_number'];

// Функции для форматирования
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

function getPaymentMethodName($method) {
    $methodNames = [
        'credit_card' => 'Кредитна картка',
        'bank_transfer' => 'Банківський переказ',
        'cash_on_delivery' => 'Оплата при отриманні'
    ];
    return $methodNames[$method] ?? $method;
}

// Подключение дополнительных CSS
$extra_css = '
<style>
    .order-status-timeline {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        position: relative;
    }
    
    .order-status-timeline::before {
        content: "";
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 4px;
        background-color: #e9ecef;
        z-index: 1;
    }
    
    .status-step {
        position: relative;
        z-index: 2;
        text-align: center;
        flex: 1;
    }
    
    .status-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #e9ecef;
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 0 auto 10px;
    }
    
    .status-icon i {
        color: #fff;
    }
    
    .status-text {
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    .status-active .status-icon {
        background-color: #007bff;
    }
    
    .status-active .status-text {
        color: #000;
        font-weight: 500;
    }
    
    .status-completed .status-icon {
        background-color: #28a745;
    }
    
    .status-cancelled .status-icon {
        background-color: #dc3545;
    }
    
    .order-detail-card {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: none;
    }
    
    .product-row {
        align-items: center;
    }
    
    .product-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
    }
    
    .container-info {
        background-color: #f8f9fa;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 0.85rem;
        margin-top: 5px;
    }
    
    .volume-badge {
        background: #007bff;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: bold;
        margin-right: 8px;
    }
    
    .price-per-liter {
        color: #6c757d;
        font-size: 0.8rem;
    }
    
    .order-summary-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 0.5rem;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 1px solid rgba(255,255,255,0.2);
    }
    
    .summary-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        font-weight: bold;
        font-size: 1.1rem;
    }
</style>';
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Головна</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('orders') ?>">Замовлення</a></li>
                <li class="breadcrumb-item active"><?= $order['order_number'] ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h2 mb-0">
            <?= $title ?>
            <span class="badge bg-<?= getStatusClass($order['status']) ?> ms-2">
                <?= getStatusName($order['status']) ?>
            </span>
        </h1>
        <p class="text-muted">
            Створено: <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?>
            <?php if ($order['created_at'] != $order['updated_at']): ?>
                | Оновлено: <?= date('d.m.Y H:i', strtotime($order['updated_at'])) ?>
            <?php endif; ?>
        </p>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <a href="<?= base_url('orders/print/' . $order['id']) ?>" class="btn btn-outline-secondary" target="_blank">
                <i class="fas fa-print me-1"></i> Друк
            </a>
            <?php if ($order['status'] == 'pending' && (has_role(['admin', 'sales_manager']) || 
                (has_role('customer') && $order['customer_id'] == get_current_user_id()))): ?>
                <a href="<?= base_url('orders/cancel/' . $order['id']) ?>" class="btn btn-outline-danger confirm-delete" data-item-name="замовлення <?= $order['order_number'] ?>">
                    <i class="fas fa-times me-1"></i> Скасувати
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Шкала статусов -->
<?php if ($order['status'] != 'cancelled'): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card order-detail-card">
            <div class="card-body">
                <div class="order-status-timeline">
                    <?php
                    $statuses = ['pending', 'processing', 'shipped', 'delivered'];
                    $statusLabels = [
                        'pending' => 'Очікує обробки',
                        'processing' => 'В обробці',
                        'shipped' => 'Відправлено',
                        'delivered' => 'Доставлено'
                    ];
                    $statusIcons = [
                        'pending' => 'fa-clock',
                        'processing' => 'fa-cog',
                        'shipped' => 'fa-truck',
                        'delivered' => 'fa-check'
                    ];
                    
                    $currentStatusIndex = array_search($order['status'], $statuses);
                    
                    foreach ($statuses as $index => $status):
                        $statusClass = '';
                        if ($index < $currentStatusIndex) {
                            $statusClass = 'status-completed';
                        } elseif ($index == $currentStatusIndex) {
                            $statusClass = 'status-active';
                        }
                    ?>
                    <div class="status-step <?= $statusClass ?>">
                        <div class="status-icon">
                            <i class="fas <?= $statusIcons[$status] ?>"></i>
                        </div>
                        <div class="status-text"><?= $statusLabels[$status] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (has_role(['admin', 'sales_manager', 'warehouse_manager'])): ?>
                <div class="text-center">
                    <form action="<?= base_url('orders/update_status/' . $order['id']) ?>" method="POST" class="d-inline-block">
                        <?= csrf_field() ?>
                        <div class="input-group">
                            <select name="status" class="form-select">
                                <option value="">Змінити статус...</option>
                                <?php foreach ($statuses as $status): ?>
                                    <?php if ($status != $order['status']): ?>
                                        <option value="<?= $status ?>"><?= $statusLabels[$status] ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <?php if ($order['status'] != 'cancelled'): ?>
                                    <option value="cancelled">Скасувати</option>
                                <?php endif; ?>
                            </select>
                            <button type="submit" class="btn btn-primary">Застосувати</button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <!-- Информация о заказе -->
    <div class="col-md-4 mb-4">
        <div class="card order-detail-card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Інформація про замовлення</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="fw-bold">Клієнт:</h6>
                    <p class="mb-1"><?= $order['first_name'] . ' ' . $order['last_name'] ?></p>
                    <p class="mb-1"><i class="fas fa-envelope me-1"></i> <?= $order['email'] ?></p>
                    <?php if (!empty($order['phone'])): ?>
                        <p class="mb-0"><i class="fas fa-phone me-1"></i> <?= $order['phone'] ?></p>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <h6 class="fw-bold">Адреса доставки:</h6>
                    <p class="mb-0"><?= nl2br($order['shipping_address']) ?></p>
                </div>
                <div class="mb-3">
                    <h6 class="fw-bold">Спосіб оплати:</h6>
                    <p class="mb-0"><?= getPaymentMethodName($order['payment_method']) ?></p>
                </div>
                <?php if (!empty($order['notes'])): ?>
                    <div class="mb-0">
                        <h6 class="fw-bold">Примітки:</h6>
                        <p class="mb-0"><?= nl2br($order['notes']) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Товары в заказе -->
    <div class="col-md-5 mb-4">
        <div class="card order-detail-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Товари в замовленні</h5>
            </div>
            <div class="card-body">
                <?php foreach ($orderItems as $item): ?>
                    <div class="product-row mb-3 pb-3 <?= !end($orderItems) || array_search($item, $orderItems) !== count($orderItems) - 1 ? 'border-bottom' : '' ?>">
                        <div class="d-flex">
                            <img src="<?= $item['image'] ? upload_url($item['image']) : asset_url('images/no-image.jpg') ?>" 
                                 alt="<?= $item['display_name'] ?>" 
                                 class="product-image me-3">
                            
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    <a href="<?= base_url('products/view/' . $item['product_id']) ?>" class="text-decoration-none">
                                        <?= $item['product_name'] ?>
                                    </a>
                                </h6>
                                
                                <?php if (!empty($item['container_id'])): ?>
                                    <div class="container-info">
                                        <span class="volume-badge"><?= $item['volume'] ?> л</span>
                                        <span class="price-per-liter">
                                            <?= number_format($item['price_per_liter'], 2) ?> грн/л
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div>
                                        <span class="fw-bold"><?= number_format($item['price'], 2) ?> грн</span>
                                        <span class="text-muted">× <?= $item['quantity'] ?></span>
                                    </div>
                                    <div class="text-end">
                                        <strong><?= number_format($item['price'] * $item['quantity'], 2) ?> грн</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Сводка заказа -->
    <div class="col-md-3 mb-4">
        <div class="card order-summary-card text-white">
            <div class="card-header border-0">
                <h5 class="mb-0">Підсумок замовлення</h5>
            </div>
            <div class="card-body">
                <?php
                // Получаем статистику заказа
                $orderItemModel = new OrderItem();
                $orderStats = $orderItemModel->getOrderVolumeStats($order['id']);
                ?>
                
                <div class="summary-item">
                    <span>Кількість товарів:</span>
                    <span><?= $orderStats['total_items'] ?? 0 ?> шт.</span>
                </div>
                
                <div class="summary-item">
                    <span>Унікальних продуктів:</span>
                    <span><?= $orderStats['unique_products'] ?? 0 ?></span>
                </div>
                
                <div class="summary-item">
                    <span>Загальний об'єм:</span>
                    <span><?= number_format($orderStats['total_volume'] ?? 0, 2) ?> л</span>
                </div>
                
                <div class="summary-item">
                    <span>Середня ціна за літр:</span>
                    <span><?= number_format($orderStats['avg_price_per_liter'] ?? 0, 2) ?> грн/л</span>
                </div>
                
                <div class="summary-item">
                    <span>Загальна сума:</span>
                    <span><?= number_format($order['total_amount'], 2) ?> грн</span>
                </div>
            </div>
        </div>
        
        <!-- Рекомендации -->
        <?php if (has_role(['admin', 'sales_manager'])): ?>
        <div class="card mt-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">Рекомендації</h6>
            </div>
            <div class="card-body">
                <?php
                $avgPricePerLiter = $orderStats['avg_price_per_liter'] ?? 0;
                $totalVolume = $orderStats['total_volume'] ?? 0;
                ?>
                
                <?php if ($avgPricePerLiter > 50): ?>
                    <div class="alert alert-warning p-2 mb-2">
                        <small><i class="fas fa-exclamation-triangle me-1"></i> 
                        Висока ціна за літр. Запропонуйте більші об'єми.</small>
                    </div>
                <?php endif; ?>
                
                <?php if ($totalVolume < 5): ?>
                    <div class="alert alert-info p-2 mb-2">
                        <small><i class="fas fa-info-circle me-1"></i> 
                        Малий об'єм замовлення. Можна запропонувати знижку при збільшенні.</small>
                    </div>
                <?php endif; ?>
                
                <?php if ($order['total_amount'] > 1000): ?>
                    <div class="alert alert-success p-2">
                        <small><i class="fas fa-star me-1"></i> 
                        Великий чек! Розгляньте можливість бонусу.</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- История изменений -->
<?php if (has_role(['admin', 'sales_manager'])): ?>
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card order-detail-card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Історія замовлення</h5>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-success">Створено</span>
                                <span class="ms-2">Замовлення створено</span>
                            </div>
                            <small class="text-muted"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></small>
                        </div>
                    </li>
                    <?php if ($order['status'] == 'cancelled'): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-danger">Скасовано</span>
                                    <span class="ms-2">Замовлення скасовано</span>
                                </div>
                                <small class="text-muted"><?= date('d.m.Y H:i', strtotime($order['updated_at'])) ?></small>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Кнопки действий -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="d-flex justify-content-between">
            <a href="<?= base_url('orders') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Повернутись до списку
            </a>
            
            <?php if (has_role(['admin', 'sales_manager']) && $order['status'] != 'cancelled'): ?>
                <div class="btn-group">
                    <a href="<?= base_url('orders/create') ?>" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Нове замовлення
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>