<?php
// app/views/profile/index.php - Сторінка перегляду профілю користувача
$title = 'Мій профіль';

// Підключення додаткових CSS
$extra_css = '
<style>
    .profile-card {
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .profile-header {
        padding: 2rem;
        background-color: #007bff;
        color: white;
        text-align: center;
    }
    
    .profile-img {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        margin-bottom: 1rem;
    }
    
    .profile-info {
        padding: 2rem;
    }
    
    .info-label {
        font-weight: bold;
        color: #6c757d;
    }
    
    .info-value {
        margin-bottom: 1.5rem;
    }
</style>';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card profile-card">
            <div class="profile-header">
                <img src="<?= asset_url('images/user-profile.png') ?>" alt="Profile Image" class="profile-img">
                <h2><?= $user['first_name'] . ' ' . $user['last_name'] ?></h2>
                <p class="mb-0"><?= ucfirst($user['role']) ?></p>
            </div>
            
            <div class="profile-info">
                <div class="row">
                    <div class="col-md-6">
                        <p class="info-label">Логін:</p>
                        <p class="info-value"><?= $user['username'] ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="info-label">Email:</p>
                        <p class="info-value"><?= $user['email'] ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="info-label">Номер телефону:</p>
                        <p class="info-value"><?= !empty($user['phone']) ? $user['phone'] : 'Не вказано' ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="info-label">Дата реєстрації:</p>
                        <p class="info-value"><?= date('d.m.Y', strtotime($user['created_at'])) ?></p>
                    </div>
                </div>
                
                <div class="d-flex justify-content-center mt-4">
                    <a href="<?= base_url('profile/edit') ?>" class="btn btn-primary me-2">
                        <i class="fas fa-user-edit me-1"></i> Редагувати профіль
                    </a>
                    <a href="<?= base_url('profile/change_password') ?>" class="btn btn-secondary">
                        <i class="fas fa-key me-1"></i> Змінити пароль
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (has_role('customer')): ?>
<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card profile-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-shopping-cart me-2"></i> Останні замовлення
                </h5>
            </div>
            <div class="card-body">
                <?php
                // Припускаємо, що в контролері ми отримали останні замовлення користувача
                $orderModel = new Order();
                $customerOrders = $orderModel->getCustomerOrders(get_current_user_id());
                
                // Функції для відображення статусу
                function getOrderStatusName($status) {
                    $statusNames = [
                        'pending' => 'Очікує обробки',
                        'processing' => 'В обробці',
                        'shipped' => 'Відправлено',
                        'delivered' => 'Доставлено',
                        'cancelled' => 'Скасовано'
                    ];
                    return $statusNames[$status] ?? $status;
                }
                
                function getOrderStatusClass($status) {
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
                
                <?php if (empty($customerOrders)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> У вас ще немає замовлень.
                    </div>
                    <div class="text-center">
                        <a href="<?= base_url('products') ?>" class="btn btn-primary">
                            <i class="fas fa-shopping-basket me-1"></i> Перейти до каталогу
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
                                <?php foreach (array_slice($customerOrders, 0, 5) as $order): ?>
                                    <tr>
                                        <td><?= $order['order_number'] ?></td>
                                        <td><?= date('d.m.Y', strtotime($order['created_at'])) ?></td>
                                        <td><?= number_format($order['total_amount'], 2) ?> грн.</td>
                                        <td>
                                            <span class="badge bg-<?= getOrderStatusClass($order['status']) ?>">
                                                <?= getOrderStatusName($order['status']) ?>
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
                            <i class="fas fa-list me-1"></i> Переглянути всі замовлення
                        </a>
                        <a href="<?= base_url('orders/create') ?>" class="btn btn-success ms-2">
                            <i class="fas fa-cart-plus me-1"></i> Створити нове замовлення
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>