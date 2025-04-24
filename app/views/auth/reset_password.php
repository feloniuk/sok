<?php
// app/views/auth/reset_password.php - Страница сброса пароля
$title = 'Встановлення нового пароля';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-key me-2"></i> Встановлення нового пароля
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('auth/reset_password') ?>" method="POST">
                    <?= csrf_field() ?>
                    
                    <input type="hidden" name="token" value="<?= $token ?? '' ?>">
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Новий пароль</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control <?= has_error('password') ? 'is-invalid' : '' ?>" id="password" name="password" placeholder="Введіть новий пароль" required>
                            <?php if (has_error('password')): ?>
                                <div class="invalid-feedback"><?= get_error('password') ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-text">Пароль повинен містити мінімум 6 символів.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Підтвердження пароля</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control <?= has_error('password_confirm') ? 'is-invalid' : '' ?>" id="password_confirm" name="password_confirm" placeholder="Повторіть новий пароль" required>
                            <?php if (has_error('password_confirm')): ?>
                                <div class="invalid-feedback"><?= get_error('password_confirm') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i> Зберегти новий пароль
                    </button>
                </form>
            </div>
            <div class="card-footer text-center">
                <p class="mb-0">Згадали пароль? <a href="<?= base_url('auth/login') ?>" class="text-decoration-none">Повернутися до входу</a></p>
            </div>
        </div>
    </div>
</div>