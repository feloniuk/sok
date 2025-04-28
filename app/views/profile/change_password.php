<?php
// app/views/profile/change_password.php - Сторінка зміни паролю користувача
$title = 'Зміна паролю';

// Підключення додаткових CSS
$extra_css = '
<style>
    .password-form-card {
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
    
    .password-strength {
        height: 5px;
        margin-top: 10px;
        border-radius: 5px;
        transition: all 0.3s ease;
    }
</style>';

// Підключення додаткових JS
$extra_js = '
<script>
    $(document).ready(function() {
        // Перевірка сили пароля
        $("#new_password").on("keyup", function() {
            const password = $(this).val();
            let strength = 0;
            let progress = 0;
            
            if (password.length >= 6) {
                strength += 1;
            }
            
            if (password.match(/[A-Z]/)) {
                strength += 1;
            }
            
            if (password.match(/[0-9]/)) {
                strength += 1;
            }
            
            if (password.match(/[^A-Za-z0-9]/)) {
                strength += 1;
            }
            
            switch(strength) {
                case 0:
                    progress = 0;
                    $(".password-strength").css("background-color", "#e74a3b").css("width", progress + "%");
                    $(".password-strength-text").text("").removeClass("text-success text-warning text-danger");
                    break;
                case 1:
                    progress = 25;
                    $(".password-strength").css("background-color", "#e74a3b").css("width", progress + "%");
                    $(".password-strength-text").text("Слабкий").addClass("text-danger").removeClass("text-success text-warning");
                    break;
                case 2:
                    progress = 50;
                    $(".password-strength").css("background-color", "#f6c23e").css("width", progress + "%");
                    $(".password-strength-text").text("Середній").addClass("text-warning").removeClass("text-success text-danger");
                    break;
                case 3:
                    progress = 75;
                    $(".password-strength").css("background-color", "#36b9cc").css("width", progress + "%");
                    $(".password-strength-text").text("Добрий").addClass("text-info").removeClass("text-success text-danger text-warning");
                    break;
                case 4:
                    progress = 100;
                    $(".password-strength").css("background-color", "#1cc88a").css("width", progress + "%");
                    $(".password-strength-text").text("Сильний").addClass("text-success").removeClass("text-danger text-warning");
                    break;
            }
        });
        
        // Перевірка співпадіння паролів
        $("#confirm_password").on("keyup", function() {
            if ($(this).val() === $("#new_password").val()) {
                $("#password-match").text("Паролі співпадають").addClass("text-success").removeClass("text-danger");
            } else {
                $("#password-match").text("Паролі не співпадають").addClass("text-danger").removeClass("text-success");
            }
        });
    });
</script>';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card password-form-card">
            <div class="form-header">
                <h3 class="mb-0">
                    <i class="fas fa-key me-2"></i> Зміна паролю
                </h3>
            </div>
            
            <div class="form-body">
                <form action="<?= base_url('profile/update_password') ?>" method="POST" class="needs-validation" novalidate>
                    <?= csrf_field() ?>
                    
                    <div class="mb-4">
                        <label for="current_password" class="form-label">Поточний пароль <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control <?= has_error('current_password') ? 'is-invalid' : '' ?>" id="current_password" name="current_password" required>
                            <?php if (has_error('current_password')): ?>
                                <div class="invalid-feedback"><?= get_error('current_password') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="new_password" class="form-label">Новий пароль <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password" class="form-control <?= has_error('new_password') ? 'is-invalid' : '' ?>" id="new_password" name="new_password" required>
                            <?php if (has_error('new_password')): ?>
                                <div class="invalid-feedback"><?= get_error('new_password') ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="password-strength mt-2"></div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Мінімум 6 символів</small>
                            <small class="password-strength-text"></small>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Підтвердження нового паролю <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password" class="form-control <?= has_error('confirm_password') ? 'is-invalid' : '' ?>" id="confirm_password" name="confirm_password" required>
                            <?php if (has_error('confirm_password')): ?>
                                <div class="invalid-feedback"><?= get_error('confirm_password') ?></div>
                            <?php endif; ?>
                        </div>
                        <small id="password-match" class="mt-1"></small>
                    </div>
                    
                    <div class="alert alert-info mb-4">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-info-circle fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="alert-heading">Рекомендації з безпеки</h5>
                                <p class="mb-0">Для створення надійного паролю використовуйте комбінацію букв у верхньому та нижньому регістрах, цифр та спеціальних символів. Не використовуйте однаковий пароль для різних сервісів.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('profile') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Назад до профілю
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Зберегти новий пароль
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>