<?php
// app/models/ProductContainer.php - Исправленная модель для объемов тары продуктов

class ProductContainer extends BaseModel {
    protected $table = 'product_containers';
    protected $fillable = [
        'product_id', 'volume', 'price', 'stock_quantity', 'is_active'
    ];

    /**
     * Получить все объемы тары для продукта
     */
    public function getByProductId($productId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE product_id = ? ORDER BY volume ASC";
        return $this->db->getAll($sql, [$productId]);
    }

    /**
     * Получить конкретный объем тары
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->getOne($sql, [$id]);
    }

    /**
     * Создать новый объем тары для продукта
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return parent::create($data);
    }

    /**
     * Обновить объем тары
     */
    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return parent::update($id, $data);
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

        return $this->db->getAll($sql, $params);
    }

    /**
     * Обновить остатки при заказе
     */
    public function updateStock($containerId, $quantity, $operation = 'subtract')
    {
        $operator = ($operation === 'subtract') ? '-' : '+';
        $sql = "UPDATE {$this->table} 
                SET stock_quantity = stock_quantity {$operator} ?, updated_at = NOW() 
                WHERE id = ?";
        
        return $this->db->query($sql, [$quantity, $containerId]);
    }

    /**
     * Получить доступные объемы для продукта
     */
    public function getAvailableVolumes($productId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE product_id = ? AND is_active = 1 AND stock_quantity > 0 
                ORDER BY volume ASC";
        return $this->db->getAll($sql, [$productId]);
    }

    /**
     * Получить минимальную цену для продукта
     */
    public function getMinPrice($productId)
    {
        $sql = "SELECT MIN(price) as min_price FROM {$this->table} 
                WHERE product_id = ? AND is_active = 1";
        $result = $this->db->getOne($sql, [$productId]);
        return $result['min_price'] ?? 0;
    }

    /**
     * Проверить наличие объема на складе
     */
    public function checkStock($containerId, $quantity)
    {
        $sql = "SELECT stock_quantity FROM {$this->table} WHERE id = ?";
        $result = $this->db->getOne($sql, [$containerId]);
        return ($result['stock_quantity'] ?? 0) >= $quantity;
    }
}
?>