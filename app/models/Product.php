<?php
// app/models/Product.php - Модель для работы с продуктами

class Product extends BaseModel {
    protected $table = 'products';
    protected $fillable = [
        'category_id', 'name', 'description', 'price', 
        'stock_quantity', 'image', 'is_featured', 'is_active'
    ];
    
    /**
     * Получение всех активных продуктов
     *
     * @return array
     */
    public function getAllActive() {
        return $this->where('is_active = 1');
    }
    
    /**
     * Получение всех рекомендуемых продуктов
     *
     * @param int $limit
     * @return array
     */
    public function getFeatured($limit = 6) {
        $sql = 'SELECT * FROM products WHERE is_featured = 1 AND is_active = 1 LIMIT ?';
        return $this->db->getAll($sql, [$limit]);
    }
    
    /**
     * Получение продуктов по категории
     *
     * @param int $categoryId
     * @return array
     */
    public function getByCategory($categoryId) {
        return $this->where('category_id = ? AND is_active = 1', [$categoryId]);
    }
    
    /**
     * Поиск продуктов
     *
     * @param string $keyword
     * @param array $fields (опционально)
     * @return array
     */
    public function search($keyword, $fields = null) {
        if ($fields === null) {
            $fields = ['name', 'description'];
        }
        return parent::search($keyword, $fields);
    }
    
    /**
     * Получение продуктов с пагинацией и фильтрацией
     *
     * @param int $page
     * @param int $perPage
     * @param array $filters
     * @return array
     */
    public function getFiltered($page = 1, $perPage = ITEMS_PER_PAGE, $filters = []) {
        $conditions = [];
        $params = [];
        
        // Фильтр по категории
        if (!empty($filters['category_id'])) {
            $conditions[] = 'category_id = ?';
            $params[] = $filters['category_id'];
        }
        
        // Фильтр по активности
        if (isset($filters['is_active'])) {
            $conditions[] = 'is_active = ?';
            $params[] = $filters['is_active'];
        }
        
        // Фильтр по рекомендуемым
        if (isset($filters['is_featured'])) {
            $conditions[] = 'is_featured = ?';
            $params[] = $filters['is_featured'];
        }
        
        // Фильтр по цене (минимальная)
        if (!empty($filters['min_price'])) {
            $conditions[] = 'price >= ?';
            $params[] = $filters['min_price'];
        }
        
        // Фильтр по цене (максимальная)
        if (!empty($filters['max_price'])) {
            $conditions[] = 'price <= ?';
            $params[] = $filters['max_price'];
        }
        
        // Поиск по ключевому слову
        if (!empty($filters['keyword'])) {
            $conditions[] = '(name LIKE ? OR description LIKE ?)';
            $params[] = '%' . $filters['keyword'] . '%';
            $params[] = '%' . $filters['keyword'] . '%';
        }
        
        // Формирование условия
        $whereClause = '';
        if (!empty($conditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }
        
        // Сортировка
        $orderClause = 'ORDER BY ';
        
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_asc':
                    $orderClause .= 'price ASC';
                    break;
                case 'price_desc':
                    $orderClause .= 'price DESC';
                    break;
                case 'name_asc':
                    $orderClause .= 'name ASC';
                    break;
                case 'name_desc':
                    $orderClause .= 'name DESC';
                    break;
                case 'newest':
                    $orderClause .= 'created_at DESC';
                    break;
                default:
                    $orderClause .= 'id DESC';
            }
        } else {
            $orderClause .= 'id DESC';
        }
        
        // Формирование SQL-запроса
        $sql = "SELECT * FROM {$this->table} $whereClause $orderClause";
        
        // Пагинация
        $page = max(1, intval($page));
        
        // Получение общего количества записей
        $countSql = "SELECT COUNT(*) FROM {$this->table} $whereClause";
        $totalItems = $this->db->getValue($countSql, $params);
        
        // Расчет пагинации
        $totalPages = ceil($totalItems / $perPage);
        $page = min($page, max(1, $totalPages));
        $offset = ($page - 1) * $perPage;
        
        // Получение записей для текущей страницы
        $pageSql = "$sql LIMIT $offset, $perPage";
        $items = $this->db->getAll($pageSql, $params);
        
        return [
            'items' => $items,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_items' => $totalItems,
            'total_pages' => $totalPages
        ];
    }
    
    /**
     * Получение продукта с информацией о категории
     *
     * @param int $id
     * @return array|null
     */
    public function getWithCategory($id) {
        $sql = 'SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ?';
        
        return $this->db->getOne($sql, [$id]);
    }
    
    /**
     * Получение всех продуктов с информацией о категории
     *
     * @return array
     */
    public function getAllWithCategory() {
        $sql = 'SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.id DESC';
        
        return $this->db->getAll($sql);
    }
    
    /**
     * Обновление количества товара на складе
     *
     * @param int $id
     * @param int $quantity
     * @return bool
     */
    public function updateStock($id, $quantity) {
        $sql = 'UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?';
        $this->db->query($sql, [$quantity, $id]);
        
        return true;
    }
    
    /**
     * Получение низко-запасных продуктов
     *
     * @param int $threshold
     * @return array
     */
    public function getLowStockProducts($threshold = 10) {
        $sql = 'SELECT * FROM products WHERE stock_quantity < ? ORDER BY stock_quantity ASC';
        return $this->db->getAll($sql, [$threshold]);
    }
    
    /**
     * Получение данных аналитики по продуктам
     *
     * @param string $period
     * @return array
     */
    public function getAnalyticsData($period = 'month') {
        $data = [];
        
        switch ($period) {
            case 'week':
                $interval = 'INTERVAL 7 DAY';
                break;
            case 'month':
                $interval = 'INTERVAL 1 MONTH';
                break;
            case 'quarter':
                $interval = 'INTERVAL 3 MONTH';
                break;
            case 'year':
                $interval = 'INTERVAL 1 YEAR';
                break;
            default:
                $interval = 'INTERVAL 1 MONTH';
        }
        
        // Топ продаваемых продуктов
        $sql = 'SELECT p.id, p.name, SUM(sa.quantity_sold) as total_sold, SUM(sa.revenue) as total_revenue 
                FROM products p 
                JOIN sales_analytics sa ON p.id = sa.product_id 
                WHERE sa.date >= DATE_SUB(CURDATE(), ' . $interval . ') 
                GROUP BY p.id, p.name 
                ORDER BY total_sold DESC 
                LIMIT 10';
        
        $data['topSellingProducts'] = $this->db->getAll($sql);
        
        // Общие показатели
        $sql = 'SELECT SUM(quantity_sold) as total_quantity, SUM(revenue) as total_revenue, SUM(profit) as total_profit 
                FROM sales_analytics 
                WHERE date >= DATE_SUB(CURDATE(), ' . $interval . ')';
        
        $data['totalStats'] = $this->db->getOne($sql);
        
        return $data;
    }
}