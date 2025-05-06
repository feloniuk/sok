<?php
// app/models/Warehouse.php - Оновлення методів для роботи зі складом

class Warehouse extends BaseModel {
    protected $table = 'warehouses';
    protected $fillable = [
        'name', 'address', 'manager_id'
    ];
    
    /**
     * Отримання інформації про менеджера складу
     *
     * @param int $id
     * @return array|null
     */
    public function getWithManager($id) {
        $sql = 'SELECT w.*, u.first_name, u.last_name, u.email 
                FROM warehouses w 
                LEFT JOIN users u ON w.manager_id = u.id 
                WHERE w.id = ?';
        
        return $this->db->getOne($sql, [$id]);
    }
    
    /**
     * Отримання всіх складів з інформацією про менеджерів
     *
     * @return array
     */
    public function getAllWithManagers() {
        $sql = 'SELECT w.*, u.first_name, u.last_name 
                FROM warehouses w 
                LEFT JOIN users u ON w.manager_id = u.id 
                ORDER BY w.name';
        
        return $this->db->getAll($sql);
    }
    
    /**
     * Отримання статистики по складу
     *
     * @param int $warehouseId
     * @return array
     */
    public function getWarehouseStats($warehouseId = null) {
        // Загальна кількість товарів на складі
        $totalQuantitySql = 'SELECT SUM(stock_quantity) FROM products';
        $params = [];
        
        if ($warehouseId) {
            $totalQuantitySql = 'SELECT SUM(quantity) FROM inventory WHERE warehouse_id = ?';
            $params[] = $warehouseId;
        }
        
        $totalQuantity = $this->db->getValue($totalQuantitySql, $params);
        
        // Загальна вартість запасів
        $totalValueSql = 'SELECT SUM(p.stock_quantity * p.price) FROM products p';
        $params = [];
        
        if ($warehouseId) {
            $totalValueSql = 'SELECT SUM(i.quantity * p.price) 
                              FROM inventory i 
                              JOIN products p ON i.product_id = p.id 
                              WHERE i.warehouse_id = ?';
            $params[] = $warehouseId;
        }
        
        $totalValue = $this->db->getValue($totalValueSql, $params);
        
        // Надходження сьогодні
        $todayIncomingSql = 'SELECT COALESCE(SUM(quantity), 0) 
                            FROM inventory_movements 
                            WHERE movement_type = "incoming" 
                            AND DATE(created_at) = CURDATE()';
        $todayIncomingParams = [];
        
        if ($warehouseId) {
            $todayIncomingSql .= ' AND warehouse_id = ?';
            $todayIncomingParams[] = $warehouseId;
        }
        
        $todayIncoming = $this->db->getValue($todayIncomingSql, $todayIncomingParams);
        
        // Відвантаження сьогодні
        $todayOutgoingSql = 'SELECT COALESCE(SUM(ABS(quantity)), 0) 
                            FROM inventory_movements 
                            WHERE movement_type = "outgoing" 
                            AND DATE(created_at) = CURDATE()';
        $todayOutgoingParams = [];
        
        if ($warehouseId) {
            $todayOutgoingSql .= ' AND warehouse_id = ?';
            $todayOutgoingParams[] = $warehouseId;
        }
        
        $todayOutgoing = $this->db->getValue($todayOutgoingSql, $todayOutgoingParams);
        
        // Розподіл запасів за категоріями
        $inventoryByCategorySql = 'SELECT c.id, c.name as category_name, COALESCE(SUM(p.stock_quantity), 0) as quantity 
                                  FROM categories c 
                                  LEFT JOIN products p ON c.id = p.category_id 
                                  GROUP BY c.id, c.name 
                                  ORDER BY quantity DESC';
        $inventoryByCategoryParams = [];
        
        if ($warehouseId) {
            $inventoryByCategorySql = 'SELECT c.id, c.name as category_name, COALESCE(SUM(i.quantity), 0) as quantity 
                                      FROM categories c 
                                      LEFT JOIN products p ON c.id = p.category_id 
                                      LEFT JOIN inventory i ON p.id = i.product_id AND i.warehouse_id = ? 
                                      GROUP BY c.id, c.name 
                                      ORDER BY quantity DESC';
            $inventoryByCategoryParams[] = $warehouseId;
        }
        
        $inventoryByCategory = $this->db->getAll($inventoryByCategorySql, $inventoryByCategoryParams);
        
        // Розподіл запасів за складами
        $inventoryByWarehouseSql = 'SELECT w.id, w.name as warehouse_name, COALESCE(SUM(p.stock_quantity), 0) as quantity 
                                   FROM warehouses w 
                                   LEFT JOIN inventory i ON w.id = i.warehouse_id 
                                   LEFT JOIN products p ON i.product_id = p.id
                                   GROUP BY w.id, w.name 
                                   ORDER BY quantity DESC';
        
        $inventoryByWarehouse = $this->db->getAll($inventoryByWarehouseSql);
        
        // Рух товарів за останні 14 днів
        $movementsByDaySql = 'SELECT DATE(created_at) as day, 
                              SUM(CASE WHEN movement_type = "incoming" THEN quantity ELSE 0 END) as incoming,
                              SUM(CASE WHEN movement_type = "outgoing" THEN ABS(quantity) ELSE 0 END) as outgoing
                              FROM inventory_movements
                              WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)';
        $movementsByDayParams = [];
        
