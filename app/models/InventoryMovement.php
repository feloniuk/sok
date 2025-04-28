<?php
// app/models/InventoryMovement.php - Модель для руху товарів

class InventoryMovement extends BaseModel {
    protected $table = 'inventory_movements';
    protected $fillable = [
        'product_id', 'warehouse_id', 'quantity', 'movement_type',
        'reference_id', 'reference_type', 'notes', 'created_by'
    ];
    
    /**
     * Створення нового запису про рух товару
     * 
     * @param array $data
     * @return int|bool ID нового запису або false при помилці
     */
    public function create($data) {
        // Починаємо транзакцію для забезпечення цілісності даних
        $this->db->beginTransaction();
        
        try {
            // Створюємо запис про рух товару
            $movementId = parent::create($data);
            
            if (!$movementId) {
                throw new Exception("Помилка при створенні запису про рух товару");
            }
            
            // Оновлюємо кількість товару на складі, якщо не встановлено флаг пропуску
            if (!isset($data['skip_stock_update']) || !$data['skip_stock_update']) {
                $productModel = new Product();
                $productModel->updateStock($data['product_id'], $data['quantity']);
            }
            
            // Фіксуємо транзакцію
            $this->db->commit();
            return $movementId;
            
        } catch (Exception $e) {
            // Відкат транзакції при помилці
            $this->db->rollBack();
            
            if (DEBUG_MODE) {
                throw $e;
            }
            
            return false;
        }
    }
    
    /**
     * Отримання записів про рух товарів з додатковою інформацією
     * 
     * @param array $filters Параметри фільтрації
     * @param int $page Номер сторінки
     * @param int $perPage Записів на сторінку
     * @return array
     */
    public function getWithDetails($filters = [], $page = 1, $perPage = ITEMS_PER_PAGE) {
        // Базовий SQL запит
        $sql = 'SELECT im.*, p.name as product_name, w.name as warehouse_name, 
                u.first_name, u.last_name 
                FROM inventory_movements im 
                JOIN products p ON im.product_id = p.id 
                JOIN warehouses w ON im.warehouse_id = w.id 
                JOIN users u ON im.created_by = u.id';
        
        // Умови фільтрації
        $conditions = [];
        $params = [];
        
        if (!empty($filters['product_id'])) {
            $conditions[] = 'im.product_id = ?';
            $params[] = $filters['product_id'];
        }
        
        if (!empty($filters['warehouse_id'])) {
            $conditions[] = 'im.warehouse_id = ?';
            $params[] = $filters['warehouse_id'];
        }
        
        if (!empty($filters['movement_type'])) {
            $conditions[] = 'im.movement_type = ?';
            $params[] = $filters['movement_type'];
        }
        
        if (!empty($filters['date_from'])) {
            $conditions[] = 'DATE(im.created_at) >= ?';
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $conditions[] = 'DATE(im.created_at) <= ?';
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['keyword'])) {
            $conditions[] = '(p.name LIKE ? OR im.notes LIKE ?)';
            $params[] = '%' . $filters['keyword'] . '%';
            $params[] = '%' . $filters['keyword'] . '%';
        }
        
        // Додавання умов до SQL запиту
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        
        // Сортування за датою (від нових до старих)
        $sql .= ' ORDER BY im.created_at DESC';
        
        // Пагінація
        $page = max(1, intval($page));
        
        // Отримання загальної кількості записів
        $countSql = str_replace('SELECT im.*', 'SELECT COUNT(*)', $sql);
        $countSql = preg_replace('/ORDER BY.*$/', '', $countSql);
        $totalItems = $this->db->getValue($countSql, $params);
        
        // Розрахунок пагінації
        $totalPages = ceil($totalItems / $perPage);
        $page = min($page, max(1, $totalPages));
        $offset = ($page - 1) * $perPage;
        
        // Отримання записів для поточної сторінки
        $sql .= " LIMIT $offset, $perPage";
        $items = $this->db->getAll($sql, $params);
        
        return [
            'items' => $items,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_items' => $totalItems,
            'total_pages' => $totalPages
        ];
    }
    
    /**
     * Отримання останніх рухів товарів
     * 
     * @param int $limit Кількість записів
     * @return array
     */
    public function getRecent($limit = 10) {
        $sql = 'SELECT im.*, p.name as product_name, w.name as warehouse_name,
                u.first_name, u.last_name 
                FROM inventory_movements im 
                JOIN products p ON im.product_id = p.id 
                JOIN warehouses w ON im.warehouse_id = w.id 
                JOIN users u ON im.created_by = u.id 
                ORDER BY im.created_at DESC 
                LIMIT ?';
        
        return $this->db->getAll($sql, [$limit]);
    }
    
    /**
     * Отримання статистики руху товарів за період
     * 
     * @param int $days Кількість днів
     * @return array
     */
    public function getStatsByPeriod($days = 14) {
        $sql = 'SELECT 
                DATE(created_at) as date,
                SUM(CASE WHEN movement_type = "incoming" THEN quantity ELSE 0 END) as incoming,
                SUM(CASE WHEN movement_type = "outgoing" THEN ABS(quantity) ELSE 0 END) as outgoing
                FROM inventory_movements
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date';
        
        return $this->db->getAll($sql, [$days]);
    }
}