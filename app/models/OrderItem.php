<?php
// app/models/OrderItem.php - Модель для товаров в заказе

class OrderItem extends BaseModel {
    protected $table = 'order_items';
    protected $fillable = [
        'order_id', 'product_id', 'quantity', 'price'
    ];
    
    /**
     * Получение всех товаров для заказа
     *
     * @param int $orderId
     * @return array
     */
    public function getByOrderId($orderId) {
        return $this->where('order_id = ?', [$orderId]);
    }
    
    /**
     * Получение товаров заказа с информацией о продуктах
     *
     * @param int $orderId
     * @return array
     */
    public function getWithProductInfo($orderId) {
        $sql = 'SELECT oi.*, p.name as product_name, p.image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?';
        
        return $this->db->getAll($sql, [$orderId]);
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
     * Проверка наличия продукта в заказе
     *
     * @param int $orderId
     * @param int $productId
     * @return bool
     */
    public function productExists($orderId, $productId) {
        $sql = 'SELECT COUNT(*) FROM order_items WHERE order_id = ? AND product_id = ?';
        return $this->db->getValue($sql, [$orderId, $productId]) > 0;
    }
    
    /**
     * Обновление количества продукта в заказе
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
    
    /**
     * Получение самых популярных продуктов
     *
     * @param int $limit
     * @return array
     */
    public function getTopProducts($limit = 5) {
        $sql = 'SELECT p.id, p.name, p.price, p.image, SUM(oi.quantity) as total_sold 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                JOIN orders o ON oi.order_id = o.id 
                WHERE o.status != "cancelled" 
                GROUP BY p.id, p.name, p.price, p.image 
                ORDER BY total_sold DESC 
                LIMIT ?';
        
        return $this->db->getAll($sql, [$limit]);
    }
    
    /**
     * Получение статистики продаж по категориям
     *
     * @return array
     */
    public function getSalesByCategory() {
        $sql = 'SELECT c.id, c.name, COUNT(oi.id) as items_count, SUM(oi.quantity) as quantity, SUM(oi.price * oi.quantity) as total 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                JOIN categories c ON p.category_id = c.id 
                JOIN orders o ON oi.order_id = o.id 
                WHERE o.status != "cancelled" 
                GROUP BY c.id, c.name 
                ORDER BY total DESC';
        
        return $this->db->getAll($sql);
    }
}