        if ($warehouseId) {
            $movementsByDaySql .= ' AND warehouse_id = ?';
            $movementsByDayParams[] = $warehouseId;
        }
        
        $movementsByDaySql .= ' GROUP BY DATE(created_at) ORDER BY day';
        
        $movementsByDay = $this->db->getAll($movementsByDaySql, $movementsByDayParams);
        
        // Повернення статистики
        return [
            'totalInventory' => $totalQuantity ?: 0,
            'totalValue' => $totalValue ?: 0,
            'todayIncoming' => $todayIncoming ?: 0,
            'todayOutgoing' => $todayOutgoing ?: 0,
            'inventoryByCategory' => $inventoryByCategory,
            'inventoryByWarehouse' => $inventoryByWarehouse,
            'movementsByDay' => $movementsByDay
        ];
    }
    
    /**
     * Отримання статистики для всіх складів
     *
     * @return array
     */
    public function getStats() {
        return $this->getWarehouseStats();
    }
    
    /**
     * Пошук складів
     *
     * @param string $keyword
     * @param array $fields
     * @return array
     */
    public function search($keyword, $fields = null) {
        if ($fields === null) {
            $fields = ['name', 'address'];
        }
        return parent::search($keyword, $fields);
    }
    
    /**
     * Створення нового складу
     *
     * @param array $data
     * @return int|bool
     */
    public function createWarehouse($data) {
        // Перевірка обов'язкових полів
        if (empty($data['name']) || empty($data['address'])) {
            return false;
        }
        
        // Створення складу
        return $this->create($data);
    }
    
    /**
     * Оновлення інформації про склад
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateWarehouse($id, $data) {
        // Перевірка обов'язкових полів
        if (empty($data['name']) || empty($data['address'])) {
            return false;
        }
        
        // Оновлення складу
        return $this->update($id, $data);
    }
    
    /**
     * Отримання наявних товарів на конкретному складі
     *
     * @param int $warehouseId
     * @return array
     */
    public function getInventory($warehouseId) {
        $sql = 'SELECT i.product_id, p.name as product_name, p.price, i.quantity,
                c.name as category_name, p.image
                FROM inventory i
                JOIN products p ON i.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE i.warehouse_id = ?
                ORDER BY p.name';
        
        return $this->db->getAll($sql, [$warehouseId]);
    }
    
    /**
     * Оновлення кількості товару на складі
     *
     * @param int $warehouseId
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function updateInventory($warehouseId, $productId, $quantity) {
        // Перевірка наявності запису в інвентарі
        $existingSql = 'SELECT COUNT(*) FROM inventory WHERE warehouse_id = ? AND product_id = ?';
        $exists = $this->db->getValue($existingSql, [$warehouseId, $productId]) > 0;
        
        if ($exists) {
            // Оновлення існуючого запису
            $sql = 'UPDATE inventory SET quantity = ?, updated_at = NOW() 
                    WHERE warehouse_id = ? AND product_id = ?';
            $this->db->query($sql, [$quantity, $warehouseId, $productId]);
        } else {
            // Створення нового запису
            $sql = 'INSERT INTO inventory (warehouse_id, product_id, quantity) VALUES (?, ?, ?)';
            $this->db->query($sql, [$warehouseId, $productId, $quantity]);
        }
        
        return true;
    }
    
    /**
     * Перевірка наявності достатньої кількості товару на складі
     *
     * @param int $warehouseId
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function hasEnoughStock($warehouseId, $productId, $quantity) {
        $sql = 'SELECT quantity FROM inventory WHERE warehouse_id = ? AND product_id = ?';
        $currentQuantity = $this->db->getValue($sql, [$warehouseId, $productId]) ?: 0;
        
        return $currentQuantity >= $quantity;
    }
    
    /**
     * Отримання інформації про склад за іменем
     *
     * @param string $name
     * @return array|null
     */
    public function getByName($name) {
        return $this->findOne('name = ?', [$name]);
    }
    
    /**
     * Отримання списку продуктів з низьким запасом на складі
     *
     * @param int $warehouseId
     * @param int $threshold
     * @return array
     */
    public function getLowStockProducts($warehouseId, $threshold = 10) {
        $sql = 'SELECT p.id, p.name, p.price, i.quantity, c.name as category_name, p.image
                FROM inventory i
                JOIN products p ON i.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE i.warehouse_id = ? AND i.quantity < ?
                ORDER BY i.quantity ASC';
        
        return $this->db->getAll($sql, [$warehouseId, $threshold]);
    }
    
    /**
     * Отримання списку продуктів з низьким запасом на всіх складах
     *
     * @param int $threshold
     * @return array
     */
    public function getAllLowStockProducts($threshold = 10) {
        $sql = 'SELECT p.id, p.name, p.price, p.stock_quantity, c.name as category_name, p.image
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.stock_quantity < ? AND p.is_active = 1
                ORDER BY p.stock_quantity ASC';
        
        return $this->db->getAll($sql, [$threshold]);
    }
}