<?php
// app/middleware/RoleMiddleware.php - Проверка роли пользователя

class RoleMiddleware {
    private $allowedRoles = [];
    
    public function __construct($roles = []) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        $this->allowedRoles = $roles;
    }
    
    public function handle() {
        // Сначала проверяем авторизацию
        $authMiddleware = new AuthMiddleware();
        $result = $authMiddleware->handle();
        
        if ($result === false) {
            return false;
        }
        
        // Проверяем роль пользователя
        if (!isset($_SESSION['user_role']) || 
            (!empty($this->allowedRoles) && !in_array($_SESSION['user_role'], $this->allowedRoles))) {
            
            // Доступ запрещен
            header('HTTP/1.1 403 Forbidden');
            echo '<h1>403 Доступ запрещен</h1>';
            echo '<p>У вас нет доступа к этой странице.</p>';
            echo '<p><a href="' . BASE_URL . '">Вернуться на главную</a></p>';
            exit;
        }
        
        return true;
    }
    
    // Статический метод для простого использования в маршрутах
    public static function allow($roles = []) {
        return new self($roles);
    }
}