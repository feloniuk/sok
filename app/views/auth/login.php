<?php
// app/views/auth/login.php - Страница входа
$title = 'Вхід в систему';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-sign-in-alt me-2"></i> Вхід в систему
                </h5>
            </div>
            <div class="card-body">
                <?php if (has_error('login')): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i> <?= get_error('login') ?>
                    </div>
                <?php endif; ?>
                
                <form action="<?= base_url('auth/login') ?>" method="POST">
                    <?= csrf_field() ?>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Логін або Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control <?= has_error('username') ? 'is-invalid' : '' ?>" id="username" name="username" value="<?= old('username') ?>" placeholder="Введіть логін або email" required>
                            <?php if (has_error('username')): ?>
                                <div class="invalid-feedback"><?= get_error('username') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control <?= has_error('password') ? 'is-invalid' : '' ?>" id="password" name="password" placeholder="Введіть пароль" required>
                            <?php if (has_error('password')): ?>
                                <div class="invalid-feedback"><?= get_error('password') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1" <?= old('remember') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="remember">Запам'ятати мене</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt me-2"></i> Увійти
                    </button>
                </form>
                
                <div class="mt-3 text-center">
                    <a href="<?= base_url('auth/forgot_password') ?>" class="text-decoration-none">
                        <i class="fas fa-question-circle me-1"></i> Забули пароль?
                    </a>
                </div>
            </div>
            <div class="card-footer text-center">
                <p class="mb-0">Ще не зареєстровані? <a href="<?= base_url('auth/register') ?>" class="text-decoration-none">Створити акаунт</a></p>
            </div>
        </div>
    </div>
</div>