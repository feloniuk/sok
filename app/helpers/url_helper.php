<?php
// app/helpers/url_helper.php - Вспомогательные функции для работы с URL

/**
 * Получение базового URL приложения
 *
 * @param string $path
 * @return string
 */
function base_url($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL . ($path ? "/$path" : '');
}

/**
 * Получение URL ресурса (CSS, JS, изображения)
 *
 * @param string $path
 * @return string
 */
function asset_url($path) {
    $path = ltrim($path, '/');
    return base_url("assets/$path");
}

/**
 * Получение URL загруженного файла
 *
 * @param string $path
 * @return string
 */
function upload_url($path) {
    $path = ltrim($path, '/');
    return base_url("uploads/$path");
}

/**
 * Перенаправление на указанный URL
 *
 * @param string $url
 * @return void
 */
function redirect($url) {
    if (strpos($url, 'http') !== 0) {
        $url = base_url($url);
    }
    
    header("Location: $url");
    exit;
}

/**
 * Перенаправление назад (на предыдущую страницу)
 *
 * @return void
 */
function redirect_back() {
    $url = $_SERVER['HTTP_REFERER'] ?? base_url();
    redirect($url);
}

/**
 * Проверка, является ли текущий URL указанным путем
 *
 * @param string $path
 * @return bool
 */
function is_current_url($path) {
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $basePath = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
    
    // Удаление базового пути
    $currentPath = str_replace($basePath, '', $currentPath);
    
    // Удаление начального и конечного слеша
    $currentPath = trim($currentPath, '/');
    $path = trim($path, '/');
    
    return $currentPath === $path;
}

/**
 * Генерация ссылки пагинации
 *
 * @param int $page
 * @param array $params
 * @return string
 */
function pagination_url($page, $params = []) {
    $currentUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Получение текущих параметров запроса
    $queryParams = [];
    parse_str($_SERVER['QUERY_STRING'] ?? '', $queryParams);
    
    // Обновление параметров
    $queryParams['page'] = $page;
    
    if (!empty($params)) {
        $queryParams = array_merge($queryParams, $params);
    }
    
    // Формирование URL
    $query = http_build_query($queryParams);
    return $currentUrl . ($query ? "?$query" : '');
}

/**
 * Получение текущего URL
 *
 * @param array $params
 * @return string
 */
function current_url($params = []) {
    $currentUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Получение текущих параметров запроса
    $queryParams = [];
    parse_str($_SERVER['QUERY_STRING'] ?? '', $queryParams);
    
    // Обновление параметров
    if (!empty($params)) {
        $queryParams = array_merge($queryParams, $params);
    }
    
    // Формирование URL
    $query = http_build_query($queryParams);
    return $currentUrl . ($query ? "?$query" : '');
}