<?php
// app/middleware/AuthMiddleware.php - Проверка авторизации пользователя

class AuthMiddleware {
    public function handle() {
        // Проверка наличия пользователя в сессии
        if (!isset($_SESSION['user_id'])) {
            // Сохранение текущего URL для перенаправления после авторизации
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            
            // Перенаправление на страницу входа
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
        
        return true;
    }
}