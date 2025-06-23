<?php
// app/models/ProductContainer.php - Модель для работы с объемами тары

class ProductContainer extends BaseModel {
    protected $table = 'product_containers';
    protected $fillable = [
        'product_id', 'volume', 'price', 'stock_quantity', 'is_active'
    ];
    
    /**
     * Получить все объемы тары для продукта
     */
    public function getByProductId($productId) {
        $sql = "SELECT * FROM {$this->table} WHERE product_id = ? ORDER BY volume ASC";
        return $this->db->getAll($sql, [$productId]);
    }
    
    /**
     * Получить конкретный объем тары
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->getOne($sql, [$id]);
    }
    
    /**
     * Создать новый контейнер
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (product_id, volume, price, stock_quantity, is_active) 
                VALUES (:product_id, :volume, :price, :stock_quantity, :is_active)";
        
        $result = $this->db->query($sql, $data);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Обновить контейнер
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $fields[] = "$key = :$key";
                $params[$key] = $value;
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params['id'] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = :id";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Удалить контейнер
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    /**
     * Обновить остатки при заказе
     */
    public function updateStock($containerId, $quantity, $operation = 'subtract') {
        $operator = ($operation === 'subtract') ? '-' : '+';
        $sql = "UPDATE {$this->table} 
                SET stock_quantity = stock_quantity {$operator} ?, updated_at = NOW() 
                WHERE id = ?";
        
        return $this->db->query($sql, [$quantity, $containerId]);
    }
    
    /**
     * Получить доступные объемы для продукта
     */
    public function getAvailableVolumes($productId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE product_id = ? AND is_active = 1 AND stock_quantity > 0 
                ORDER BY volume ASC";
        return $this->db->getAll($sql, [$productId]);
    }
    
    /**
     * Получить минимальную цену для продукта
     */
    public function getMinPrice($productId) {
        $sql = "SELECT MIN(price) as min_price FROM {$this->table} 
                WHERE product_id = ? AND is_active = 1";
        $result = $this->db->getOne($sql, [$productId]);
        return $result['min_price'] ?? 0;
    }
    
    /**
     * Проверить наличие объема на складе
     */
    public function checkStock($containerId, $quantity) {
        $sql = "SELECT stock_quantity FROM {$this->table} WHERE id = ?";
        $result = $this->db->getOne($sql, [$containerId]);
        return ($result['stock_quantity'] ?? 0) >= $quantity;
    }
    
    /**
     * Синхронизация общего количества с основной таблицей products
     */
    public function syncTotalStock($productId) {
        // Получаем сумму всех активных контейнеров
        $sql = "SELECT SUM(stock_quantity) as total_stock, 
                       SUM(price * stock_quantity) / SUM(stock_quantity) as avg_price
                FROM {$this->table} 
                WHERE product_id = ? AND is_active = 1";
        
        $result = $this->db->getOne($sql, [$productId]);
        
        if ($result) {
            $totalStock = $result['total_stock'] ?? 0;
            $avgPrice = $result['avg_price'] ?? 0;
            
            // Обновляем основную таблицу products
            $productModel = new Product();
            return $productModel->update($productId, [
                'stock_quantity' => $totalStock,
                'price' => $avgPrice
            ]);
        }
        
        return false;
    }
}