<?php
// app/views/auth/register.php - Страница регистрации
$title = 'Реєстрація';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-plus me-2"></i> Реєстрація нового користувача
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('auth/register') ?>" method="POST">
                    <?= csrf_field() ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">Ім'я</label>
                            <input type="text" class="form-control <?= has_error('first_name') ? 'is-invalid' : '' ?>" id="first_name" name="first_name" value="<?= old('first_name') ?>" placeholder="Введіть ім'я" required>
                            <?php if (has_error('first_name')): ?>
                                <div class="invalid-feedback"><?= get_error('first_name') ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Прізвище</label>
                            <input type="text" class="form-control <?= has_error('last_name') ? 'is-invalid' : '' ?>" id="last_name" name="last_name" value="<?= old('last_name') ?>" placeholder="Введіть прізвище" required>
                            <?php if (has_error('last_name')): ?>
                                <div class="invalid-feedback"><?= get_error('last_name') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Логін</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control <?= has_error('username') ? 'is-invalid' : '' ?>" id="username" name="username" value="<?= old('username') ?>" placeholder="Оберіть логін" required>
                            <?php if (has_error('username')): ?>
                                <div class="invalid-feedback"><?= get_error('username') ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-text">Логін повинен містити від 3 до 50 символів.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control <?= has_error('email') ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= old('email') ?>" placeholder="Введіть email" required>
                            <?php if (has_error('email')): ?>
                                <div class="invalid-feedback"><?= get_error('email') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Телефон</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="tel" class="form-control <?= has_error('phone') ? 'is-invalid' : '' ?>" id="phone" name="phone" value="<?= old('phone') ?>" placeholder="Введіть номер телефону">
                            <?php if (has_error('phone')): ?>
                                <div class="invalid-feedback"><?= get_error('phone') ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-text">Формат: +380XXXXXXXXX</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Пароль</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control <?= has_error('password') ? 'is-invalid' : '' ?>" id="password" name="password" placeholder="Створіть пароль" required>
                                <?php if (has_error('password')): ?>
                                    <div class="invalid-feedback"><?= get_error('password') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">Пароль повинен містити мінімум 6 символів.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirm" class="form-label">Підтвердження пароля</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control <?= has_error('password_confirm') ? 'is-invalid' : '' ?>" id="password_confirm" name="password_confirm" placeholder="Повторіть пароль" required>
                                <?php if (has_error('password_confirm')): ?>
                                    <div class="invalid-feedback"><?= get_error('password_confirm') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input <?= has_error('terms') ? 'is-invalid' : '' ?>" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            Я погоджуюсь з <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">умовами використання</a> та <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">політикою конфіденційності</a>
                        </label>
                        <?php if (has_error('terms')): ?>
                            <div class="invalid-feedback"><?= get_error('terms') ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-user-plus me-2"></i> Зареєструватися
                    </button>
                </form>
            </div>
            <div class="card-footer text-center">
                <p class="mb-0">Вже маєте акаунт? <a href="<?= base_url('auth/login') ?>" class="text-decoration-none">Увійти</a></p>
            </div>
        </div>
    </div>
</div>

<!-- Модальне вікно з умовами використання -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Умови використання</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>1. Загальні положення</h6>
                <p>Ласкаво просимо до системи автоматизації продажів сокової продукції. Ці умови використання визначають правила та обмеження використання нашого сервісу.</p>
                
                <h6>2. Реєстрація та особистий кабінет</h6>
                <p>Для використання сервісу вам необхідно зареєструватися та створити особистий кабінет. Ви несете відповідальність за збереження конфіденційності вашого логіна та пароля.</p>
                
                <h6>3. Правила використання</h6>
                <p>Використовуючи наш сервіс, ви погоджуєтеся не порушувати законодавство України та не використовувати сервіс для незаконних цілей.</p>
                
                <h6>4. Обмеження відповідальності</h6>
                <p>Ми докладаємо всіх зусиль для забезпечення точності та актуальності інформації, але не гарантуємо її повну точність або своєчасність.</p>
                
                <h6>5. Зміни умов використання</h6>
                <p>Ми залишаємо за собою право змінювати ці умови використання в будь-який час. Зміни набувають чинності з моменту їх публікації на сайті.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрити</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальне вікно з політикою конфіденційності -->
<div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="privacyModalLabel">Політика конфіденційності</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>1. Збір інформації</h6>
                <p>Ми збираємо персональні дані, які ви надаєте нам під час реєстрації та використання нашого сервісу.</p>
                
                <h6>2. Використання інформації</h6>
                <p>Ми використовуємо вашу інформацію для надання та покращення наших послуг, а також для спілкування з вами.</p>
                
                <h6>3. Захист інформації</h6>
                <p>Ми вживаємо всіх необхідних заходів для захисту ваших персональних даних від несанкціонованого доступу.</p>
                
                <h6>4. Розкриття інформації</h6>
                <p>Ми не продаємо, не обмінюємо і не передаємо ваші персональні дані третім сторонам без вашої згоди.</p>
                
                <h6>5. Cookies</h6>
                <p>Наш сайт використовує cookies для покращення користувацького досвіду.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрити</button>
            </div>
        </div>
    </div>
</div>