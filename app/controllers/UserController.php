<?php
// app/controllers/UserController.php - Контроллер для управления пользователями

class UserController extends BaseController {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }
    
    /**
     * Отображение списка пользователей
     */
    public function index() {
        // Проверка прав доступа
        if (!has_role('admin')) {
            $this->setFlash('error', 'У вас нет доступа к этой странице.');
            $this->redirect('dashboard');
            return;
        }
        
        // Получение параметров пагинации
        $page = intval($this->input('page', 1));
        
        // Получение данных для фильтрации
        $filter = [
            'role' => $this->input('role'),
            'keyword' => $this->input('keyword')
        ];
        
        // Получение пользователей с пагинацией
        $users = $this->userModel->getFiltered($filter, $page);
        
        // Передача данных в представление
        $this->data['users'] = $users['items'];
        $this->data['pagination'] = [
            'current_page' => $users['current_page'],
            'per_page' => $users['per_page'],
            'total_items' => $users['total_items'],
            'total_pages' => $users['total_pages']
        ];
        $this->data['filter'] = $filter;
        
        $this->view('admin/users/index');
    }
    
    /**
     * Отображение данных пользователя
     *
     * @param int $id
     */
    public function view($id, $data = []) {
        // Проверка прав доступа
        if (!has_role('admin')) {
            $this->setFlash('error', 'У вас нет доступа к этой странице.');
            $this->redirect('dashboard');
            return;
        }
        
        // Получение данных пользователя
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            $this->setFlash('error', 'Пользователь не найден.');
            $this->redirect('users');
            return;
        }
        
        // Если пользователь - клиент, получаем его заказы
        $orders = [];
        if ($user['role'] == 'customer') {
            $orderModel = new Order();
            $orders = $orderModel->getCustomerOrders($id);
        }
        
        // Передача данных в представление
        $this->data['user'] = $user;
        $this->data['orders'] = $orders;
        
        $this->view('admin/users/view');
    }
    
    /**
     * Отображение формы создания пользователя
     */
    public function create() {
        // Проверка прав доступа
        if (!has_role('admin')) {
            $this->setFlash('error', 'У вас нет доступа к этой странице.');
            $this->redirect('dashboard');
            return;
        }
        
        $this->view('admin/users/form');
    }
    
    /**
     * Обработка формы создания пользователя
     */
    public function store() {
        // Проверка прав доступа
        if (!has_role('admin')) {
            $this->setFlash('error', 'У вас нет доступа к этой странице.');
            $this->redirect('dashboard');
            return;
        }
        
        // Проверка метода запроса
        if (!$this->isPost()) {
            $this->redirect('users/create');
            return;
        }
        
        // Проверка CSRF-токена
        $this->validateCsrfToken();
        
        // Получение данных из формы
        $username = $this->input('username');
        $email = $this->input('email');
        $password = $this->input('password');
        $confirmPassword = $this->input('password_confirm');
        $role = $this->input('role');
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
        
        if (empty($role)) {
            $errors['role'] = 'Выберите роль пользователя';
        }
        
        if (empty($firstName)) {
            $errors['first_name'] = 'Введите имя';
        }
        
        if (empty($lastName)) {
            $errors['last_name'] = 'Введите фамилию';
        }
        
        // Если есть ошибки, возвращаемся к форме
        if (!empty($errors)) {
            set_form_errors($errors);
            $this->redirect('users/create');
            return;
        }
        
        // Создание нового пользователя
        $userData = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone
        ];
        
        $userId = $this->userModel->create($userData);
        
        if ($userId) {
            $this->setFlash('success', 'Пользователь успешно создан.');
            $this->redirect('users/view/' . $userId);
        } else {
            $this->setFlash('error', 'Ошибка при создании пользователя.');
            $this->redirect('users/create');
        }
    }
    
    /**
     * Отображение формы редактирования пользователя
     *
     * @param int $id
     */
    public function edit($id) {
        // Проверка прав доступа
        if (!has_role('admin')) {
            $this->setFlash('error', 'У вас нет доступа к этой странице.');
            $this->redirect('dashboard');
            return;
        }
        
        // Получение данных пользователя
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            $this->setFlash('error', 'Пользователь не найден.');
            $this->redirect('users');
            return;
        }
        
        // Передача данных в представление
        $this->data['user'] = $user;
        
        $this->view('admin/users/form');
    }
    
    /**
     * Обработка формы редактирования пользователя
     *
     * @param int $id
     */
    public function update($id) {
        // Проверка прав доступа
        if (!has_role('admin')) {
            $this->setFlash('error', 'У вас нет доступа к этой странице.');
            $this->redirect('dashboard');
            return;
        }
        
        // Проверка метода запроса
        if (!$this->isPost()) {
            $this->redirect('users/edit/' . $id);
            return;
        }
        
        // Проверка CSRF-токена
        $this->validateCsrfToken();
        
        // Получение данных пользователя
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            $this->setFlash('error', 'Пользователь не найден.');
            $this->redirect('users');
            return;
        }
        
        // Получение данных из формы
        $username = $this->input('username');
        $email = $this->input('email');
        $password = $this->input('password');
        $confirmPassword = $this->input('password_confirm');
        $role = $this->input('role');
        $firstName = $this->input('first_name');
        $lastName = $this->input('last_name');
        $phone = $this->input('phone');
        
        // Валидация данных
        $errors = [];
        
        if (empty($username)) {
            $errors['username'] = 'Введите имя пользователя';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errors['username'] = 'Имя пользователя должно содержать от 3 до 50 символов';
        } elseif ($username !== $user['username'] && $this->userModel->usernameExists($username)) {
            $errors['username'] = 'Имя пользователя уже занято';
        }
        
        if (empty($email)) {
            $errors['email'] = 'Введите email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Введите корректный email';
        } elseif ($email !== $user['email'] && $this->userModel->emailExists($email)) {
            $errors['email'] = 'Email уже зарегистрирован';
        }
        
        // Проверка пароля только если он был введен
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $errors['password'] = 'Пароль должен содержать не менее 6 символов';
            }
            
            if ($password !== $confirmPassword) {
                $errors['password_confirm'] = 'Пароли не совпадают';
            }
        }
        
        if (empty($role)) {
            $errors['role'] = 'Выберите роль пользователя';
        }
        
        if (empty($firstName)) {
            $errors['first_name'] = 'Введите имя';
        }
        
        if (empty($lastName)) {
            $errors['last_name'] = 'Введите фамилию';
        }
        
        // Если есть ошибки, возвращаемся к форме
        if (!empty($errors)) {
            set_form_errors($errors);
            $this->redirect('users/edit/' . $id);
            return;
        }
        
        // Обновление данных пользователя
        $userData = [
            'username' => $username,
            'email' => $email,
            'role' => $role,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone
        ];
        
        // Добавляем пароль только если он был введен
        if (!empty($password)) {
            $userData['password'] = $password;
        }
        
        $result = $this->userModel->update($id, $userData);
        
        if ($result) {
            $this->setFlash('success', 'Пользователь успешно обновлен.');
            $this->redirect('users/view/' . $id);
        } else {
            $this->setFlash('error', 'Ошибка при обновлении пользователя.');
            $this->redirect('users/edit/' . $id);
        }
    }
    
    /**
     * Удаление пользователя
     *
     * @param int $id
     */
    public function delete($id) {
        // Проверка прав доступа
        if (!has_role('admin')) {
            $this->setFlash('error', 'У вас нет доступа к этой странице.');
            $this->redirect('dashboard');
            return;
        }
        
        // Проверка, не пытаемся ли удалить текущего пользователя
        if ($id == get_current_user_id()) {
            $this->setFlash('error', 'Вы не можете удалить свой собственный аккаунт.');
            $this->redirect('users');
            return;
        }
        
        // Получение данных пользователя
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            $this->setFlash('error', 'Пользователь не найден.');
            $this->redirect('users');
            return;
        }
        
        // Удаление пользователя
        $result = $this->userModel->delete($id);
        
        if ($result) {
            $this->setFlash('success', 'Пользователь успешно удален.');
        } else {
            $this->setFlash('error', 'Ошибка при удалении пользователя.');
        }
        
        $this->redirect('users');
    }
}