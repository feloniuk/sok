<?php
// app/Router.php - Класс для маршрутизации запросов

class Router {
    private $routes = [];
    private $notFoundCallback;
    
    // Добавление маршрута с корректной обработкой слешей
    public function add($method, $path, $controller, $action, $middleware = []) {
        // Нормализация пути - удаление начального слеша, если он есть
        $path = ltrim($path, '/');
        
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action,
            'middleware' => $middleware
        ];
        
        return $this;
    }
    
    // Метод GET с нормализацией пути
    public function get($path, $controller, $action, $middleware = []) {
        return $this->add('GET', $path, $controller, $action, $middleware);
    }
    
    // Метод POST с нормализацией пути
    public function post($path, $controller, $action, $middleware = []) {
        return $this->add('POST', $path, $controller, $action, $middleware);
    }
    
    // Обработчик для маршрутов, которые не найдены
    public function notFound($callback) {
        $this->notFoundCallback = $callback;
        return $this;
    }
    
    // Выполнение запроса
    public function dispatch() {
        // Получение метода и пути запроса
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];
        
        // Базовый путь приложения
        $basePath = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
        
        // Удаление базового пути из URI
        $requestPath = str_replace($basePath, '', $requestUri);
        
        // Удаление начального слеша и параметров запроса
        $requestPath = ltrim($requestPath, '/');
        $position = strpos($requestPath, '?');
        if ($position !== false) {
            $requestPath = substr($requestPath, 0, $position);
        }
        
        // Декодирование пути
        $requestPath = urldecode($requestPath);
        
        // Если путь пустой, устанавливаем корневой
        $requestPath = $requestPath ?: '';
        
        // Поиск подходящего маршрута
        foreach ($this->routes as $route) {
            // Проверка метода
            if ($route['method'] !== $requestMethod) {
                continue;
            }
            
            // Преобразование пути маршрута в регулярное выражение
            $pattern = $this->convertRouteToRegex($route['path']);
            
            // Проверка соответствия пути
            if (preg_match($pattern, $requestPath, $matches)) {
                // Получение параметров из URL
                $params = $this->extractParams($matches);
                
                // Выполнение промежуточного ПО
                foreach ($route['middleware'] as $middleware) {
                    $middlewareInstance = is_string($middleware) ? new $middleware() : $middleware;
                    $result = $middlewareInstance->handle();
                    
                    if ($result === false) {
                        return;
                    }
                }
                
                // Создание экземпляра контроллера
                $controllerName = $route['controller'];
                $controller = new $controllerName();
                
                // Вызов действия контроллера с параметрами
                $action = $route['action'];
                call_user_func_array([$controller, $action], $params);
                
                return;
            }
        }
        
        // Маршрут не найден
        if ($this->notFoundCallback) {
            call_user_func($this->notFoundCallback);
        } else {
            header("HTTP/1.0 404 Not Found");
            echo "<h1>404 Not Found</h1>";
            echo "<p>Запрашиваемая страница не существует.</p>";
        }
    }
    
    // Преобразование пути маршрута в регулярное выражение
    private function convertRouteToRegex($route) {
        // Замена параметров в формате {id} на регулярное выражение
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route);
        
        // Экранирование символов и добавление якорей
        $pattern = '#^' . str_replace('/', '\/', $pattern) . '$#';
        
        return $pattern;
    }
    
    // Извлечение параметров из совпадений регулярного выражения
    private function extractParams($matches) {
        $params = [];
        
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }
        
        return $params;
    }
}