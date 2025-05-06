<?php
// app/models/InventoryMovement.php - Розширені методи для руху товарів

class InventoryMovement extends BaseModel {
    protected $table = 'inventory_movements';
    protected $fillable = [
        'product_id', 'warehouse_id', 'quantity', 'movement_type',
        'reference_id', 'reference_type', 'notes', 'created_by'
    ];
    
    /**
     * Створення запису про рух товару з автоматичним оновленням кількості на складі
     *
     * @param array $data
     * @return int|bool
     */
    public function create($data) {
        try {
            // Перевіряємо, чи є вже активна транзакція
            $inTransaction = $this->db->inTransaction();
            
            // Починаємо транзакцію тільки якщо вона ще не почата
            if (!$inTransaction) {
                $this->db->beginTransaction();
            }
            
            // Створення запису про рух
            $id = parent::create($data);
            
            if (!$id) {
                throw new Exception('Помилка при створенні запису про рух товару');
            }
            
            // Оновлення кількості товару на складі, якщо не оновлюється через інший процес
            if (!isset($data['skip_stock_update']) || !$data['skip_stock_update']) {
                $sql = 'UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?';
                $this->db->query($sql, [$data['quantity'], $data['product_id']]);
                
                // Також оновлюємо таблицю inventory, якщо використовується
                $inventorySql = 'SELECT COUNT(*) FROM inventory WHERE product_id = ? AND warehouse_id = ?';
                $hasInventoryRecord = $this->db->getValue($inventorySql, [$data['product_id'], $data['warehouse_id']]) > 0;
                
                if ($hasInventoryRecord) {
                    $updateInventorySql = 'UPDATE inventory SET quantity = quantity + ? WHERE product_id = ? AND warehouse_id = ?';
                    $this->db->query($updateInventorySql, [$data['quantity'], $data['product_id'], $data['warehouse_id']]);
                } else {
                    $insertInventorySql = 'INSERT INTO inventory (product_id, warehouse_id, quantity) VALUES (?, ?, ?)';
                    $this->db->query($insertInventorySql, [$data['product_id'], $data['warehouse_id'], $data['quantity']]);
                }
            }
            
            // Завершуємо транзакцію тільки якщо ми її почали
            if (!$inTransaction) {
                $this->db->commit();
            }
            
            return $id;
            
        } catch (Exception $e) {
            // Відкатуємо транзакцію тільки якщо ми її почали
            if (!$inTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            
            if (DEBUG_MODE) {
                error_log("Error in InventoryMovement::create: " . $e->getMessage());
                error_log($e->getTraceAsString());
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
        
        if (!empty($filters['reference_type'])) {
            $conditions[] = 'im.reference_type = ?';
            $params[] = $filters['reference_type'];
        }
        
        if (!empty($filters['reference_id'])) {
            $conditions[] = 'im.reference_id = ?';
            $params[] = $filters['reference_id'];
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
    
    /**
     * Перевірка наявності руху товару
     *
     * @param int $productId
     * @param string $movementType
     * @param string $dateFrom
     * @param string $dateTo
     * @return bool
     */
    public function hasMovements($productId, $movementType = null, $dateFrom = null, $dateTo = null) {
        $sql = 'SELECT COUNT(*) FROM inventory_movements WHERE product_id = ?';
        $params = [$productId];
        
        if ($movementType) {
            $sql .= ' AND movement_type = ?';
            $params[] = $movementType;
        }
        
        if ($dateFrom) {
            $sql .= ' AND DATE(created_at) >= ?';
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= ' AND DATE(created_at) <= ?';
            $params[] = $dateTo;
        }
        
        return $this->db->getValue($sql, $params) > 0;
    }
    
    /**
     * Отримання сумарної кількості для товару за період
     *
     * @param int $productId
     * @param string $movementType
     * @param string $dateFrom
     * @param string $dateTo
     * @return int
     */
    public function getTotalQuantity($productId, $movementType = null, $dateFrom = null, $dateTo = null) {
        $sql = 'SELECT SUM(';
        
        if ($movementType === 'outgoing') {
            $sql .= 'ABS(quantity)';
        } else {
            $sql .= 'quantity';
        }
        
        $sql .= ') FROM inventory_movements WHERE product_id = ?';
        $params = [$productId];
        
        if ($movementType) {
            $sql .= ' AND movement_type = ?';
            $params[] = $movementType;
        }
        
        if ($dateFrom) {
            $sql .= ' AND DATE(created_at) >= ?';
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= ' AND DATE(created_at) <= ?';
            $params[] = $dateTo;
        }
        
        return $this->db->getValue($sql, $params) ?: 0;
    }
    
    /**
     * Створення запису про коригування товару (інвентаризація)
     *
     * @param int $productId
     * @param int $warehouseId
     * @param int $newQuantity
     * @param string $notes
     * @param int $userId
     * @return int|bool
     */
    public function adjustInventory($productId, $warehouseId, $newQuantity, $notes, $userId) {
        try {
            // Отримуємо поточну кількість товару
            $productModel = new Product();
            $product = $productModel->getById($productId);
            
            if (!$product) {
                throw new Exception('Продукт не знайдено');
            }
            
            // Розраховуємо різницю кількості
            $quantityDifference = $newQuantity - $product['stock_quantity'];
            
            // Якщо різниці немає, нічого не робимо
            if ($quantityDifference == 0) {
                return true;
            }
            
            // Визначаємо тип руху
            $movementType = $quantityDifference > 0 ? 'incoming' : 'outgoing';
            
            // Створюємо запис про рух
            $movementData = [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantityDifference,
                'movement_type' => $movementType,
                'reference_type' => 'adjustment',
                'notes' => $notes,
                'created_by' => $userId
            ];
            
            return $this->create($movementData);
            
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                error_log("Error in InventoryMovement::adjustInventory: " . $e->getMessage());
                error_log($e->getTraceAsString());
            }
            
            return false;
        }
    }
    
    /**
     * Експорт руху товарів у CSV
     *
     * @param array $filters
     * @return string
     */
    public function exportToCSV($filters = []) {
        // Отримуємо дані для експорту
        $data = $this->getWithDetails($filters);
        $items = $data['items'];
        
        // Створюємо тимчасовий файл
        $tempFile = tempnam(sys_get_temp_dir(), 'export');
        $handle = fopen($tempFile, 'w');
        
        // Додаємо заголовки
        $headers = [
            'ID', 'Дата', 'Продукт', 'Склад', 'Тип руху', 'Кількість', 
            'Тип посилання', 'ID посилання', 'Примітки', 'Виконавець'
        ];
        
        // Додаємо BOM для UTF-8
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Записуємо заголовки
        fputcsv($handle, $headers);
        
        // Записуємо дані
        foreach ($items as $item) {
            $row = [
                $item['id'],
                $item['created_at'],
                $item['product_name'],
                $item['warehouse_name'],
                $item['movement_type'],
                $item['quantity'],
                $item['reference_type'],
                $item['reference_id'],
                $item['notes'],
                $item['first_name'] . ' ' . $item['last_name']
            ];
            
            fputcsv($handle, $row);
        }
        
        fclose($handle);
        
        return $tempFile;
    }
}