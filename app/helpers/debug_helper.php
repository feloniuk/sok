<?php
// app/helpers/debug_helper.php - Вспомогательные функции для отладки

/**
 * Функция для красивого вывода отладочной информации
 *
 * @param mixed $data Данные для вывода
 * @param bool $die Остановить выполнение скрипта после вывода
 * @return void
 */
function debug($data, $die = false) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    
    if ($die) {
        die();
    }
}

/**
 * Функция для записи отладочной информации в лог
 *
 * @param mixed $data Данные для записи
 * @param string $logFile Имя файла лога
 * @return void
 */
function log_debug($data, $logFile = 'debug.log') {
    $logPath = LOG_PATH . '/' . $logFile;
    
    // Создаем директорию логов, если она не существует
    if (!is_dir(LOG_PATH)) {
        mkdir(LOG_PATH, 0755, true);
    }
    
    // Форматируем данные
    $logData = '[' . date('Y-m-d H:i:s') . '] ';
    
    if (is_array($data) || is_object($data)) {
        $logData .= print_r($data, true);
    } else {
        $logData .= $data;
    }
    
    // Добавляем информацию о вызывающем файле и строке
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
    $logData .= ' in ' . ($backtrace[0]['file'] ?? '') . ' on line ' . ($backtrace[0]['line'] ?? '');
    
    // Записываем в лог
    file_put_contents($logPath, $logData . PHP_EOL, FILE_APPEND);
}

/**
 * Функция для измерения времени выполнения
 *
 * @param string $name Уникальное имя таймера
 * @param bool $start Запустить или остановить таймер
 * @return float|void Возвращает время выполнения при остановке таймера
 */
function timer($name, $start = true) {
    static $timers = [];
    
    if ($start) {
        $timers[$name] = microtime(true);
        return;
    }
    
    if (isset($timers[$name])) {
        $time = microtime(true) - $timers[$name];
        unset($timers[$name]);
        return $time;
    }
    
    return 0;
}

/**
 * Функция для получения использования памяти
 *
 * @param bool $peak Получить пиковое использование памяти
 * @return string Отформатированный размер использованной памяти
 */
function memory_usage($peak = false) {
    if ($peak) {
        $memory = memory_get_peak_usage(true);
    } else {
        $memory = memory_get_usage(true);
    }
    
    $unit = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    
    return @round($memory / pow(1024, ($i = floor(log($memory, 1024)))), 2) . ' ' . $unit[$i];
}

/**
 * Функция для отслеживания SQL-запросов
 *
 * @param string $sql SQL-запрос
 * @param array $params Параметры запроса
 * @return void
 */
function log_query($sql, $params = []) {
    if (!DEBUG_MODE) {
        return;
    }
    
    static $queries = [];
    
    $queries[] = [
        'sql' => $sql,
        'params' => $params,
        'time' => microtime(true)
    ];
    
    // Если это первый запрос, регистрируем функцию для вывода всех запросов при завершении скрипта
    if (count($queries) === 1) {
        register_shutdown_function(function() use (&$queries) {
            if (!empty($queries)) {
                $output = "SQL Queries:\n\n";
                
                foreach ($queries as $index => $query) {
                    $time = isset($queries[$index + 1]) ? $queries[$index + 1]['time'] - $query['time'] : microtime(true) - $query['time'];
                    $output .= ($index + 1) . ". [" . round($time * 1000, 2) . " ms] " . $query['sql'] . "\n";
                    
                    if (!empty($query['params'])) {
                        $output .= "   Params: " . print_r($query['params'], true) . "\n";
                    }
                    
                    $output .= "\n";
                }
                
                log_debug($output, 'sql.log');
            }
        });
    }
}