<?php
// app/models/OrderItem.php - Обновленная модель для товаров в заказе с поддержкой контейнеров

class OrderItem extends BaseModel {
    protected $table = 'order_items';
    protected $fillable = [
        'order_id', 'product_id', 'container_id', 'quantity', 'price', 'volume'
    ];
    
    /**
     * Получение всех товаров для определенного заказа
     *
     * @param int $orderId
     * @return array
     */
    public function getByOrderId($orderId) {
        return $this->where('order_id = ?', [$orderId]);
    }
    
    /**
     * Получение товаров заказа с полной информацией о продуктах и контейнерах
     *
     * @param int $orderId
     * @return array
     */
    public function getWithProductInfo($orderId) {
        $sql = 'SELECT 
                    oi.*, 
                    p.name as product_name, 
                    p.image, 
                    p.category_id,
                    pc.volume,
                    pc.volume as container_volume,
                    CASE 
                        WHEN oi.container_id IS NOT NULL THEN CONCAT(p.name, " (", pc.volume, " л)")
                        ELSE p.name 
                    END as display_name,
                    CASE 
                        WHEN oi.container_id IS NOT NULL THEN pc.price
                        ELSE oi.price 
                    END as unit_price,
                    CASE 
                        WHEN oi.container_id IS NOT NULL THEN (pc.price / pc.volume)
                        ELSE (oi.price / 1) 
                    END as price_per_liter
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                LEFT JOIN product_containers pc ON oi.container_id = pc.id
                WHERE oi.order_id = ?
                ORDER BY oi.id';
        
        return $this->db->getAll($sql, [$orderId]);
    }
    
    /**
     * Создание товара в заказе с поддержкой контейнеров
     *
     * @param array $data
     * @return int|bool
     */
    public function create($data) {
        // Если указан container_id, получаем информацию о контейнере
        if (!empty($data['container_id'])) {
            $containerModel = new ProductContainer();
            $container = $containerModel->getById($data['container_id']);
            
            if ($container) {
                $data['price'] = $container['price'];
                $data['volume'] = $container['volume'];
                
                // Проверяем наличие на складе
                if ($container['stock_quantity'] < $data['quantity']) {
                    throw new Exception('Недостатньо товару на складі. Доступно: ' . $container['stock_quantity']);
                }
            }
        } else {
            // Если контейнер не указан, используем базовую цену продукта
            $productModel = new Product();
            $product = $productModel->getById($data['product_id']);
            
            if ($product) {
                $data['price'] = $product['price'];
                $data['volume'] = 1; // Базовый объем
                
                // Проверяем наличие на складе
                if ($product['stock_quantity'] < $data['quantity']) {
                    throw new Exception('Недостатньо товару на складі. Доступно: ' . $product['stock_quantity']);
                }
            }
        }
        
        return parent::create($data);
    }
    
    /**
     * Вычисление общей суммы заказа
     *
     * @param int $orderId
     * @return float
     */
    public function calculateOrderTotal($orderId) {
        $sql = 'SELECT SUM(quantity * price) FROM order_items WHERE order_id = ?';
        return $this->db->getValue($sql, [$orderId]) ?: 0;
    }
    
    /**
     * Получение статистики по объемам в заказе
     *
     * @param int $orderId
     * @return array
     */
    public function getOrderVolumeStats($orderId) {
        $sql = 'SELECT 
                    SUM(oi.quantity * COALESCE(oi.volume, 1)) as total_volume,
                    SUM(oi.quantity) as total_items,
                    COUNT(DISTINCT oi.product_id) as unique_products,
                    AVG(oi.price / COALESCE(oi.volume, 1)) as avg_price_per_liter
                FROM order_items oi 
                WHERE oi.order_id = ?';
        
        return $this->db->getOne($sql, [$orderId]);
    }
    
    /**
     * Удаление всех товаров из заказа
     *
     * @param int $orderId
     * @return bool
     */
    public function deleteByOrderId($orderId) {
        $sql = 'DELETE FROM order_items WHERE order_id = ?';
        $this->db->query($sql, [$orderId]);
        return true;
    }
    
    /**
     * Получение популярных продуктов с учетом контейнеров
     *
     * @param int $limit
     * @return array
     */
    public function getPopularProducts($limit = 5) {
        $sql = 'SELECT 
                    p.id, 
                    p.name, 
                    p.image,
                    SUM(oi.quantity) as total_ordered,
                    COUNT(DISTINCT oi.container_id) as container_variants,
                    AVG(oi.price) as avg_price,
                    SUM(oi.quantity * COALESCE(oi.volume, 1)) as total_volume
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                JOIN orders o ON oi.order_id = o.id 
                WHERE o.status != "cancelled" AND p.is_active = 1
                GROUP BY p.id, p.name, p.image 
                ORDER BY total_ordered DESC 
                LIMIT ?';
        
        return $this->db->getAll($sql, [$limit]);
    }
    
    /**
     * Проверка наличия продукта в заказе
     *
     * @param int $orderId
     * @param int $productId
     * @param int $containerId
     * @return bool
     */
    public function hasProduct($orderId, $productId, $containerId = null) {
        if ($containerId) {
            $sql = 'SELECT COUNT(*) FROM order_items WHERE order_id = ? AND product_id = ? AND container_id = ?';
            return $this->db->getValue($sql, [$orderId, $productId, $containerId]) > 0;
        } else {
            $sql = 'SELECT COUNT(*) FROM order_items WHERE order_id = ? AND product_id = ? AND container_id IS NULL';
            return $this->db->getValue($sql, [$orderId, $productId]) > 0;
        }
    }
    
    /**
     * Обновление количества товара в заказе
     *
     * @param int $orderId
     * @param int $productId
     * @param int $quantity
     * @param int $containerId
     * @return bool
     */
    public function updateQuantity($orderId, $productId, $quantity, $containerId = null) {
        if ($containerId) {
            $sql = 'UPDATE order_items SET quantity = ? WHERE order_id = ? AND product_id = ? AND container_id = ?';
            $this->db->query($sql, [$quantity, $orderId, $productId, $containerId]);
        } else {
            $sql = 'UPDATE order_items SET quantity = ? WHERE order_id = ? AND product_id = ? AND container_id IS NULL';
            $this->db->query($sql, [$quantity, $orderId, $productId]);
        }
        return true;
    }
    
    /**
     * Получение самых выгодных комбинаций продукт-контейнер
     *
     * @param int $limit
     * @return array
     */
    public function getBestValueCombinations($limit = 10) {
        $sql = 'SELECT 
                    p.id as product_id,
                    p.name as product_name,
                    pc.id as container_id,
                    pc.volume,
                    pc.price,
                    (pc.price / pc.volume) as price_per_liter,
                    SUM(oi.quantity) as times_ordered,
                    AVG(oi.quantity) as avg_quantity_per_order
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                JOIN product_containers pc ON oi.container_id = pc.id
                JOIN orders o ON oi.order_id = o.id
                WHERE o.status != "cancelled" 
                AND pc.is_active = 1 
                AND pc.stock_quantity > 0
                GROUP BY p.id, p.name, pc.id, pc.volume, pc.price
                ORDER BY (pc.price / pc.volume) ASC, times_ordered DESC
                LIMIT ?';
        
        return $this->db->getAll($sql, [$limit]);
    }
}