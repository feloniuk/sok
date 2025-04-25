<?php
// app/views/customer/dashboard.php - Панель керування клієнта
$title = 'Особистий кабінет';

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

// Підключення додаткових CSS
$extra_css = '
<style>
    .stat-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    .stat-icon {
        font-size: 2.5rem;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    
    .product-card {
        transition: all 0.3s ease;
        overflow: hidden;
        height: 100%;
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .product-card .card-img-top {
        height: 180px;
        object-fit: cover;
    }
    
    .product-price {
        font-weight: bold;
        color: #007bff;
    }
</style>';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4 shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0 font-weight-bold text-primary">Вітаємо, <?= get_current_user_name() ?>!</h5>
                <a href="<?= base_url('profile') ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-user-edit me-1"></i> Редагувати профіль
                </a>
            </div>
            <div class="card-body">
                <div class="row dashboard-stats">
                    <div class="col-md-4 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2 stat-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Замовлення
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= count($customerOrders ?? []) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="stat-icon bg-primary text-white">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card border-left-success shadow h-100 py-2 stat-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Активні замовлення
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php
                                            $activeOrders = 0;
                                            foreach ($customerOrders as $order) {
                                                if (in_array($order['status'], ['pending', 'processing', 'shipped'])) {
                                                    $activeOrders++;
                                                }
                                            }
                                            echo $activeOrders;
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="stat-icon bg-success text-white">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card border-left-info shadow h-100 py-2 stat-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Виконані замовлення
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php
                                            $completedOrders = 0;
                                            foreach ($customerOrders as $order) {
                                                if ($order['status'] == 'delivered') {
                                                    $completedOrders++;
                                                }
                                            }
                                            echo $completedOrders;
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="stat-icon bg-info text-white">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Інформація про останні замовлення -->
                <h5 class="mb-3">Ваші останні замовлення</h5>
                
                <?php if (empty($customerOrders)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> У вас ще немає замовлень. 
                        <a href="<?= base_url('products') ?>" class="alert-link">Перейти до каталогу продукції</a>.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>№ замовлення</th>
                                    <th>Дата</th>
                                    <th>Сума</th>
                                    <th>Статус</th>
                                    <th>Дії</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($customerOrders, 0, 5) as $order): ?>
                                    <tr>
                                        <td><?= $order['order_number'] ?></td>
                                        <td><?= date('d.m.Y', strtotime($order['created_at'])) ?></td>
                                        <td><?= number_format($order['total_amount'], 2) ?> грн.</td>
                                        <td>
                                            <span class="badge bg-<?= getStatusClass($order['status']) ?>">
                                                <?= getStatusName($order['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('orders/view/' . $order['id']) ?>" class="btn btn-outline-primary" title="Переглянути деталі">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($order['status'] == 'pending'): ?>
                                                    <a href="<?= base_url('orders/cancel/' . $order['id']) ?>" class="btn btn-outline-danger confirm-delete" data-item-name="замовлення <?= $order['order_number'] ?>" title="Скасувати">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?= base_url('orders/print/' . $order['id']) ?>" class="btn btn-outline-secondary" title="Друкувати">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="<?= base_url('orders') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i> Переглянути всі замовлення
                        </a>
                    </div>
                <?php endif; ?>
                
                <!-- Кнопки дій -->
                <div class="mt-4">
                    <h5 class="mb-3">Швидкі дії</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="<?= base_url('products') ?>" class="btn btn-primary w-100">
                                <i class="fas fa-shopping-basket me-2"></i> Переглянути каталог
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="<?= base_url('orders/create') ?>" class="btn btn-success w-100">
                                <i class="fas fa-cart-plus me-2"></i> Створити нове замовлення
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Інформація про користувача -->
        <div class="card mb-4 shadow">
            <div class="card-header">
                <h5 class="m-0 font-weight-bold text-primary">Ваш профіль</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <img src="<?= asset_url('images/user-profile.png') ?>" alt="Profile Image" class="img-profile rounded-circle" style="width: 100px;">
                    <h5 class="mt-3"><?= get_current_user_name() ?></h5>
                    <p class="text-muted">Клієнт</p>
                </div>
                
                <hr>
                
                <div class="row mb-2">
                    <div class="col-md-4 text-muted">Логін:</div>
                    <div class="col-md-8"><?= $_SESSION['user_username'] ?? '' ?></div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 text-muted">Email:</div>
                    <div class="col-md-8"><?= $_SESSION['user_email'] ?? '' ?></div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 text-muted">Телефон:</div>
                    <div class="col-md-8"><?= $_SESSION['user_phone'] ?? 'Не вказано' ?></div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-md-4 text-muted">Дата реєстрації:</div>
                    <div class="col-md-8"><?= $_SESSION['user_created_at'] ? date('d.m.Y', strtotime($_SESSION['user_created_at'])) : 'Не вказано' ?></div>
                </div>
                
                <div class="d-grid gap-2 mt-3">
                    <a href="<?= base_url('profile/edit') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-user-edit me-1"></i> Редагувати профіль
                    </a>
                    <a href="<?= base_url('profile/change_password') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-key me-1"></i> Змінити пароль
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Рекомендовані продукти -->
        <div class="card shadow">
            <div class="card-header">
                <h5 class="m-0 font-weight-bold text-primary">Рекомендовані товари</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recommendedProducts)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> У нас поки немає рекомендацій.
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($recommendedProducts as $product): ?>
                            <div class="col-12">
                                <div class="card product-card h-100">
                                    <div class="row g-0">
                                        <div class="col-4">
                                            <img src="<?= $product['image'] ? upload_url($product['image']) : asset_url('images/no-image.jpg') ?>" class="img-fluid rounded-start" alt="<?= $product['name'] ?>" style="height: 100%; object-fit: cover;">
                                        </div>
                                        <div class="col-8">
                                            <div class="card-body d-flex flex-column h-100">
                                                <h6 class="card-title"><?= $product['name'] ?></h6>
                                                <p class="card-text text-muted small mb-2"><?= mb_substr($product['description'], 0, 60) ?>...</p>
                                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                                    <span class="product-price"><?= number_format($product['price'], 2) ?> грн.</span>
                                                    <a href="<?= base_url('products/view/' . $product['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i> Деталі
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="<?= base_url('products') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-shopping-basket me-1"></i> Перейти до каталогу
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>