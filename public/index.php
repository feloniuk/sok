<?php
// public/index.php - Точка входа в приложение

// Для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Логирование запроса
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Script Name: " . $_SERVER['SCRIPT_NAME']);


// Для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Загрузка конфигурации
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/init.php';

// Явные подключения классов
require_once APP_PATH . '/Router.php';
require_once APP_PATH . '/controllers/BaseController.php';
require_once APP_PATH . '/middleware/AuthMiddleware.php';
require_once APP_PATH . '/middleware/RoleMiddleware.php';

// Проверяем, является ли запрос запросом к статическому файлу в директории assets
$request_uri = $_SERVER['REQUEST_URI'];
if (strpos($request_uri, '/public/assets/') !== false || strpos($request_uri, '/assets/') !== false) {
    // Это запрос к статическому файлу, определяем путь к файлу
    $file_path = str_replace(['public/public', '/'], ['public', '\\'], __DIR__ . parse_url($request_uri, PHP_URL_PATH));
    // print_r($file_path);
    // exit();
    // Если файл существует, отдаем его напрямую
    if (file_exists($file_path) && is_file($file_path)) {
        // Определяем MIME-тип файла
        $mime_types = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
        ];
        
        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $mime_type = $mime_types[$ext] ?? 'application/octet-stream';
        
        header("Content-Type: $mime_type");
        readfile($file_path);
        exit;
    }
    
    // Файл не найден, возвращаем 404
    header("HTTP/1.0 404 Not Found");
    echo "404 Not Found: The requested file does not exist.";
    exit;
}

// Создание экземпляра маршрутизатора
$router = new Router();

// Определение маршрутов

// Главная страница
$router->get('', 'HomeController', 'index');
$router->get('home', 'HomeController', 'index');

// Авторизация
$router->get('auth/login', 'AuthController', 'login');
$router->post('auth/login', 'AuthController', 'processLogin');
$router->get('auth/register', 'AuthController', 'register');
$router->post('auth/register', 'AuthController', 'processRegister');
$router->get('auth/logout', 'AuthController', 'logout');
$router->get('auth/forgot_password', 'AuthController', 'forgotPassword');
$router->post('auth/forgot_password', 'AuthController', 'processForgotPassword');
$router->get('auth/reset_password', 'AuthController', 'resetPassword');
$router->post('auth/reset_password', 'AuthController', 'processResetPassword');

// Панель управления
$router->get('dashboard', 'DashboardController', 'index', [new AuthMiddleware()]);
$router->get('dashboard/chart_data', 'DashboardController', 'getChartData', [new AuthMiddleware()]);

// Продукты

$router->get('products', 'ProductController', 'index');
$router->get('products/get_product_json', 'ProductController', 'getProductJson');
$router->get('products/view/{id}', 'ProductController', 'details');
$router->get('products/create', 'ProductController', 'create', [RoleMiddleware::allow(['admin', 'warehouse_manager'])]);
$router->post('products/store', 'ProductController', 'store', [RoleMiddleware::allow(['admin', 'warehouse_manager'])]);
$router->get('products/edit/{id}', 'ProductController', 'edit', [RoleMiddleware::allow(['admin', 'warehouse_manager'])]);
$router->post('products/update/{id}', 'ProductController', 'update', [RoleMiddleware::allow(['admin', 'warehouse_manager'])]);
$router->get('products/delete/{id}', 'ProductController', 'delete', [RoleMiddleware::allow(['admin'])]);
$router->get('products/search', 'ProductController', 'search');

// Категории
$router->get('categories', 'CategoryController', 'index');
$router->get('categories/view/{id}', 'CategoryController', 'details');
$router->get('categories/create', 'CategoryController', 'create', [RoleMiddleware::allow(['admin'])]);
$router->post('categories/store', 'CategoryController', 'store', [RoleMiddleware::allow(['admin'])]);
$router->get('categories/edit/{id}', 'CategoryController', 'edit', [RoleMiddleware::allow(['admin'])]);
$router->post('categories/update/{id}', 'CategoryController', 'update', [RoleMiddleware::allow(['admin'])]);
$router->get('categories/delete/{id}', 'CategoryController', 'delete', [RoleMiddleware::allow(['admin'])]);

// Заказы
// In your router setup (typically in public/index.php or similar)

$router->get('orders', 'OrderController', 'index', [new RoleMiddleware(['admin', 'sales_manager', 'warehouse_manager'])]);
$router->get('orders/view/{id}', 'OrderController', 'details', [new RoleMiddleware(['admin', 'sales_manager', 'warehouse_manager', 'customer'])]);
$router->get('orders/process/{id}', 'OrderController', 'process', [new RoleMiddleware(['admin', 'warehouse_manager'])]);
$router->get('orders/ship/{id}', 'OrderController', 'ship', [new RoleMiddleware(['admin', 'warehouse_manager'])]);
$router->post('orders/complete_processing/{id}', 'OrderController', 'completeProcessing', [new RoleMiddleware(['admin', 'warehouse_manager'])]);
$router->post('orders/update_status/{id}', 'OrderController', 'updateStatus', [new RoleMiddleware(['admin', 'sales_manager', 'warehouse_manager'])]);

