<?php
// app/controllers/AuthController.php - Контроллер для авторизации и регистрации

class AuthController extends BaseController {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }
    
    /**
     * Отображение формы входа
     */
    public function login() {
        
        // Если пользователь уже авторизован, перенаправляем на дашборд
        if (is_logged_in()) {
            $this->redirect('dashboard');
            return;
        }
        
        $this->view('auth/login');
    }
    
    /**
     * Обработка формы входа
     */
    public function processLogin() {
        // Проверка метода запроса
        if (!$this->isPost()) {
            $this->redirect('auth/login');
            return;
        }
        
        // Проверка CSRF-токена
        $this->validateCsrfToken();
        
        // Получение данных из формы
        $username = $this->input('username');
        $password = $this->input('password');
        $remember = $this->input('remember') ? true : false;
        
        // Валидация данных
        $errors = [];
        
        if (empty($username)) {
            $errors['username'] = 'Введите имя пользователя или email';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Введите пароль';
        }
        
        // Если есть ошибки, возвращаемся на форму
        if (!empty($errors)) {
            set_form_errors($errors);
            $this->redirect('auth/login');
            return;
        }
        
        // Проверка учетных данных
        $user = $this->userModel->authenticate($username, $password);
        
        if ($user) {
            // Авторизация пользователя
            login_user($user['id'], $user['username'], $user['role'], $user['first_name'] . ' ' . $user['last_name']);
            
            // Установка куки для функции "запомнить меня"
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                
                // Сохранение токена в куки на 30 дней
                setcookie(
                    'remember_token', 
                    $token, 
                    time() + (30 * 24 * 60 * 60), 
                    '/', 
                    '', 
                    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', 
                    true
                );
                
                // Сохранение токена в базе данных
                // Это упрощенный пример. В реальной системе лучше использовать отдельную таблицу
                $this->userModel->update($user['id'], [
                    'remember_token' => $token
                ]);
            }
            
            // Перенаправление на страницу перенаправления или дашборд
            $redirectUrl = $_SESSION['redirect_url'] ?? 'dashboard';
            unset($_SESSION['redirect_url']);
            
            $this->redirect($redirectUrl);
        } else {
            // Ошибка авторизации
            set_form_errors(['login' => 'Неверное имя пользователя или пароль']);
            $this->redirect('auth/login');
        }
    }
    
    /**
     * Отображение формы регистрации
     */
    public function register() {
        // Если пользователь уже авторизован, перенаправляем на дашборд
        if (is_logged_in()) {
            $this->redirect('dashboard');
            return;
        }
        
        $this->view('auth/register');
    }
    
    /**
     * Обработка формы регистрации
     */
    public function processRegister() {
        // Проверка метода запроса
        if (!$this->isPost()) {
            $this->redirect('auth/register');
            return;
        }
        
        // Проверка CSRF-токена
        $this->validateCsrfToken();
        
        // Получение данных из формы
        $username = $this->input('username');
        $email = $this->input('email');
        $password = $this->input('password');
        $confirmPassword = $this->input('password_confirm');
        $firstName = $this->input('first_name');
        $lastName = $this->input('last_name');
        $phone = $this->input('phone');
        
        // Валидация данных
        $errors = [];
        
        if (empty($username)) {
            $errors['username'] = 'Введите имя пользователя';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errors['username'] = 'Имя пользователя должно содержать от 3 до 50 символов';
        } elseif ($this->userModel->usernameExists($username)) {
            $errors['username'] = 'Имя пользователя уже занято';
        }
        
        if (empty($email)) {
            $errors['email'] = 'Введите email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Введите корректный email';
        } elseif ($this->userModel->emailExists($email)) {
            $errors['email'] = 'Email уже зарегистрирован';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Введите пароль';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Пароль должен содержать не менее 6 символов';
        }
        
        if ($password !== $confirmPassword) {
            $errors['password_confirm'] = 'Пароли не совпадают';
        }
        
        if (empty($firstName)) {
            $errors['first_name'] = 'Введите имя';
        }
        
        if (empty($lastName)) {
            $errors['last_name'] = 'Введите фамилию';
        }
        
        // Если есть ошибки, возвращаемся на форму
        if (!empty($errors)) {
            set_form_errors($errors);
            $this->redirect('auth/register');
            return;
        }
        
        // Создание пользователя
        $userData = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => 'customer', // По умолчанию регистрируем как клиента
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone
        ];
        
        $userId = $this->userModel->create($userData);
        
        if ($userId) {
            // Авторизация пользователя
            login_user($userId, $username, 'customer', $firstName . ' ' . $lastName);
            
            // Установка флеш-сообщения
            $this->setFlash('success', 'Регистрация успешно завершена!');
            
            // Перенаправление на дашборд
            $this->redirect('dashboard');
        } else {
            // Ошибка при создании пользователя
            $this->setFlash('error', 'Произошла ошибка при регистрации. Пожалуйста, попробуйте снова.');
            $this->redirect('auth/register');
        }
    }
    
    /**
     * Выход пользователя
     */
    public function logout() {
        // Выход из системы
        logout_user();
        
        // Удаление куки "запомнить меня"
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Перенаправление на главную страницу
        $this->redirect('');
    }
    
    /**
     * Отображение формы восстановления пароля
     */
    public function forgotPassword() {
        $this->view('auth/forgot_password');
    }
    
    /**
     * Обработка формы восстановления пароля
     */
    public function processForgotPassword() {
        // Проверка метода запроса
        if (!$this->isPost()) {
            $this->redirect('auth/forgot_password');
            return;
        }
        
        // Проверка CSRF-токена
        $this->validateCsrfToken();
        
        // Получение данных из формы
        $email = $this->input('email');
        
        // Валидация данных
        $errors = [];
        
        if (empty($email)) {
            $errors['email'] = 'Введите email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Введите корректный email';
        }
        
        // Если есть ошибки, возвращаемся на форму
        if (!empty($errors)) {
            set_form_errors($errors);
            $this->redirect('auth/forgot_password');
            return;
        }
        
        // Поиск пользователя по email
        $user = $this->userModel->findByEmail($email);
        
        if ($user) {
            // Генерация токена сброса пароля
            $token = bin2hex(random_bytes(32));
            
            // Сохранение токена в базе данных
            // Это упрощенный пример. В реальной системе лучше использовать отдельную таблицу
            $this->userModel->update($user['id'], [
                'reset_token' => $token,
                'reset_expires' => date('Y-m-d H:i:s', strtotime('+1 hour'))
            ]);
            
            // Формирование ссылки для сброса пароля
            $resetLink = base_url("auth/reset_password?token=$token");
            
            // Отправка письма (в реальной системе)
            // mail($email, 'Сброс пароля', "Для сброса пароля перейдите по ссылке: $resetLink");
            
            // В демо-режиме просто покажем ссылку на экране
            $this->setFlash('info', "В реальной системе на email было бы отправлено письмо со ссылкой для сброса пароля. Ссылка: <a href=\"$resetLink\">$resetLink</a>");
            $this->redirect('auth/forgot_password');
        } else {
            // Пользователь не найден, но для безопасности показываем то же сообщение
            $this->setFlash('info', 'Если указанный email зарегистрирован в системе, вы получите письмо с инструкциями по сбросу пароля.');
            $this->redirect('auth/forgot_password');
        }
    }
    
    /**
     * Отображение формы сброса пароля
     */
    public function resetPassword() {
        // Получение токена из URL
        $token = $this->input('token');
        
        if (empty($token)) {
            $this->setFlash('error', 'Некорректная ссылка для сброса пароля.');
            $this->redirect('auth/login');
            return;
        }
        
        // Поиск пользователя по токену
        $user = $this->userModel->findOne('reset_token = ?', [$token]);
        
        if (!$user || strtotime($user['reset_expires']) < time()) {
            $this->setFlash('error', 'Ссылка для сброса пароля недействительна или истекла.');
            $this->redirect('auth/login');
            return;
        }
        
        // Передача токена в представление
        $this->data['token'] = $token;
        
        $this->view('auth/reset_password');
    }
    
    /**
     * Обработка формы сброса пароля
     */
    public function processResetPassword() {
        // Проверка метода запроса
        if (!$this->isPost()) {
            $this->redirect('auth/login');
            return;
        }
        
        // Проверка CSRF-токена
        $this->validateCsrfToken();
        
        // Получение данных из формы
        $token = $this->input('token');
        $password = $this->input('password');
        $confirmPassword = $this->input('password_confirm');
        
        // Валидация данных
        $errors = [];
        
        if (empty($token)) {
            $errors['token'] = 'Токен сброса пароля отсутствует';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Введите новый пароль';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Пароль должен содержать не менее 6 символов';
        }
        
        if ($password !== $confirmPassword) {
            $errors['password_confirm'] = 'Пароли не совпадают';
        }
        
        // Если есть ошибки, возвращаемся на форму
        if (!empty($errors)) {
            set_form_errors($errors);
            $this->redirect("auth/reset_password?token=$token");
            return;
        }
        
        // Поиск пользователя по токену
        $user = $this->userModel->findOne('reset_token = ?', [$token]);
        
        if (!$user || strtotime($user['reset_expires']) < time()) {
            $this->setFlash('error', 'Ссылка для сброса пароля недействительна или истекла.');
            $this->redirect('auth/login');
            return;
        }
        
        // Обновление пароля пользователя
        $result = $this->userModel->update($user['id'], [
            'password' => $password,
            'reset_token' => null,
            'reset_expires' => null
        ]);
        
        if ($result) {
            $this->setFlash('success', 'Пароль успешно изменен. Вы можете войти с новым паролем.');
            $this->redirect('auth/login');
        } else {
            $this->setFlash('error', 'Произошла ошибка при изменении пароля. Пожалуйста, попробуйте снова.');
            $this->redirect("auth/reset_password?token=$token");
        }
    }
}