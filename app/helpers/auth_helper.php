<?php
// app/helpers/auth_helper.php - Вспомогательные функции для авторизации

/**
 * Функция для проверки авторизации пользователя
 *
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Получение ID текущего пользователя
 *
 * @return int|null
 */
function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Получение роли текущего пользователя
 *
 * @return string|null
 */
function get_current_user_role() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Получение имени текущего пользователя
 *
 * @return string|null
 */
function get_current_user_name() {
    return $_SESSION['user_name'] ?? null;
}

/**
 * Проверка, имеет ли пользователь указанную роль
 *
 * @param string|array $roles
 * @return bool
 */
function has_role($roles) {
    if (!is_logged_in()) {
        return false;
    }
    
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    
    return in_array($_SESSION['user_role'], $roles);
}

/**
 * Авторизация пользователя
 *
 * @param int $id
 * @param string $username
 * @param string $role
 * @param string $name
 * @return void
 */
function login_user($id, $username, $role, $name) {
    $_SESSION['user_id'] = $id;
    $_SESSION['user_username'] = $username;
    $_SESSION['user_role'] = $role;
    $_SESSION['user_name'] = $name;
    
    // Обновление CSRF-токена при входе
    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
}

/**
 * Выход пользователя
 *
 * @return void
 */
function logout_user() {
    // Удаление всех данных сессии
    session_unset();
    
    // Уничтожение сессии
    session_destroy();
    
    // Запуск новой сессии
    session_start([
        'cookie_lifetime' => SESSION_LIFETIME,
        'name' => SESSION_NAME,
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true
    ]);
    
    // Создание нового CSRF-токена
    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
}

/**
 * Хеширование пароля
 *
 * @param string $password
 * @return string
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_HASH_ALGO, PASSWORD_HASH_OPTIONS);
}

/**
 * Проверка пароля
 *
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Генерация CSRF-токена
 *
 * @return string
 */
function csrf_token() {
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Генерация поля с CSRF-токеном для формы
 *
 * @return string
 */
function csrf_field() {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . csrf_token() . '">';
}