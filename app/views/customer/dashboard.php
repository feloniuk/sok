<?php
// app/views/customer/dashboard.php - Customer Dashboard View

$title = 'Особистий кабінет';

// Additional CSS for styling
$extra_css = '
<style>
    .dashboard-card {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: none;
        margin-bottom: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .stat-card {
        text-align: center;
        padding: 1.5rem;
    }
    
    .stat-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        color: var(--primary);
    }
    
    .stat-value {
        font-size: 1.75rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: var(--gray);
    }
    
    .product-thumbnail {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
    }
</style>';
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="alert alert-info">
            <h4 class="alert-heading">Ласкаво просимо, <?= get_current_user_name() ?>!</h4>
            <p>Раді бачити вас в нашому інтернет-магазині натуральних соків. Тут ви можете переглядати історію своїх замовлень, керувати своїм профілем та здійснювати нові замовлення.</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Quick Stats -->
    <div class="col-md-3 mb-4">
        <div class="dashboard-card">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-value"><?= count($customerOrders) ?></div>
                <div class="stat-label">Усього замовлень</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="dashboard-card">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <?php
                $completedOrders = 0;
                $totalSpent = 0;
                
                foreach ($customerOrders as $order) {
                    if ($order['status'] == 'delivered') {
                        $completedOrders++;
                    }
                    $totalSpent += $order['total_amount'];
                }
                ?>
                <div class="stat-value"><?= $completedOrders ?></div>
                <div class="stat-label">Завершених замовлень</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="dashboard-card">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-value"><?= number_format($totalSpent, 2) ?> грн</div>
                <div class="stat-label">Загальна сума</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="dashboard-card">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-value">
                    <?php
                    $loyaltyLevel = "Новачок";
                    if ($totalSpent > 5000) {
                        $loyaltyLevel = "Преміум";
                    } elseif ($totalSpent > 2000) {
                        $loyaltyLevel = "Срібний";
                    } elseif ($totalSpent > 1000) {
                        $loyaltyLevel = "Бронзовий";
                    }
                    echo $loyaltyLevel;
                    ?>
                </div>
                <div class="stat-label">Рівень лояльності</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders -->
    <div class="col-md-8 mb-4">
        <div class="dashboard-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Останні замовлення</h5>
            </div>
            <div class="card-body">
                <?php if (empty($customerOrders)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> У вас ще немає замовлень.
                    </div>
                    <div class="text-center">
                        <a href="<?= base_url('products') ?>" class="btn btn-primary">
                            <i class="fas fa-shopping-basket me-2"></i> Перейти до каталогу
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
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
                                <?php 
                                // Function to get status name in Ukrainian
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
                                
                                // Function to get status class for badge
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
                                
                                // Display only last 5 orders
                                $recentOrders = array_slice($customerOrders, 0, 5);
                                
                                foreach ($recentOrders as $order):
                                ?>
                                <tr>
                                    <td><?= $order['order_number'] ?></td>
                                    <td><?= date('d.m.Y', strtotime($order['created_at'])) ?></td>
                                    <td><?= number_format($order['total_amount'], 2) ?> грн</td>
                                    <td>
                                        <span class="badge bg-<?= getStatusClass($order['status']) ?>">
                                            <?= getStatusName($order['status']) ?>
                                        </span>
                                    </td>
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
                    
                    <div class="text-center mt-3">
                        <a href="<?= base_url('orders') ?>" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i> Всі замовлення
                        </a>
                        <a href="<?= base_url('orders/create') ?>" class="btn btn-success ms-2">
                            <i class="fas fa-cart-plus me-2"></i> Нове замовлення
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recommended Products -->
    <div class="col-md-4 mb-4">
        <div class="dashboard-card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Рекомендовані товари</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recommendedProducts)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Немає рекомендованих товарів.
                    </div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($recommendedProducts as $product): ?>
                            <a href="<?= base_url('products/view/' . $product['id']) ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <img src="<?= $product['image'] ? upload_url($product['image']) : asset_url('images/no-image.jpg') ?>" alt="<?= $product['name'] ?>" class="product-thumbnail me-3">
                                        <div>
                                            <h6 class="mb-1"><?= $product['name'] ?></h6>
                                            <p class="mb-1 text-muted"><?= number_format($product['price'], 2) ?> грн</p>
                                        </div>
                                    </div>
                                    <span class="badge bg-<?= $product['stock_quantity'] > 10 ? 'success' : ($product['stock_quantity'] > 0 ? 'warning' : 'danger') ?>">
                                        <?= $product['stock_quantity'] > 0 ? 'В наявності' : 'Немає в наявності' ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="<?= base_url('products') ?>" class="btn btn-outline-success">
                            <i class="fas fa-th-list me-2"></i> Всі товари
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Quick Actions -->
    <div class="col-md-12 mb-4">
        <div class="dashboard-card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Швидкі дії</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('orders/create') ?>" class="btn btn-primary w-100">
                            <i class="fas fa-cart-plus me-2"></i> Нове замовлення
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('profile') ?>" class="btn btn-info w-100">
                            <i class="fas fa-user-edit me-2"></i> Редагувати профіль
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('profile/change_password') ?>" class="btn btn-warning w-100">
                            <i class="fas fa-key me-2"></i> Змінити пароль
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('auth/logout') ?>" class="btn btn-danger w-100">
                            <i class="fas fa-sign-out-alt me-2"></i> Вийти
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>