<?php
// app/models/Category.php - Модель для роботи з категоріями

class Category extends BaseModel {
    protected $table = 'categories';
    protected $fillable = [
        'name', 'description', 'image'
    ];
    
    /**
     * Отримання всіх категорій з кількістю продуктів
     *
     * @return array
     */
    public function getAllWithProductCount() {
        $sql = 'SELECT c.*, COUNT(p.id) as product_count 
                FROM categories c 
                LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
                GROUP BY c.id, c.name, c.description, c.image, c.created_at, c.updated_at
                ORDER BY c.name';
                
        return $this->db->getAll($sql);
    }
    
    /**
     * Отримання категорії з продуктами
     *
     * @param int $id
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getWithProducts($id, $page = 1, $perPage = ITEMS_PER_PAGE) {
        // Отримання категорії
        $category = $this->getById($id);
        
        if (!$category) {
            return null;
        }
        
        // Отримання продуктів цієї категорії з пагінацією
        $page = max(1, intval($page));
        
        // Отримання загальної кількості активних продуктів у категорії
        $countSql = 'SELECT COUNT(*) FROM products WHERE category_id = ? AND is_active = 1';
        $totalItems = $this->db->getValue($countSql, [$id]);
        
        // Розрахунок пагінації
        $totalPages = ceil($totalItems / $perPage);
        $page = min($page, max(1, $totalPages));
        $offset = ($page - 1) * $perPage;
        
        // Отримання продуктів для поточної сторінки
        $productsSql = 'SELECT * FROM products WHERE category_id = ? AND is_active = 1 ORDER BY is_featured DESC, name LIMIT ?, ?';
        $products = $this->db->getAll($productsSql, [$id, $offset, $perPage]);
        
        // Додавання продуктів до категорії
        $category['products'] = $products;
        $category['pagination'] = [
            'current_page' => $page,
            'per_page' => $perPage,
            'total_items' => $totalItems,
            'total_pages' => $totalPages
        ];
        
        return $category;
    }
    
    /**
     * Пошук категорій
     *
     * @param string $keyword
     * @return array
     */
    public function search($keyword) {
        return parent::search($keyword, ['name', 'description']);
    }
    
    /**
     * Отримання категорій для навігаційного меню
     *
     * @param int $limit
     * @return array
     */
    public function getForMenu($limit = null) {
        $sql = 'SELECT c.*, COUNT(p.id) as product_count 
                FROM categories c 
                LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
                GROUP BY c.id, c.name
                HAVING COUNT(p.id) > 0
                ORDER BY product_count DESC';
                
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
                
        return $this->db->getAll($sql);
    }
    
    /**
     * Перевірка, чи є продукти в категорії
     *
     * @param int $id
     * @return bool
     */
    public function hasProducts($id) {
        $sql = 'SELECT COUNT(*) FROM products WHERE category_id = ?';
        return $this->db->getValue($sql, [$id]) > 0;
    }
}