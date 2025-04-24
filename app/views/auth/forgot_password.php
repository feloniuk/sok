<?php
// app/views/auth/forgot_password.php - Страница восстановления пароля
$title = 'Відновлення паролю';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-key me-2"></i> Відновлення паролю
                </h5>
            </div>
            <div class="card-body">
                <p class="text-center mb-4">Введіть вашу електронну адресу, і ми надішлемо вам інструкції для відновлення паролю.</p>
                
                <form action="<?= base_url('auth/forgot_password') ?>" method="POST">
                    <?= csrf_field() ?>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control <?= has_error('email') ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= old('email') ?>" placeholder="Введіть вашу електронну адресу" required>
                            <?php if (has_error('email')): ?>
                                <div class="invalid-feedback"><?= get_error('email') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-paper-plane me-2"></i> Надіслати інструкції
                    </button>
                </form>
            </div>
            <div class="card-footer text-center">
                <p class="mb-0">Згадали пароль? <a href="<?= base_url('auth/login') ?>" class="text-decoration-none">Повернутися до входу</a></p>
            </div>
        </div>
    </div>
</div>