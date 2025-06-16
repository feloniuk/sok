<?php
// Визначення змінних, якщо вони не були встановлені
$title = $title ?? APP_NAME;
$extra_css = $extra_css ?? '';
$extra_js = $extra_js ?? '';
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> | <?= APP_NAME ?></title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= asset_url('images/favicon.ico') ?>" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Основні стилі -->
    <link href="<?= asset_url('css/style.css') ?>" rel="stylesheet">
    
    <!-- Додаткові стилі -->
    <?= $extra_css ?>
    <meta name="csrf-token" content="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">
</head>
<body>
    <!-- Верхнє меню -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url() ?>">
                <img src="<?= asset_url('images/logo.png') ?>" alt="<?= APP_NAME ?>" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= is_current_url('') || is_current_url('home') ? 'active' : '' ?>" href="<?= base_url() ?>">
                            <i class="fas fa-home"></i> Головна
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= is_current_url('products') ? 'active' : '' ?>" href="<?= base_url('products') ?>">
                            <i class="fas fa-box"></i> Продукція
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= is_current_url('categories') ? 'active' : '' ?>" href="<?= base_url('categories') ?>">
                            <i class="fas fa-list"></i> Категорії
                        </a>
                    </li>
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= is_current_url('dashboard') ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>">
                                <i class="fas fa-tachometer-alt"></i> Панель керування
                            </a>
                        </li>
                        <?php if (has_role('warehouse_manager')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('warehouse/inventory') ?>">
                                <i class="fas fa-boxes"></i>
                                <span>Інвентаризація</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('warehouse/movements') ?>">
                                <i class="fas fa-exchange-alt"></i>
                                <span>Рух товарів</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (has_role(['admin', 'sales_manager'])): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= is_current_url('orders') ? 'active' : '' ?>" href="<?= base_url('orders') ?>">
                                    <i class="fas fa-shopping-cart"></i> Замовлення
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (has_role(['admin'])): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= is_current_url('warehouse') ? 'active' : '' ?>" href="<?= base_url('warehouse') ?>">
                                    <i class="fas fa-warehouse"></i> Склад
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= is_current_url('scada') ? 'active' : '' ?>" href="<?= base_url('scada') ?>">
                                    <i class="fas fa-warehouse"></i> SCADA
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (has_role(['admin'])): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= is_current_url('users') ? 'active' : '' ?>" href="<?= base_url('users') ?>">
                                    <i class="fas fa-users"></i> Користувачі
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= is_current_url('camera') ? 'active' : '' ?>" href="<?= base_url('camera') ?>">
                                    <i class="fas fa-users"></i> Відеонагляд
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (has_role(['admin', 'sales_manager'])): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= is_current_url('reports') ? 'active' : '' ?>" href="<?= base_url('reports') ?>">
                                    <i class="fas fa-chart-bar"></i> Звіти
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (is_logged_in()): ?>
                        <?php if (has_role('customer')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= is_current_url('orders') ? 'active' : '' ?>" href="<?= base_url('orders') ?>">
                                    <i class="fas fa-shopping-cart"></i> Мої замовлення
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle"></i> <?= get_current_user_name() ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('profile') ?>">
                                        <i class="fas fa-user me-2"></i> Мій профіль
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('profile/change_password') ?>">
                                        <i class="fas fa-key me-2"></i> Змінити пароль
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('auth/logout') ?>">
                                        <i class="fas fa-sign-out-alt me-2"></i> Вийти
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?= is_current_url('auth/login') ? 'active' : '' ?>" href="<?= base_url('auth/login') ?>">
                                <i class="fas fa-sign-in-alt"></i> Вхід
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_current_url('auth/register') ? 'active' : '' ?>" href="<?= base_url('auth/register') ?>">
                                <i class="fas fa-user-plus"></i> Реєстрація
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Флеш-повідомлення -->
    <div class="container mt-4">
        <?php if (isset($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                <?php 
                $alertClass = 'alert-info';
                $icon = 'fa-info-circle';
                
                if ($type == 'success') {
                    $alertClass = 'alert-success';
                    $icon = 'fa-check-circle';
                } elseif ($type == 'error') {
                    $alertClass = 'alert-danger';
                    $icon = 'fa-exclamation-circle';
                } elseif ($type == 'warning') {
                    $alertClass = 'alert-warning';
                    $icon = 'fa-exclamation-triangle';
                }
                ?>
                <div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
                    <i class="fas <?= $icon ?> me-2"></i> <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['flash'][$type]); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Основний контент -->
    <div class="container my-4">
        <?php if (isset($title) && !is_current_url('')): ?>
            <h1 class="mb-4"><?= $title ?></h1>
        <?php endif; ?>
        
        <?php echo $content ?? ''; ?>
    </div>
    
    <!-- Футер -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Про нас</h5>
                    <p>Наша компанія спеціалізується на виробництві та продажу високоякісних натуральних соків. Ми працюємо з 2010 року і за цей час здобули довіру тисяч клієнтів.</p>
                </div>
                <div class="col-md-4">
                    <h5>Контакти</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt me-2"></i> вул. Соняшникова, 10, Київ</li>
                        <li><i class="fas fa-phone me-2"></i> +380 (44) 123-45-67</li>
                        <li><i class="fas fa-envelope me-2"></i> info@juicesales.com</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Слідкуйте за нами</h5>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-3">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?= date('Y') ?> <?= APP_NAME ?>. Всі права захищені.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-white text-decoration-none me-3">Умови використання</a>
                    <a href="#" class="text-white text-decoration-none">Політика конфіденційності</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Основні скрипти -->
    <script src="<?= asset_url('js/main.js') ?>"></script>
    
    <!-- Додаткові скрипти -->
    <?= $extra_js ?>
</body>
</html>