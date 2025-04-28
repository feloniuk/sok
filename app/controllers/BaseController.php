<?php
// app/controllers/BaseController.php - Базовый класс для всех контроллеров

class BaseController {
    protected $db;
    protected $data = [];
    
    public function __construct() {
        // Получение экземпляра базы данных
        $this->db = Database::getInstance();
    }
    
    // Загрузка представления
    protected function view($view, $data = []) {
        // Объединение данных
        $this->data = array_merge($this->data, $data);
        
        // Извлечение переменных из массива данных
        extract($this->data);
        
        // Путь к файлу представления
        $viewPath = APP_PATH . '/views/' . $view . '.php';
        
        // Проверка существования файла
        if (!file_exists($viewPath)) {
            throw new Exception("Представление '$view' не найдено.");
        }
        
        // Буферизация вывода
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        
        // Вывод содержимого
        $layoutPath = APP_PATH . '/views/layouts/main.php';
        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            echo $content;
        }
    }

    protected function displayView($id, $viewPath, $data = []) {
        return $this->view($viewPath, $data);
    }
    
    // Перенаправление на другой URL
    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    
    // Получение введенных данных с очисткой
    protected function input($key, $default = null) {
        $value = isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
        
        if (is_string($value)) {
            // Очистка строки от XSS
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        
        return $value;
    }
    
    // Проверка наличия данных в POST
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    // Проверка CSRF-токена
    protected function validateCsrfToken() {
        $token = $this->input(CSRF_TOKEN_NAME);
        
        if (!$token || $token !== $_SESSION[CSRF_TOKEN_NAME]) {
            http_response_code(403);
            die('CSRF token validation failed');
        }
        
        return true;
    }
    
    // Отправка JSON-ответа
    protected function json($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
    
    // Флеш-сообщения
    protected function setFlash($type, $message) {
        $_SESSION['flash'][$type] = $message;
    }
    
    protected function hasFlash($type) {
        return isset($_SESSION['flash'][$type]);
    }
    
    protected function getFlash($type) {
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }
    
    // Пагинация
    protected function paginate($sql, $params = [], $page = 1, $perPage = ITEMS_PER_PAGE) {
        $page = max(1, intval($page));
        
        // Получение общего количества записей
        $countSql = preg_replace('/SELECT(.*?)FROM/is', 'SELECT COUNT(*) FROM', $sql);
        $countSql = preg_replace('/ORDER BY(.*?)$/is', '', $countSql);
        $totalItems = $this->db->getValue($countSql, $params);
        
        // Расчет пагинации
        $totalPages = ceil($totalItems / $perPage);
        $page = min($page, max(1, $totalPages));
        $offset = ($page - 1) * $perPage;
        
        // Получение записей для текущей страницы
        $pageSql = $sql . " LIMIT $offset, $perPage";
        $items = $this->db->getAll($pageSql, $params);
        
        return [
            'items' => $items,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_items' => $totalItems,
            'total_pages' => $totalPages
        ];
    }
}