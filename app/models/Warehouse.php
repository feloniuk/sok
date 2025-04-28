<?php
// app/models/Warehouse.php - Модель для роботи зі складами

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
        $todayOutgoingSql = 'SELECT COALESCE(SUM(quantity), 0) 
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
        $inventoryByWarehouseSql = 'SELECT w.id, w.name as warehouse_name, COALESCE(SUM(i.quantity), 0) as quantity 
                                   FROM warehouses w 
                                   LEFT JOIN inventory i ON w.id = i.warehouse_id 
                                   GROUP BY w.id, w.name 
                                   ORDER BY quantity DESC';
        
        $inventoryByWarehouse = $this->db->getAll($inventoryByWarehouseSql);
        
        // Рух товарів за останні 14 днів
        $movementsByDaySql = 'SELECT DATE(created_at) as day, 
                              SUM(CASE WHEN movement_type = "incoming" THEN quantity ELSE 0 END) as incoming,
                              SUM(CASE WHEN movement_type = "outgoing" THEN quantity ELSE 0 END) as outgoing
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
}