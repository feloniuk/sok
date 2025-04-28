<?php
// app/models/OrderItem.php - Модель для товарів у замовленні

class OrderItem extends BaseModel {
    protected $table = 'order_items';
    protected $fillable = [
        'order_id', 'product_id', 'quantity', 'price', 'warehouse_id'
    ];
    
    /**
     * Отримання всіх товарів для певного замовлення
     *
     * @param int $orderId
     * @return array
     */
    public function getByOrderId($orderId) {
        return $this->where('order_id = ?', [$orderId]);
    }
    
    /**
     * Отримання товарів замовлення з інформацією про продукти
     *
     * @param int $orderId
     * @return array
     */
    public function getWithProductInfo($orderId) {
        $sql = 'SELECT oi.*, p.name as product_name, p.image, p.category_id 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?';
        
        return $this->db->getAll($sql, [$orderId]);
    }
    
    /**
     * Обчислення загальної суми замовлення
     *
     * @param int $orderId
     * @return float
     */
    public function calculateOrderTotal($orderId) {
        $sql = 'SELECT SUM(quantity * price) FROM order_items WHERE order_id = ?';
        return $this->db->getValue($sql, [$orderId]) ?: 0;
    }
    
    /**
     * Видалення всіх товарів із замовлення
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
     * Отримання популярних продуктів (для рекомендацій)
     *
     * @param int $limit
     * @return array
     */
    public function getPopularProducts($limit = 5) {
        $sql = 'SELECT p.id, p.name, p.price, p.image, SUM(oi.quantity) as total_ordered 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                JOIN orders o ON oi.order_id = o.id 
                WHERE o.status != "cancelled" AND p.is_active = 1
                GROUP BY p.id, p.name, p.price, p.image 
                ORDER BY total_ordered DESC 
                LIMIT ?';
        
        return $this->db->getAll($sql, [$limit]);
    }
    
    /**
     * Перевірка, чи є продукт у замовленні
     *
     * @param int $orderId
     * @param int $productId
     * @return bool
     */
    public function hasProduct($orderId, $productId) {
        $sql = 'SELECT COUNT(*) FROM order_items WHERE order_id = ? AND product_id = ?';
        return $this->db->getValue($sql, [$orderId, $productId]) > 0;
    }
    
    /**
     * Оновлення кількості товару в замовленні
     *
     * @param int $orderId
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function updateQuantity($orderId, $productId, $quantity) {
        $sql = 'UPDATE order_items SET quantity = ? WHERE order_id = ? AND product_id = ?';
        $this->db->query($sql, [$quantity, $orderId, $productId]);
        return true;
    }
}