$router->get('orders/cart', 'OrderController', 'cartAction', [new AuthMiddleware()]);
$router->post('orders/cart', 'OrderController', 'cartAction', [new AuthMiddleware()]);
$router->get('orders/create', 'OrderController', 'create', [RoleMiddleware::allow(['admin', 'sales_manager', 'customer'])]);
$router->post('orders/store', 'OrderController', 'store', [RoleMiddleware::allow(['admin', 'sales_manager', 'customer'])]);
$router->get('orders/cancel/{id}', 'OrderController', 'cancel', [new AuthMiddleware()]);
$router->get('orders/print/{id}', 'OrderController', 'print', [new AuthMiddleware()]);
$router->get('orders/products_json', 'OrderController', 'getProductsJson', [new AuthMiddleware()]);

// Склад
$router->get('warehouse/export_movements', 'WarehouseController', 'exportMovements', [RoleMiddleware::allow(['admin', 'warehouse_manager'])]);
$router->get('warehouse', 'WarehouseController', 'index', [RoleMiddleware::allow(['admin', 'warehouse_manager'])]);
$router->get('warehouse/inventory', 'WarehouseController', 'inventory', [RoleMiddleware::allow(['admin', 'warehouse_manager'])]);
$router->get('warehouse/movements', 'WarehouseController', 'movements', [RoleMiddleware::allow(['admin', 'warehouse_manager'])]);
$router->get('warehouse/add_movement', 'WarehouseController', 'addMovement', [RoleMiddleware::allow(['admin', 'warehouse_manager'])]);
$router->post('warehouse/store_movement', 'WarehouseController', 'storeMovement', [RoleMiddleware::allow(['admin', 'warehouse_manager'])]);
$router->get('warehouse/get_product_stock', 'WarehouseController', 'getProductStock', [RoleMiddleware::allow(['admin', 'warehouse_manager'])]);

// Пользователи
$router->get('users', 'UserController', 'index', [RoleMiddleware::allow(['admin'])]);
$router->get('users/view/{id}', 'UserController', 'details', [RoleMiddleware::allow(['admin'])]);
$router->get('users/create', 'UserController', 'create', [RoleMiddleware::allow(['admin'])]);
$router->post('users/store', 'UserController', 'store', [RoleMiddleware::allow(['admin'])]);
$router->get('users/edit/{id}', 'UserController', 'edit', [RoleMiddleware::allow(['admin'])]);
$router->post('users/update/{id}', 'UserController', 'update', [RoleMiddleware::allow(['admin'])]);
$router->get('users/delete/{id}', 'UserController', 'delete', [RoleMiddleware::allow(['admin'])]);

// Профиль
$router->get('profile', 'ProfileController', 'index', [new AuthMiddleware()]);
$router->get('profile/edit', 'ProfileController', 'edit', [new AuthMiddleware()]);
$router->post('profile/update', 'ProfileController', 'update', [new AuthMiddleware()]);
$router->get('profile/change_password', 'ProfileController', 'changePassword', [new AuthMiddleware()]);
$router->post('profile/update_password', 'ProfileController', 'updatePassword', [new AuthMiddleware()]);

// Аналитика и отчеты
$router->get('reports', 'ReportController', 'index', [RoleMiddleware::allow(['admin', 'sales_manager'])]);
$router->get('reports/sales', 'ReportController', 'sales', [RoleMiddleware::allow(['admin', 'sales_manager'])]);
$router->get('reports/products', 'ReportController', 'products', [RoleMiddleware::allow(['admin', 'sales_manager'])]);
$router->get('reports/customers', 'ReportController', 'customers', [RoleMiddleware::allow(['admin', 'sales_manager'])]);
$router->get('reports/generate', 'ReportController', 'generate', [RoleMiddleware::allow(['admin', 'sales_manager'])]);

// Обработка 404 ошибки
$router->notFound(function() {
    header("HTTP/1.0 404 Not Found");
    echo '<h1>404 Страница не найдена</h1>';
    echo '<p>Запрашиваемая страница не существует!!</p>';
    echo '<p><a href="/">Вернуться на главную</a></p>';
});

// Запуск маршрутизатора
try {
    $router->dispatch();
} catch (Exception $e) {
    error_log('Router Exception: ' . $e->getMessage());
    echo "Произошла ошибка: " . $e->getMessage();
    exit;
}