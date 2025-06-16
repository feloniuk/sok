<?php
// app/models/ProductContainer.php - Модель для объемов тары продуктов

class ProductContainer
{
    private $db;
    private $table = 'product_containers';

    public function __construct($database = null)
    {
        $this->db = $database ?: Database::getInstance();
    }

    /**
     * Получить все объемы тары для продукта
     */
    public function getByProductId($productId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE product_id = ? ORDER BY volume ASC";
        return $this->db->fetchAll($sql, [$productId]);
    }

    /**
     * Получить конкретный объем тары
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Создать новый объем тары для продукта
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (product_id, volume, price, stock_quantity, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        return $this->db->execute($sql, [
            $data['product_id'],
            $data['volume'],
            $data['price'],
            $data['stock_quantity'] ?? 0,
            $data['is_active'] ?? 1
        ]);
    }

    /**
     * Обновить объем тары
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} 
                SET volume = ?, price = ?, stock_quantity = ?, is_active = ?, updated_at = NOW() 
                WHERE id = ?";
        
        return $this->db->execute($sql, [
            $data['volume'],
            $data['price'],
            $data['stock_quantity'],
            $data['is_active'] ?? 1,
            $id
        ]);
    }

    /**
     * Удалить объем тары
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Получить продукты с их объемами тары
     */
    public function getProductsWithContainers($filters = [])
    {
        $sql = "SELECT 
                    p.id as product_id,
                    p.name as product_name,
                    p.description,
                    p.image,
                    p.category_id,
                    c.name as category_name,
                    pc.id as container_id,
                    pc.volume,
                    pc.price,
                    pc.stock_quantity,
                    pc.is_active as container_active
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN {$this->table} pc ON p.id = pc.product_id
                WHERE p.is_active = 1";

        $params = [];

        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['keyword'])) {
            $sql .= " AND p.name LIKE ?";
            $params[] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['min_price'])) {
            $sql .= " AND pc.price >= ?";
            $params[] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND pc.price <= ?";
            $params[] = $filters['max_price'];
        }

        $sql .= " ORDER BY p.name, pc.volume";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Обновить остатки при заказе
     */
    public function updateStock($containerId, $quantity, $operation = 'subtract')
    {
        $operator = ($operation === 'subtract') ? '-' : '+';
        $sql = "UPDATE {$this->table} 
                SET stock_quantity = stock_quantity {$operator} ?, updated_at = NOW() 
                WHERE id = ? AND stock_quantity >= ?";
        
        if ($operation === 'subtract') {
            return $this->db->execute($sql, [$quantity, $containerId, $quantity]);
        } else {
            return $this->db->execute($sql, [$quantity, $containerId, 0]);
        }
    }

    /**
     * Получить доступные объемы для продукта
     */
    public function getAvailableVolumes($productId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE product_id = ? AND is_active = 1 AND stock_quantity > 0 
                ORDER BY volume ASC";
        return $this->db->fetchAll($sql, [$productId]);
    }

    /**
     * Получить минимальную цену для продукта
     */
    public function getMinPrice($productId)
    {
        $sql = "SELECT MIN(price) as min_price FROM {$this->table} 
                WHERE product_id = ? AND is_active = 1";
        $result = $this->db->fetch($sql, [$productId]);
        return $result['min_price'] ?? 0;
    }

    /**
     * Проверить наличие объема на складе
     */
    public function checkStock($containerId, $quantity)
    {
        $sql = "SELECT stock_quantity FROM {$this->table} WHERE id = ?";
        $result = $this->db->fetch($sql, [$containerId]);
        return ($result['stock_quantity'] ?? 0) >= $quantity;
    }
}

?>