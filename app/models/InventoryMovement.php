<?php
// app/models/InventoryMovement.php - Модель для руху товарів

class InventoryMovement extends BaseModel {
    protected $table = 'inventory_movements';
    protected $fillable = [
        'product_id', 'warehouse_id', 'quantity', 'movement_type',
        'reference_id', 'reference_type', 'notes', 'created_by'
    ];
    
    /**
     * Створення запису про рух товару
     *
     * @param array $data
     * @return int|bool
     */
    public function create($data) {
        try {
            $this->db->beginTransaction();
            
            // Створення запису про рух
            $id = parent::create($data);
            
            if (!$id) {
                throw new Exception('Помилка при створенні запису про рух товару');
            }
            
            // Оновлення кількості товару на складі, якщо не оновлюється через інший процес
            if (!isset($data['skip_stock_update']) || !$data['skip_stock_update']) {
                $sql = 'UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?';
                $this->db->query($sql, [$data['quantity'], $data['product_id']]);
            }
            
            $this->db->commit();
            return $id;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            
            if (DEBUG_MODE) {
                throw $e;
            }
            
            return false;
        }
    }
    
    /**
     * Отримання руху товарів з додатковою інформацією
     *
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getWithDetails($filters = [], $page = 1, $perPage = ITEMS_PER_PAGE) {
        // Базовий запит
        $baseSql = 'SELECT im.*, p.name as product_name, w.name as warehouse_name, u.first_name, u.last_name 
                    FROM inventory_movements im 
                    JOIN products p ON im.product_id = p.id 
                    JOIN warehouses w ON im.warehouse_id = w.id 
                    JOIN users u ON im.created_by = u.id';
        
        // Умови
        $conditions = [];
        $params = [];
        
        // Фільтр за товаром
        if (!empty($filters['product_id'])) {
            $conditions[] = 'im.product_id = ?';
            $params[] = $filters['product_id'];
        }
        
        // Фільтр за складом
        if (!empty($filters['warehouse_id'])) {
            $conditions[] = 'im.warehouse_id = ?';
            $params[] = $filters['warehouse_id'];
        }
        
        // Фільтр за типом руху
        if (!empty($filters['movement_type'])) {
            $conditions[] = 'im.movement_type = ?';
            $params[] = $filters['movement_type'];
        }
        
        // Фільтр за користувачем
        if (!empty($filters['created_by'])) {
            $conditions[] = 'im.created_by = ?';
            $params[] = $filters['created_by'];
        }
        
        // Фільтр за датою (з)
        if (!empty($filters['date_from'])) {
            $conditions[] = 'DATE(im.created_at) >= ?';
            $params[] = $filters['date_from'];
        }
        
        // Фільтр за датою (по)
        if (!empty($filters['date_to'])) {
            $conditions[] = 'DATE(im.created_at) <= ?';
            $params[] = $filters['date_to'];
        }
        
        // Пошук за ключовим словом
        if (!empty($filters['keyword'])) {
            $conditions[] = '(p.name LIKE ? OR im.notes LIKE ?)';
            $params[] = '%' . $filters['keyword'] . '%';
            $params[] = '%' . $filters['keyword'] . '%';
        }
        
        // Складання умови WHERE
        $whereClause = '';
        if (!empty($conditions)) {
            $whereClause = ' WHERE ' . implode(' AND ', $conditions);
        }
        
        // Повний SQL запит
        $sql = $baseSql . $whereClause . ' ORDER BY im.created_at DESC';
        
        // Пагінація
        return $this->paginate($sql, $params, $page, $perPage);
    }
    
    /**
     * Отримання останніх рухів товарів
     *
     * @param int $limit
     * @return array
     */
    public function getRecent($limit = 10) {
        $sql = 'SELECT im.*, p.name as product_name, w.name as warehouse_name, u.first_name, u.last_name 
                FROM inventory_movements im 
                JOIN products p ON im.product_id = p.id 
                JOIN warehouses w ON im.warehouse_id = w.id 
                JOIN users u ON im.created_by = u.id 
                ORDER BY im.created_at DESC 
                LIMIT ?';
        
        return $this->db->getAll($sql, [$limit]);
    }
    
    /**
     * Отримання статистики руху товарів по днях
     *
     * @param int $days
     * @return array
     */
    public function getMovementsByDay($days = 14) {
        $sql = 'SELECT DATE(created_at) as day, 
                SUM(CASE WHEN movement_type = "incoming" THEN quantity ELSE 0 END) as incoming,
                SUM(CASE WHEN movement_type = "outgoing" THEN quantity ELSE 0 END) as outgoing
                FROM inventory_movements
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY day';
        
        return $this->db->getAll($sql, [$days]);
    }
}