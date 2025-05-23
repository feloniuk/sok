<?php
// config/config.php - Основные настройки приложения

define('APP_NAME', 'Система сбуту сокової продукції');
define('APP_VERSION', '1.0.0');

// URL-адрес и путь приложения
define('BASE_URL', 'http://sok.loc/');
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('APP_PATH', ROOT_PATH . '/app');

// Настройки сессии
define('SESSION_LIFETIME', 7200); // 2 часа
define('SESSION_NAME', 'juice_sales_session');

// Настройки безопасности
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_OPTIONS', ['cost' => 12]);

// Настройки пагинации
define('ITEMS_PER_PAGE', 12);

// Параметры загрузки файлов
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('UPLOAD_PATH', ROOT_PATH . '/public/uploads');

// Настройки отладки
define('DEBUG_MODE', true);
define('LOG_PATH', ROOT_PATH . '/logs');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);