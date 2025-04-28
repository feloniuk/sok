<?php
// app/views/customer/orders/view.php - Сторінка перегляду замовлення для клієнта
$title = 'Замовлення №' . $order['order_number'];

// Функції для форматування
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

// Підключення додаткових CSS
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
    
    .tracking-info {
        padding: 1rem;
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .tracking-step {
        position: relative;
        padding-left: 30px;
        margin-bottom: 15px;
    }
    
    .tracking-step:last-child {
        margin-bottom: 0;
    }
    
    .tracking-step::before {
        content: "";
        position: absolute;
        top: 0;
        left: 10px;
        height: 100%;
        width: 2px;
        background-color: #e9ecef;
    }
    
    .tracking-step:last-child::before {
        height: 10px;
    }
    
    .tracking-dot {
        position: absolute;
        left: 5px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #e9ecef;
    }
    
    .tracking-step.active .tracking-dot {
        background-color: #007bff;
    }
    
    .tracking-step.completed .tracking-dot {
        background-color: #28a745;
    }
</style>';

// Визначення поточного етапу замовлення
$statusStep = 0;
switch ($order['status']) {
    case 'pending':
        $statusStep = 0;
        break;
    case 'processing':
        $statusStep = 1;
        break;
    case 'shipped':
        $statusStep = 2;
        break;
    case 'delivered':
        $statusStep = 3;
        break;
    case 'cancelled':
        $statusStep = -1; // Спеціальний статус для скасованого замовлення
        break;
}
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Головна</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('orders') ?>">Мої замовлення</a></li>
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
            Замовлення від <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?>
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
            <?php if ($order['status'] == 'pending'): ?>
                <a href="<?= base_url('orders/cancel/' . $order['id']) ?>" class="btn btn-outline-danger confirm-delete" data-item-name="замовлення <?= $order['order_number'] ?>">
                    <i class="fas fa-times me-1"></i> Скасувати
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Статусна шкала -->
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
                    
                    foreach ($statuses as $index => $status):
                        $statusClass = '';
                        if ($index < $statusStep) {
                            $statusClass = 'status-completed';
                        } elseif ($index == $statusStep) {
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
                
                <!-- Інформація про відстеження -->
                <?php if ($order['status'] == 'shipped'): ?>
                <div class="tracking-info">
                    <h5 class="mb-3">Інформація про відправлення</h5>
                    <div class="tracking-step completed">
                        <div class="tracking-dot"></div>
                        <div class="tracking-text">
                            <strong>Замовлення відправлено</strong>
                            <div class="text-muted small"><?= date('d.m.Y H:i', strtotime($order['updated_at'])) ?></div>
                        </div>
                    </div>
                    <div class="tracking-step active">
                        <div class="tracking-dot"></div>
                        <div class="tracking-text">
                            <strong>Замовлення в дорозі</strong>
                            <div class="text-muted small">Очікувана дата доставки: <?= date('d.m.Y', strtotime('+3 days', strtotime($order['updated_at']))) ?></div>
                        </div>
                    </div>
                    <div class="tracking-step">
                        <div class="tracking-dot"></div>
                        <div class="tracking-text">
                            <strong>Доставка до клієнта</strong>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <!-- Інформація про замовлення -->
    <div class="col-md-4 mb-4">
        <div class="card order-detail-card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Інформація про замовлення</h5>
            </div>
            <div class="card-body">
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
    
    <!-- Товари в замовленні -->
    <div class="col-md-8 mb-4">
        <div class="card order-detail-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Товари</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 60px;"></th>
                                <th>Назва</th>
                                <th class="text-center">Кількість</th>
                                <th class="text-end">Ціна</th>
                                <th class="text-end">Сума</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr class="product-row">
                                    <td>
                                        <img src="<?= $item['image'] ? upload_url($item['image']) : asset_url('images/no-image.jpg') ?>" alt="<?= $item['product_name'] ?>" class="product-image">
                                    </td>
                                    <td>
                                        <a href="<?= base_url('products/view/' . $item['product_id']) ?>"><?= $item['product_name'] ?></a>
                                    </td>
                                    <td class="text-center"><?= $item['quantity'] ?></td>
                                    <td class="text-end"><?= number_format($item['price'], 2) ?> грн</td>
                                    <td class="text-end"><?= number_format($item['price'] * $item['quantity'], 2) ?> грн</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="4" class="text-end">Загальна сума:</td>
                                <td class="text-end"><?= number_format($order['total_amount'], 2) ?> грн</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Історія статусів -->
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
                    
                    <?php if ($order['status'] == 'processing'): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-info">В обробці</span>
                                    <span class="ms-2">Замовлення в обробці</span>
                                </div>
                                <small class="text-muted"><?= date('d.m.Y H:i', strtotime($order['updated_at'])) ?></small>
                            </div>
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($order['status'] == 'shipped'): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-primary">Відправлено</span>
                                    <span class="ms-2">Замовлення відправлено</span>
                                </div>
                                <small class="text-muted"><?= date('d.m.Y H:i', strtotime($order['updated_at'])) ?></small>
                            </div>
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($order['status'] == 'delivered'): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-success">Доставлено</span>
                                    <span class="ms-2">Замовлення доставлено</span>
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

<!-- Кнопки дій -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="d-flex justify-content-between">
            <a href="<?= base_url('orders') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Повернутись до списку
            </a>
            
            <div>
                <?php if ($order['status'] == 'delivered'): ?>
                    <a href="<?= base_url('orders/create') ?>" class="btn btn-success">
                        <i class="fas fa-shopping-cart me-1"></i> Зробити нове замовлення
                    </a>
                <?php endif; ?>
                
                <?php if ($order['status'] == 'pending'): ?>
                    <a href="<?= base_url('orders/cancel/' . $order['id']) ?>" class="btn btn-danger confirm-delete" data-item-name="замовлення <?= $order['order_number'] ?>">
                        <i class="fas fa-times me-1"></i> Скасувати замовлення
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Рекомендовані товари -->
<?php 
// Відображення рекомендованих товарів
$productModel = new Product();
$recommendedProducts = $productModel->getFeatured(4);

if (!empty($recommendedProducts)):
?>
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card order-detail-card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Вам також може сподобатися</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($recommendedProducts as $product): ?>
                        <div class