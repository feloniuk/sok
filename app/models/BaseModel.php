<?php
// app/models/BaseModel.php - Базовый класс для всех моделей

class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Получение всех записей
    public function getAll() {
        return $this->db->getAll("SELECT * FROM {$this->table}");
    }
    
    // Получение записи по ID
    public function getById($id) {
        return $this->db->getOne("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?", [$id]);
    }
    
    // Создание новой записи
    public function create($data) {
        // Фильтрация данных
        $data = $this->filterData($data);
        
        if (empty($data)) {
            return false;
        }
        
        // Формирование SQL-запроса
        $fields = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)";
        
        // Выполнение запроса
        $this->db->query($sql, array_values($data));
        
        // Возвращение ID новой записи
        return $this->db->getLastId();
    }
    
    // Обновление записи
    public function update($id, $data) {
        // Фильтрация данных
        $data = $this->filterData($data);
        
        if (empty($data)) {
            return false;
        }
        
        // Формирование SQL-запроса
        $setParts = [];
        foreach (array_keys($data) as $field) {
            $setParts[] = "$field = ?";
        }
        
        $setClause = implode(', ', $setParts);
        $sql = "UPDATE {$this->table} SET $setClause WHERE {$this->primaryKey} = ?";
        
        // Добавление ID в параметры
        $params = array_values($data);
        $params[] = $id;
        
        // Выполнение запроса
        $this->db->query($sql, $params);
        
        return true;
    }
    
    // Удаление записи
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $this->db->query($sql, [$id]);
        
        return true;
    }
    
    // Проверка существования записи
    public function exists($id) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->getValue($sql, [$id]) > 0;
    }
    
    // Поиск записей
    public function search($keyword, $fields) {
        $whereParts = [];
        $params = [];
        
        foreach ($fields as $field) {
            $whereParts[] = "$field LIKE ?";
            $params[] = "%$keyword%";
        }
        
        $whereClause = implode(' OR ', $whereParts);
        $sql = "SELECT * FROM {$this->table} WHERE $whereClause";
        
        return $this->db->getAll($sql, $params);
    }
    
    // Подсчет количества записей
    public function count() {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        return $this->db->getValue($sql);
    }
    
    // Фильтрация данных
    protected function filterData($data) {
        $filtered = [];
        
        foreach ($this->fillable as $field) {
            if (array_key_exists($field, $data)) {
                $filtered[$field] = $data[$field];
            }
        }
        
        return $filtered;
    }
    
    // Создание запроса на выборку с условиями
    public function where($conditions, $params = []) {
        $sql = "SELECT * FROM {$this->table} WHERE $conditions";
        return $this->db->getAll($sql, $params);
    }
    
    // Получение одной записи по условию
    public function findOne($conditions, $params = []) {
        $sql = "SELECT * FROM {$this->table} WHERE $conditions LIMIT 1";
        return $this->db->getOne($sql, $params);
    }
    
    // Получение пагинированных записей
    public function paginate($page = 1, $perPage = ITEMS_PER_PAGE) {
        $page = max(1, intval($page));
        
        // Получение общего количества записей
        $totalItems = $this->count();
        
        // Расчет пагинации
        $totalPages = ceil($totalItems / $perPage);
        $page = min($page, max(1, $totalPages));
        $offset = ($page - 1) * $perPage;
        
        // Получение записей для текущей страницы
        $sql = "SELECT * FROM {$this->table} LIMIT $offset, $perPage";
        $items = $this->db->getAll($sql);
        
        return [
            'items' => $items,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_items' => $totalItems,
            'total_pages' => $totalPages
        ];
    }
}