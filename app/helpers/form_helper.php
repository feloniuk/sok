<?php
// app/helpers/form_helper.php - Вспомогательные функции для работы с формами

/**
 * Получение значения поля из POST/GET или значения по умолчанию
 *
 * @param string $name
 * @param mixed $default
 * @return mixed
 */
function old($name, $default = '') {
    if (isset($_REQUEST[$name])) {
        $value = $_REQUEST[$name];
        
        // Очистка от XSS для строк
        if (is_string($value)) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        
        return $value;
    }
    
    return $default;
}

/**
 * Проверка, была ли ошибка для указанного поля
 *
 * @param string $name
 * @return bool
 */
function has_error($name) {
    return isset($_SESSION['form_errors'][$name]);
}

/**
 * Получение сообщения об ошибке для поля
 *
 * @param string $name
 * @return string
 */
function get_error($name) {
    if (has_error($name)) {
        $error = $_SESSION['form_errors'][$name];
        unset($_SESSION['form_errors'][$name]);
        return $error;
    }
    
    return '';
}

/**
 * Установка ошибок формы
 *
 * @param array $errors
 * @return void
 */
function set_form_errors($errors) {
    $_SESSION['form_errors'] = $errors;
}

/**
 * Получение класса для поля с ошибкой
 *
 * @param string $name
 * @param string $defaultClass
 * @return string
 */
function form_class($name, $defaultClass = 'form-control') {
    return has_error($name) ? "$defaultClass is-invalid" : $defaultClass;
}

/**
 * Генерация HTML-кода для сообщения об ошибке
 *
 * @param string $name
 * @return string
 */
function error_message($name) {
    if (has_error($name)) {
        return '<div class="invalid-feedback">' . get_error($name) . '</div>';
    }
    
    return '';
}

/**
 * Проверка, был ли отправлен POST-запрос
 *
 * @return bool
 */
function is_post() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Валидация формы
 *
 * @param array $rules
 * @return bool
 */
function validate($rules) {
    $errors = [];
    
    foreach ($rules as $field => $fieldRules) {
        // Получение значения поля
        $value = $_REQUEST[$field] ?? null;
        
        // Разделение правил
        $ruleArray = explode('|', $fieldRules);
        
        foreach ($ruleArray as $rule) {
            // Проверка на параметры правила (например, min:3)
            $ruleParams = [];
            if (strpos($rule, ':') !== false) {
                list($rule, $paramsStr) = explode(':', $rule, 2);
                $ruleParams = explode(',', $paramsStr);
            }
            
            // Применение правила
            switch ($rule) {
                case 'required':
                    if (empty($value) && $value !== '0') {
                        $errors[$field] = 'Поле обязательно для заполнения';
                    }
                    break;
                    
                case 'email':
                    if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field] = 'Некорректный email адрес';
                    }
                    break;
                    
                case 'numeric':
                    if (!empty($value) && !is_numeric($value)) {
                        $errors[$field] = 'Поле должно содержать только числа';
                    }
                    break;
                    
                case 'min':
                    $min = $ruleParams[0] ?? 0;
                    if (!empty($value) && strlen($value) < $min) {
                        $errors[$field] = "Минимальная длина поля: $min символов";
                    }
                    break;
                    
                case 'max':
                    $max = $ruleParams[0] ?? 255;
                    if (!empty($value) && strlen($value) > $max) {
                        $errors[$field] = "Максимальная длина поля: $max символов";
                    }
                    break;
                    
                case 'matches':
                    $matchField = $ruleParams[0] ?? '';
                    $matchValue = $_REQUEST[$matchField] ?? null;
                    
                    if (!empty($value) && $value !== $matchValue) {
                        $errors[$field] = "Поле не совпадает с $matchField";
                    }
                    break;
            }
            
            // Если уже есть ошибка для этого поля, прекращаем проверку
            if (isset($errors[$field])) {
                break;
            }
        }
    }
    
    // Сохранение ошибок в сессии
    if (!empty($errors)) {
        set_form_errors($errors);
        return false;
    }
    
    return true;
}

/**
 * Загрузка файла
 *
 * @param string $fieldName
 * @param string $directory
 * @param array $allowedTypes
 * @param int $maxSize
 * @return string|null
 */
function upload_file($fieldName, $directory = '', $allowedTypes = ALLOWED_FILE_TYPES, $maxSize = MAX_FILE_SIZE) {
    // Проверка наличия файла
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $file = $_FILES[$fieldName];
    
    // Проверка размера файла
    if ($file['size'] > $maxSize) {
        set_form_errors([$fieldName => 'Размер файла превышает допустимый']);
        return null;
    }
    
    // Проверка типа файла
    if (!in_array($file['type'], $allowedTypes)) {
        set_form_errors([$fieldName => 'Недопустимый тип файла']);
        return null;
    }
    
    // Создание директории, если не существует
    $uploadDir = UPLOAD_PATH . ($directory ? "/$directory" : '');
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Генерация уникального имени файла
    $filename = uniqid() . '_' . $file['name'];
    $destination = $uploadDir . '/' . $filename;
    
    // Перемещение файла
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        set_form_errors([$fieldName => 'Ошибка при загрузке файла']);
        return null;
    }
    
    return $directory ? "$directory/$filename" : $filename;
}