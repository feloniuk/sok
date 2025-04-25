<?php
// public/index.php - Точка входа в приложение

// Загрузка конфигурации
require_once 'config/config.php';
require_once 'config/init.php';
require_once 'app/Router.php';
require_once 'config/database.php';

// Создание экземпляра маршрутизатора
$router = new Router();

// Определение маршрутов

// Главная страница
$router->get('/', 'HomeController', 'index');
$router->get('/home', 'HomeController', 'index');

// Авторизация
$router->get('/auth/login', 'AuthController', 'login');
$router->post('/auth/login', 'AuthController', 'processLogin');
$router->get('/auth/register', 'AuthController', 'register');
$router->post('/auth/register', 'AuthController', 'processRegister');
$router->get('/auth/logout', 'AuthController', 'logout');
$router->get('/auth/forgot_password', 'AuthController', 'forgotPassword');
$router->post('/auth/forgot_password', 'AuthController', 'processForgotPassword');
$router->get('/auth/reset_password', 'AuthController', 'resetPassword');
$router->post('/auth/reset_password', 'AuthController', 'processResetPassword');

// Панель управления
$router->get('/dashboard', 'DashboardController', 'index', [new AuthMiddleware()]);
$router->get('/dashboard/chart_data', 'DashboardController', 'getChartData', [new AuthMiddleware()]);

// Продукты
$router->get('/products', 'ProductController', 'index');
$router->get('/products/view/{id}', 'ProductController', 'view');
$router->get('/products/create', 'ProductController', 'create', [RoleMiddleware::allow(['admin', 'warehouse_manager'])]);
$router->post('/products/store', 'ProductController', 'store', [RoleMiddleware::allow(['admin', 'warehouse_manager'])]);
$router->get('/products/edit/{id}', 'ProductController', 'edit', [RoleMiddleware::allow(['admin', 'warehouse_manager'])]);
$router->post('/products/update/{id}', 'ProductController', 'update', [RoleMiddleware::allow(['admin', 'warehouse_manager'])]);
$router->get('/products/delete/{id}', 'ProductController', 'delete', [RoleMiddleware::allow(['admin'])]);
$router->get('/products/search', 'ProductController', 'search');

// Категории
$router->get('/categories', 'CategoryController', 'index');
$router->get('/categories/view/{id}', 'CategoryController', 'view');
$router->get('/categories/create', 'CategoryController', 'create', [RoleMiddleware::allow(['admin'])]);
$router->post('/categories/store', 'CategoryController', 'store', [RoleMiddleware::allow(['admin'])]);
$router->get('/categories/edit/{id}', 'CategoryController', 'edit', [RoleMiddleware::allow(['admin'])]);
$router->post('/categories/update/{id}', 'CategoryController', 'update', [RoleMiddleware::allow(['admin'])]);
$router->get('/categories/delete/{id}', 'CategoryController', 'delete', [RoleMiddleware::allow(['admin'])]);

// Заказы
$router->get('/orders', 'OrderController', 'index', [new AuthMiddleware()]);
$router->get('/orders/view/{id}', 'OrderController', 'view', [new AuthMiddleware()]);
$router->get('/orders/create', 'OrderController', 'create', [RoleMiddleware::allow(['admin', 'sales_manager', 'customer'])]);
$router->post('/orders/store', 'OrderController', 'store', [RoleMiddleware::allow(['admin', 'sales_manager', 'customer'])]);
$router->post('/orders/update_status/{id}', 'OrderController', 'updateStatus', [RoleMiddleware::allow(['admin', 'sales_manager', 'warehouse_manager'])]);
$router->get('/orders/cancel/{id}', 'OrderController', 'cancel', [new AuthMiddleware()]);
$router->get('/orders/print/{id}', 'OrderController', 'print', [new AuthMiddleware()]);
$router->get('/orders/products_json', 'OrderController', 'getProductsJson', [new AuthMiddleware()]);

// Склад
$router->get('/warehouse', 'WarehouseController', 'index', [RoleMiddleware::allow(['admin', 'warehouse_manager'])]);
$router->get('/warehouse/inventory', 'WarehouseController', 'inventory', [RoleMiddleware::allow(['admin', 'warehouse_manager'])]);
$router->get('/warehouse/movements', 'WarehouseController', 'movements', [RoleMiddleware::allow(['admin', 'warehouse_manager'])]);
$router->get('/warehouse/add_movement', 'WarehouseController', 'addMovement', [RoleMiddleware::allow(['admin', 'warehouse_manager'])]);
$router->post('/warehouse/store_movement', 'WarehouseController', 'storeMovement', [RoleMiddleware::allow(['admin', 'warehouse_manager'])]);

// Пользователи
$router->get('/users', 'UserController', 'index', [RoleMiddleware::allow(['admin'])]);
$router->get('/users/view/{id}', 'UserController', 'view', [RoleMiddleware::allow(['admin'])]);
$router->get('/users/create', 'UserController', 'create', [RoleMiddleware::allow(['admin'])]);
$router->post('/users/store', 'UserController', 'store', [RoleMiddleware::allow(['admin'])]);
$router->get('/users/edit/{id}', 'UserController', 'edit', [RoleMiddleware::allow(['admin'])]);
$router->post('/users/update/{id}', 'UserController', 'update', [RoleMiddleware::allow(['admin'])]);
$router->get('/users/delete/{id}', 'UserController', 'delete', [RoleMiddleware::allow(['admin'])]);

// Профиль
$router->get('/profile', 'ProfileController', 'index', [new AuthMiddleware()]);
$router->get('/profile/edit', 'ProfileController', 'edit', [new AuthMiddleware()]);
$router->post('/profile/update', 'ProfileController', 'update', [new AuthMiddleware()]);
$router->get('/profile/change_password', 'ProfileController', 'changePassword', [new AuthMiddleware()]);
$router->post('/profile/update_password', 'ProfileController', 'updatePassword', [new AuthMiddleware()]);

// Аналитика и отчеты
$router->get('/reports', 'ReportController', 'index', [RoleMiddleware::allow(['admin', 'sales_manager'])]);
$router->get('/reports/sales', 'ReportController', 'sales', [RoleMiddleware::allow(['admin', 'sales_manager'])]);
$router->get('/reports/products', 'ReportController', 'products', [RoleMiddleware::allow(['admin', 'sales_manager'])]);
$router->get('/reports/customers', 'ReportController', 'customers', [RoleMiddleware::allow(['admin', 'sales_manager'])]);
$router->get('/reports/generate', 'ReportController', 'generate', [RoleMiddleware::allow(['admin', 'sales_manager'])]);

// Обработка 404 ошибки
$router->notFound(function() {
    header("HTTP/1.0 404 Not Found");
    echo '<h1>404 Страница не найдена</h1>';
    echo '<p>Запрашиваемая страница не существует.</p>';
    echo '<p><a href="/">Вернуться на главную</a></p>';
});

// Запуск маршрутизатора
$router->dispatch();