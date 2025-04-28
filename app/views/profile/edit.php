<?php
// app/views/profile/edit.php - Сторінка редагування профілю користувача
$title = 'Редагування профілю';

// Підключення додаткових CSS
$extra_css = '
<style>
    .profile-form-card {
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .form-header {
        padding: 1.5rem;
        background-color: #007bff;
        color: white;
    }
    
    .form-body {
        padding: 2rem;
    }
</style>';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card profile-form-card">
            <div class="form-header">
                <h3 class="mb-0">
                    <i class="fas fa-user-edit me-2"></i> Редагування профілю
                </h3>
            </div>
            
            <div class="form-body">
                <form action="<?= base_url('profile/update') ?>" method="POST" class="needs-validation" novalidate>
                    <?= csrf_field() ?>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">Ім'я <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= has_error('first_name') ? 'is-invalid' : '' ?>" id="first_name" name="first_name" value="<?= old('first_name', $user['first_name'] ?? '') ?>" required>
                            <?php if (has_error('first_name')): ?>
                                <div class="invalid-feedback"><?= get_error('first_name') ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Прізвище <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= has_error('last_name') ? 'is-invalid' : '' ?>" id="last_name" name="last_name" value="<?= old('last_name', $user['last_name'] ?? '') ?>" required>
                            <?php if (has_error('last_name')): ?>
                                <div class="invalid-feedback"><?= get_error('last_name') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control <?= has_error('email') ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= old('email', $user['email'] ?? '') ?>" required>
                            <?php if (has_error('email')): ?>
                                <div class="invalid-feedback"><?= get_error('email') ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-text">На цю адресу будуть надсилатися повідомлення про статус замовлень.</div>
                    </div>
                    
                    <div class="mb-4">
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
                    
                    <div class="alert alert-info mb-4">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-info-circle fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="alert-heading">Примітка</h5>
                                <p class="mb-0">Для зміни паролю, перейдіть на сторінку <a href="<?= base_url('profile/change_password') ?>" class="alert-link">зміни паролю</a>.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('profile') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Назад до профілю
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Зберегти зміни
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>