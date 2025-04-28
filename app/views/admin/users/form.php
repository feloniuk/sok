<?php
// app/views/admin/users/form.php - Форма створення/редагування користувача
$title = isset($user) ? 'Редагування користувача: ' . $user['username'] : 'Створення нового користувача';
$actionUrl = isset($user) ? base_url('users/update/' . $user['id']) : base_url('users/store');

// Підключення додаткових CSS
$extra_css = '
<style>
    .user-form-card {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: none;
    }
    
    .role-badge {
        font-size: 0.8rem;
        margin-right: 5px;
    }
</style>';

// Функція для отримання людиночитаємих назв ролей
function getRoleName($role) {
    $roleNames = [
        'admin' => 'Адміністратор',
        'sales_manager' => 'Менеджер продажів',
        'warehouse_manager' => 'Менеджер складу',
        'customer' => 'Клієнт'
    ];
    return $roleNames[$role] ?? $role;
}

// Функція для отримання кольорів для бейджів ролей
function getRoleClass($role) {
    $roleClasses = [
        'admin' => 'danger',
        'sales_manager' => 'info',
        'warehouse_manager' => 'success',
        'customer' => 'primary'
    ];
    return $roleClasses[$role] ?? 'secondary';
}
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Головна</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('users') ?>">Користувачі</a></li>
                <li class="breadcrumb-item active"><?= isset($user) ? 'Редагування' : 'Створення' ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card user-form-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas <?= isset($user) ? 'fa-edit' : 'fa-user-plus' ?> me-2"></i>
                    <?= $title ?>
                </h5>
            </div>
            
            <div class="card-body">
                <form action="<?= $actionUrl ?>" method="POST" class="needs-validation" novalidate>
                    <?= csrf_field() ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <!-- Логін -->
                            <div class="mb-3">
                                <label for="username" class="form-label">Логін <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control <?= has_error('username') ? 'is-invalid' : '' ?>" id="username" name="username" value="<?= old('username', $user['username'] ?? '') ?>" <?= isset($user) ? 'readonly' : '' ?> required>
                                    <?php if (has_error('username')): ?>
                                        <div class="invalid-feedback"><?= get_error('username') ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="form-text">Мінімум 3 символи, тільки латинські літери та цифри.</div>
                            </div>
                            
                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control <?= has_error('email') ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= old('email', $user['email'] ?? '') ?>" required>
                                    <?php if (has_error('email')): ?>
                                        <div class="invalid-feedback"><?= get_error('email') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Роль -->
                            <div class="mb-3">
                                <label for="role" class="form-label">Роль <span class="text-danger">*</span></label>
                                <select class="form-select <?= has_error('role') ? 'is-invalid' : '' ?>" id="role" name="role" required>
                                    <option value="">Виберіть роль</option>
                                    <option value="admin" <?= (old('role', $user['role'] ?? '') == 'admin') ? 'selected' : '' ?>>Адміністратор</option>
                                    <option value="sales_manager" <?= (old('role', $user['role'] ?? '') == 'sales_manager') ? 'selected' : '' ?>>Менеджер продажів</option>
                                    <option value="warehouse_manager" <?= (old('role', $user['role'] ?? '') == 'warehouse_manager') ? 'selected' : '' ?>>Менеджер складу</option>
                                    <option value="customer" <?= (old('role', $user['role'] ?? '') == 'customer') ? 'selected' : '' ?>>Клієнт</option>
                                </select>
                                <?php if (has_error('role')): ?>
                                    <div class="invalid-feedback"><?= get_error('role') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <!-- Ім'я -->
                            <div class="mb-3">
                                <label for="first_name" class="form-label">Ім'я <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= has_error('first_name') ? 'is-invalid' : '' ?>" id="first_name" name="first_name" value="<?= old('first_name', $user['first_name'] ?? '') ?>" required>
                                <?php if (has_error('first_name')): ?>
                                    <div class="invalid-feedback"><?= get_error('first_name') ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Прізвище -->
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Прізвище <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= has_error('last_name') ? 'is-invalid' : '' ?>" id="last_name" name="last_name" value="<?= old('last_name', $user['last_name'] ?? '') ?>" required>
                                <?php if (has_error('last_name')): ?>
                                    <div class="invalid-feedback"><?= get_error('last_name') ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Телефон -->
                            <div class="mb-3">
                                <label for="phone" class="form-label">Телефон</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" class="form-control <?= has_error('phone') ? 'is-invalid' : '' ?>" id="phone" name="phone" value="<?= old('phone', $user['phone'] ?? '') ?>">
                                    <?php if (has_error('phone')): ?>
                                        <div class="invalid-feedback"><?= get_error('phone') ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="form-text">Формат: +380XXXXXXXXX</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Паролі -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">
                                Пароль <?= isset($user) ? '' : '<span class="text-danger">*</span>' ?>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control <?= has_error('password') ? 'is-invalid' : '' ?>" id="password" name="password" <?= isset($user) ? '' : 'required' ?>>
                                <?php if (has_error('password')): ?>
                                    <div class="invalid-feedback"><?= get_error('password') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">
                                <?= isset($user) ? 'Залиште порожнім, якщо не хочете змінювати пароль.' : 'Мінімум 6 символів.' ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="password_confirm" class="form-label">
                                Підтвердження паролю <?= isset($user) ? '' : '<span class="text-danger">*</span>' ?>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control <?= has_error('password_confirm') ? 'is-invalid' : '' ?>" id="password_confirm" name="password_confirm" <?= isset($user) ? '' : 'required' ?>>
                                <?php if (has_error('password_confirm')): ?>
                                    <div class="invalid-feedback"><?= get_error('password_confirm') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?= base_url('users') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Назад до списку
                        </a>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas <?= isset($user) ? 'fa-save' : 'fa-user-plus' ?> me-1"></i>
                            <?= isset($user) ? 'Зберегти зміни' : 'Створити користувача' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (isset($user)): ?>
<!-- Додаткова інформація для режиму редагування -->
<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card user-form-card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i> Додаткова інформація
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Дата реєстрації:</label>
                            <p class="form-control-static"><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Остання активність:</label>
                            <p class="form-control-static"><?= date('d.m.Y H:i', strtotime($user['updated_at'])) ?></p>
                        </div>
                    </div>
                </div>
                
                <?php if ($user['role'] == 'customer'): ?>
                <div class="alert alert-info">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="fas fa-info-circle fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="alert-heading">Інформація про клієнта</h5>
                            <p class="mb-0">Для перегляду історії замовлень та повної інформації про клієнта перейдіть на сторінку 
                            <a href="<?= base_url('users/view/' . $user['id']) ?>" class="alert-link">профілю користувача</a>.</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>