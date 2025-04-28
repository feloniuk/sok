<?php
// app/controllers/ProfileController.php - Контроллер для управления профилем пользователя

class ProfileController extends BaseController {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }
    
    /**
     * Отображение профиля пользователя
     */
    public function index() {
        // Проверка авторизации
        if (!is_logged_in()) {
            $this->redirect('auth/login');
            return;
        }
        
        // Получение ID текущего пользователя
        $userId = get_current_user_id();
        
        // Получение данных пользователя
        $user = $this->userModel->getById($userId);
        
        if (!$user) {
            $this->setFlash('error', 'Произошла ошибка при загрузке профиля.');
            $this->redirect('dashboard');
            return;
        }
        
        // Передача данных в представление
        $this->data['user'] = $user;
        
        $this->view('profile/index');
    }
    
    /**
     * Отображение формы редактирования профиля
     */
    public function edit() {
        // Проверка авторизации
        if (!is_logged_in()) {
            $this->redirect('auth/login');
            return;
        }
        
        // Получение ID текущего пользователя
        $userId = get_current_user_id();
        
        // Получение данных пользователя
        $user = $this->userModel->getById($userId);
        
        if (!$user) {
            $this->setFlash('error', 'Произошла ошибка при загрузке профиля.');
            $this->redirect('dashboard');
            return;
        }
        
        // Передача данных в представление
        $this->data['user'] = $user;
        
        $this->view('profile/edit');
    }
    
    /**
     * Обработка формы редактирования профиля
     */
    public function update() {
        // Проверка авторизации
        if (!is_logged_in()) {
            $this->redirect('auth/login');
            return;
        }
        
        // Проверка метода запроса
        if (!$this->isPost()) {
            $this->redirect('profile/edit');
            return;
        }
        
        // Проверка CSRF-токена
        $this->validateCsrfToken();
        
        // Получение ID текущего пользователя
        $userId = get_current_user_id();
        
        // Получение данных из формы
        $firstName = $this->input('first_name');
        $lastName = $this->input('last_name');
        $email = $this->input('email');
        $phone = $this->input('phone');
        
        // Валидация данных
        $errors = [];
        
        if (empty($firstName)) {
            $errors['first_name'] = 'Введите имя';
        }
        
        if (empty($lastName)) {
            $errors['last_name'] = 'Введите фамилию';
        }
        
        if (empty($email)) {
            $errors['email'] = 'Введите email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Введите корректный email';
        } else {
            // Проверка, не занят ли email другим пользователем
            $existingUser = $this->userModel->findByEmail($email);
            if ($existingUser && $existingUser['id'] != $userId) {
                $errors['email'] = 'Этот email уже занят другим пользователем';
            }
        }
        
        // Если есть ошибки, возвращаемся к форме
        if (!empty($errors)) {
            set_form_errors($errors);
            $this->redirect('profile/edit');
            return;
        }
        
        // Обновление данных пользователя
        $userData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone
        ];
        
        $result = $this->userModel->update($userId, $userData);
        
        if ($result) {
            // Обновляем имя пользователя в сессии
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            $_SESSION['user_email'] = $email; // Если используется в сессии
            $_SESSION['user_phone'] = $phone; // Если используется в сессии
            
            $this->setFlash('success', 'Профиль успешно обновлен.');
            $this->redirect('profile');
        } else {
            $this->setFlash('error', 'Произошла ошибка при обновлении профиля.');
            $this->redirect('profile/edit');
        }
    }
    
    /**
     * Отображение формы изменения пароля
     */
    public function changePassword() {
        // Проверка авторизации
        if (!is_logged_in()) {
            $this->redirect('auth/login');
            return;
        }
        
        $this->view('profile/change_password');
    }
    
    /**
     * Обработка формы изменения пароля
     */
    public function updatePassword() {
        // Проверка авторизации
        if (!is_logged_in()) {
            $this->redirect('auth/login');
            return;
        }
        
        // Проверка метода запроса
        if (!$this->isPost()) {
            $this->redirect('profile/change_password');
            return;
        }
        
        // Проверка CSRF-токена
        $this->validateCsrfToken();
        
        // Получение ID текущего пользователя
        $userId = get_current_user_id();
        
        // Получение данных из формы
        $currentPassword = $this->input('current_password');
        $newPassword = $this->input('new_password');
        $confirmPassword = $this->input('confirm_password');
        
        // Валидация данных
        $errors = [];
        
        if (empty($currentPassword)) {
            $errors['current_password'] = 'Введите текущий пароль';
        }
        
        if (empty($newPassword)) {
            $errors['new_password'] = 'Введите новый пароль';
        } elseif (strlen($newPassword) < 6) {
            $errors['new_password'] = 'Пароль должен содержать минимум 6 символов';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'Пароли не совпадают';
        }
        
        // Получение данных пользователя для проверки текущего пароля
        $user = $this->userModel->getById($userId);
        
        if (!$user) {
            $this->setFlash('error', 'Произошла ошибка при загрузке данных пользователя.');
            $this->redirect('profile/change_password');
            return;
        }
        
        // Проверка текущего пароля
        if (!empty($currentPassword) && !verify_password($currentPassword, $user['password'])) {
            $errors['current_password'] = 'Текущий пароль введен неверно';
        }
        
        // Если есть ошибки, возвращаемся к форме
        if (!empty($errors)) {
            set_form_errors($errors);
            $this->redirect('profile/change_password');
            return;
        }
        
        // Обновление пароля
        $result = $this->userModel->update($userId, ['password' => $newPassword]);
        
        if ($result) {
            $this->setFlash('success', 'Пароль успешно изменен.');
            $this->redirect('profile');
        } else {
            $this->setFlash('error', 'Произошла ошибка при изменении пароля.');
            $this->redirect('profile/change_password');
        }
    }
}