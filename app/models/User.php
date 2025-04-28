<?php
// app/models/User.php - Модель для работы с пользователями

class User extends BaseModel {
    protected $table = 'users';
    protected $fillable = [
        'username', 'email', 'password', 'role', 
        'first_name', 'last_name', 'phone'
    ];
    
    /**
     * Поиск пользователя по имени пользователя
     *
     * @param string $username
     * @return array|null
     */
    public function findByUsername($username) {
        return $this->findOne('username = ?', [$username]);
    }
    
    /**
     * Поиск пользователя по email
     *
     * @param string $email
     * @return array|null
     */
    public function findByEmail($email) {
        return $this->findOne('email = ?', [$email]);
    }
    
    /**
     * Проверка существования пользователя с указанным именем
     *
     * @param string $username
     * @param int $exceptId
     * @return bool
     */
    public function usernameExists($username, $exceptId = null) {
        $sql = 'SELECT COUNT(*) FROM users WHERE username = ?';
        $params = [$username];
        
        if ($exceptId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $exceptId;
        }
        
        return $this->db->getValue($sql, $params) > 0;
    }
    
    /**
     * Проверка существования пользователя с указанным email
     *
     * @param string $email
     * @param int $exceptId
     * @return bool
     */
    public function emailExists($email, $exceptId = null) {
        $sql = 'SELECT COUNT(*) FROM users WHERE email = ?';
        $params = [$email];
        
        if ($exceptId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $exceptId;
        }
        
        return $this->db->getValue($sql, $params) > 0;
    }
    
    /**
     * Создание нового пользователя с хешированием пароля
     *
     * @param array $data
     * @return int|bool
     */
    public function create($data) {
        // Хеширование пароля
        if (isset($data['password'])) {
            $data['password'] = hash_password($data['password']);
        }
        
        return parent::create($data);
    }
    
    /**
     * Обновление пользователя с хешированием пароля (если есть)
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        // Хеширование пароля, если он предоставлен
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = hash_password($data['password']);
        } else {
            // Если пароль пустой, удаляем его из данных
            unset($data['password']);
        }
        
        return parent::update($id, $data);
    }
    
    /**
     * Проверка учетных данных пользователя
     *
     * @param string $username
     * @param string $password
     * @return array|null
     */
    public function authenticate($username, $password) {
        // Поиск пользователя по имени
        $user = $this->findByUsername($username);
        
        // Если пользователь не найден, пробуем искать по email
        if (!$user) {
            $user = $this->findByEmail($username);
        }
        
        // Проверка пароля
        if ($user && verify_password($password, $user['password'])) {
            return $user;
        }
        
        return null;
    }
    
    /**
     * Получение пользователей с определенной ролью
     *
     * @param string $role
     * @return array
     */
    public function getUsersByRole($role) {
        return $this->where('role = ?', [$role]);
    }
    
    /**
     * Поиск пользователей
     *
     * @param string $keyword
     * @param array $fields
     * @return array
     */
    public function search($keyword, $fields = null) {
        if ($fields === null) {
            $fields = ['username', 'email', 'first_name', 'last_name', 'phone'];
        }
        return parent::search($keyword, $fields);
    }
    /**
     * Получение полного имени пользователя
     *
     * @param array $user
     * @return string
     */
    public function getFullName($user) {
        return $user['first_name'] . ' ' . $user['last_name'];
    }
    
    /**
     * Получение списка всех клиентов
     *
     * @return array
     */
    public function getAllCustomers() {
        return $this->where('role = ?', ['customer']);
    }
    
    /**
     * Получение данных для информационной панели
     *
     * @param string $role
     * @return array
     */
    public function getDashboardData($role) {
        $data = [];
        
        switch ($role) {
            case 'admin':
                // Количество пользователей по ролям
                $sql = 'SELECT role, COUNT(*) as count FROM users GROUP BY role';
                $data['usersByRole'] = $this->db->getAll($sql);
                
                // Общее количество пользователей
                $data['totalUsers'] = $this->count();
                break;
                
            case 'sales_manager':
                // Количество клиентов
                $sql = 'SELECT COUNT(*) FROM users WHERE role = ?';
                $data['customersCount'] = $this->db->getValue($sql, ['customer']);
                break;
                
            case 'warehouse_manager':
                // Менеджеры складов
                $sql = 'SELECT COUNT(*) FROM users WHERE role = ?';
                $data['warehouseManagersCount'] = $this->db->getValue($sql, ['warehouse_manager']);
                break;
        }
        
        return $data;
    }
}