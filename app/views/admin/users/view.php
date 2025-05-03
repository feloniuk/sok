<?php
// app/views/admin/users/view.php - User details view for admin
$title = 'Інформація про користувача: ' . $user['username'];

// Функція для перетворення ролей
function getRoleName($role) {
    $roleNames = [
        'admin' => 'Адміністратор',
        'sales_manager' => 'Менеджер продажів',
        'warehouse_manager' => 'Менеджер складу',
        'customer' => 'Клієнт'
    ];
    return $roleNames[$role] ?? $role;
}

function getRoleBadgeClass($role) {
    $roleClasses = [
        'admin' => 'danger',
        'sales_manager' => 'info',
        'warehouse_manager' => 'success',
        'customer' => 'primary'
    ];
    return $roleClasses[$role] ?? 'secondary';
}

// Статуси замовлення
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
    .detail-card {
        border-radius: 0.5rem;
        overflow: hidden;
        border: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .user-image {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        margin: 0 auto 20px;
        display: block;
        border: 5px solid #f8f9fa;
    }
    
    .stats-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
</style>';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Головна</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('users') ?>">Користувачі</a></li>
                <li class="breadcrumb-item active"><?= $user['username'] ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h2 mb-0">
                <?= $title ?>
                <span class="badge bg-<?= getRoleBadgeClass($user['role']) ?> ms-2">
                    <?= getRoleName($user['role']) ?>
                </span>
            </h1>
            
            <div class="btn-group">
                <a href="<?= base_url('users/edit/' . $user['id']) ?>" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i> Редагувати
                </a>
                <?php if ($user['id'] != get_current_user_id()): ?>
                    <a href="<?= base_url('users/delete/' . $user['id']) ?>" class="btn btn-danger confirm-delete" data-item-name="користувача <?= $user['username'] ?>">
                        <i class="fas fa-trash me-1"></i> Видалити
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Інформація про користувача -->
    <div class="col-md-4">
        <div class="card detail-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Профіль користувача</h5>
            </div>
            <div class="card-body text-center">
                <img src="<?= asset_url('images/user-profile.png') ?>" alt="User avatar" class="user-image">
                <h4><?= $user['first_name'] . ' ' . $user['last_name'] ?></h4>
                <p class="text-muted"><?= getRoleName($user['role']) ?></p>
                
                <hr>
                
                <div class="text-start">
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">ID:</div>
                        <div class="col-md-8"><?= $user['id'] ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">Логін:</div>
                        <div class="col-md-8"><?= $user['username'] ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">Email:</div>
                        <div class="col-md-8"><?= $user['email'] ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">Телефон:</div>
                        <div class="col-md-8"><?= $user['phone'] ?? 'Не вказано' ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">Створено:</div>
                        <div class="col-md-8"><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">Оновлено:</div>
                        <div class="col-md-8"><?= date('d.m.Y H:i', strtotime($user['updated_at'])) ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($user['role'] == 'customer'): ?>
        <!-- Статистика клієнта -->
        <div class="card detail-card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Статистика клієнта</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card stats-card bg-light">
                            <div class="card-body text-center">
                                <h2 class="h1 mb-2"><?= count($orders) ?></h2>
                                <p class="text-muted mb-0">Всього замовлень</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card stats-card bg-light">
                            <div class="card-body text-center">
                                <h2 class="h1 mb-2">
                                    <?php
                                    $totalSpent = 0;
                                    foreach ($orders as $order) {
                                        if ($order['status'] != 'cancelled') {
                                            $totalSpent += $order['total_amount'];
                                        }
                                    }
                                    echo number_format($totalSpent, 2);
                                    ?>
                                </h2>
                                <p class="text-muted mb-0">Сума витрат (грн)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Замовлення клієнта -->
    <?php if ($user['role'] == 'customer' && !empty($orders)): ?>
    <div class="col-md-8">
        <div class="card detail-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Замовлення клієнта</h5>
            </div>
            <div class="card-body">
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
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= $order['order_number'] ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
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
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php elseif ($user['role'] == 'customer'): ?>
    <div class="col-md-8">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i> У цього клієнта ще немає замовлень.
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <a href="<?= base_url('users') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Повернутись до списку
        </a>
    </div>
</div>