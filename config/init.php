<?php
// config/init.php - Инициализация приложения

// Запуск сессии
session_start([
    'cookie_lifetime' => SESSION_LIFETIME,
    'name' => SESSION_NAME,
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true
]);

// Установка обработчиков ошибок
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    
    // Настраиваем логирование ошибок в файл
    ini_set('log_errors', 1);
    ini_set('error_log', LOG_PATH . '/error.log');
}

// Защита от CSRF-атак
if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
}

// Функция для динамической загрузки классов
spl_autoload_register(function ($class_name) {
    // Преобразование имени класса в путь к файлу
    $file_path = str_replace('\\', '/', $class_name) . '.php';
    
    // Поиск файла в различных директориях
    $directories = [
        APP_PATH . '/models/',
        APP_PATH . '/controllers/',
        APP_PATH . '/middleware/',
        APP_PATH . '/helpers/'
    ];
    
    foreach ($directories as $directory) {
        if (file_exists($directory . $file_path)) {
            require_once $directory . $file_path;
            return;
        }
    }
});

// Загрузка основных помощников
require_once APP_PATH . '/helpers/auth_helper.php';
require_once APP_PATH . '/helpers/url_helper.php';
require_once APP_PATH . '/helpers/form_helper.php';

// Функция для обработки необработанных исключений
function exception_handler($exception) {
    $message = $exception->getMessage();
    $file = $exception->getFile();
    $line = $exception->getLine();
    $trace = $exception->getTraceAsString();
    
    $error_message = "Exception: $message in $file on line $line\n$trace";
    
    if (DEBUG_MODE) {
        echo "<h1>Error</h1>";
        echo "<p>$error_message</p>";
    } else {
        error_log($error_message);
        echo "<h1>Произошла ошибка</h1>";
        echo "<p>Извините, произошла внутренняя ошибка сервера. Пожалуйста, попробуйте позже.</p>";
    }
    
    exit;
}

// Установка обработчика исключений
set_exception_handler('exception_handler');