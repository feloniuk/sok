<?php
// app/models/Product.php - Модель для работы с продуктами

class Product extends BaseModel {
    protected $table = 'products';
    protected $fillable = [
        'category_id', 'name', 'description', 'price', 
        'stock_quantity', 'image', 'is_featured', 'is_active'
    ];

    /**
     * Отримання загальної кількості товарів
     * 
     * @param array $filters Фільтри для підрахунку
     * @return int
     */
    public function getCount($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} p";
        $params = [];
        $conditions = [];
        
        // Додавання умов фільтрації
        if (!empty($filters['category_id'])) {
            $conditions[] = "p.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['keyword'])) {
            $conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
            $keyword = '%' . $filters['keyword'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
        }
        
        if (isset($filters['min_stock']) && $filters['min_stock'] !== '') {
            $conditions[] = "p.stock_quantity >= ?";
            $params[] = intval($filters['min_stock']);
        }
        
        if (isset($filters['max_stock']) && $filters['max_stock'] !== '') {
            $conditions[] = "p.stock_quantity <= ?";
            $params[] = intval($filters['max_stock']);
        }
        
        if (!empty($filters['status'])) {
            $conditions[] = "p.status = ?";
            $params[] = $filters['status'];
        } else {
            // За замовчуванням показуємо тільки активні товари
            $conditions[] = "p.status != 'deleted'";
        }
        
        // Додавання умов до запиту
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        try {
            $result = $this->db->getOne($sql, $params);
            return intval($result['total']);
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                error_log("Error in Product::getCount: " . $e->getMessage());
            }
            return 0;
        }
    }
    
    /**
     * Отримання кількості товарів за категоріями
     * 
     * @return array
     */
    public function getCountByCategories() {
        $sql = "SELECT 
                    p.category_id,
                    c.name as category_name,
                    COUNT(p.id) as product_count,
                    SUM(p.stock_quantity) as total_stock,
                    SUM(p.price * p.stock_quantity) as total_value
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status != 'deleted'
                GROUP BY p.category_id, c.name
                ORDER BY product_count DESC";
        
        try {
            return $this->db->getAll($sql);
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                error_log("Error in Product::getCountByCategories: " . $e->getMessage());
            }
            return [];
        }
    }
    
    /**
     * Отримання статистики по товарах
     * 
     * @return array
     */
    public function getStats() {
        $stats = [
            'total_products' => 0,
            'active_products' => 0,
            'low_stock_products' => 0,
            'out_of_stock_products' => 0,
            'total_stock_value' => 0,
            'average_price' => 0,
            'categories_count' => 0
        ];
        
        try {
            // Загальна статистика
            $generalStats = $this->db->getOne("
                SELECT 
                    COUNT(*) as total_products,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_products,
                    COUNT(CASE WHEN stock_quantity <= 5 AND stock_quantity > 0 THEN 1 END) as low_stock_products,
                    COUNT(CASE WHEN stock_quantity = 0 THEN 1 END) as out_of_stock_products,
                    SUM(price * stock_quantity) as total_stock_value,
                    AVG(price) as average_price,
                    COUNT(DISTINCT category_id) as categories_count
                FROM {$this->table}
                WHERE status != 'deleted'
            ");
            
            if ($generalStats) {
                $stats = array_merge($stats, [
                    'total_products' => intval($generalStats['total_products']),
                    'active_products' => intval($generalStats['active_products']),
                    'low_stock_products' => intval($generalStats['low_stock_products']),
                    'out_of_stock_products' => intval($generalStats['out_of_stock_products']),
                    'total_stock_value' => floatval($generalStats['total_stock_value']),
                    'average_price' => floatval($generalStats['average_price']),
                    'categories_count' => intval($generalStats['categories_count'])
                ]);
            }
            
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                error_log("Error in Product::getStats: " . $e->getMessage());
            }
        }
        
        return $stats;
    }
    
    /**
     * Пошук товару по штрихкоду
     * 
     * @param string $barcode
     * @return array|null
     */
    public function getByBarcode($barcode) {
        // Парсинг штрихкоду для отримання ID
        $productId = $this->parseBarcodeToId($barcode);
        
        if (!$productId) {
            return null;
        }
        
        return $this->getById($productId);
    }
    
    /**
     * Генерація штрихкоду з ID товару
     * 
     * @param int $productId
     * @return string
     */
    public function generateBarcode($productId) {
        return str_pad($productId, 8, '0', STR_PAD_LEFT);
    }
    
    /**
     * Парсинг штрихкоду для отримання ID товару
     * 
     * @param string $barcode
     * @return int|null
     */
    public function parseBarcodeToId($barcode) {
        // Видаляємо всі нецифрові символи
        $barcode = preg_replace('/[^0-9]/', '', $barcode);
        
        // Перевіряємо, чи є штрихкод
        if (empty($barcode)) {
            return null;
        }
        
        // Якщо довжина менше 8 символів, доповнюємо нулями зліва
        if (strlen($barcode) < 8) {
            $barcode = str_pad($barcode, 8, '0', STR_PAD_LEFT);
        }
        
        // Якщо довжина більше 8 символів, беремо останні 8
        if (strlen($barcode) > 8) {
            $barcode = substr($barcode, -8);
        }
        
        // Повертаємо число без ведучих нулів
        $productId = intval($barcode);
        
        // Перевіряємо, чи існує товар з таким ID
        if ($this->getById($productId)) {
            return $productId;
        }
        
        return null;
    }
    
    /**
     * Валідація штрихкоду
     * 
     * @param string $barcode
     * @return bool
     */
    public function validateBarcode($barcode) {
        // Видаляємо пробіли та інші символи
        $barcode = trim($barcode);
        
        // Перевіряємо, чи містить тільки цифри
        if (!preg_match('/^[0-9]+$/', $barcode)) {
            return false;
        }
        
        // Перевіряємо довжину (від 1 до 8 цифр)
        if (strlen($barcode) < 1 || strlen($barcode) > 8) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Отримання всіх товарів з їх штрихкодами
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllWithBarcodes($limit = null, $offset = 0) {
        $sql = "SELECT 
                    p.*,
                    c.name as category_name
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status != 'deleted'
                ORDER BY p.id";
        
        $params = [];
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = intval($limit);
            $params[] = intval($offset);
        }
        
        try {
            $products = $this->db->getAll($sql, $params);
            
            // Додаємо штрихкод до кожного товару
            foreach ($products as &$product) {
                $product['barcode'] = $this->generateBarcode($product['id']);
            }
            
            return $products;
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                error_log("Error in Product::getAllWithBarcodes: " . $e->getMessage());
            }
            return [];
        }
    }
    
    /**
     * Пошук товарів з можливістю пошуку по штрихкоду
     * 
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function searchWithBarcode($query, $limit = 20) {
        $params = [];
        $conditions = [];
        
        // Якщо запит схожий на штрихкод (тільки цифри)
        if (preg_match('/^[0-9]+$/', $query)) {
            $productId = $this->parseBarcodeToId($query);
            if ($productId) {
                $conditions[] = "p.id = ?";
                $params[] = $productId;
            }
        }
        
        // Пошук по назві та опису
        if (empty($conditions)) {
            $searchTerm = '%' . $query . '%';
            $conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql = "SELECT 
                    p.*,
                    c.name as category_name
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status != 'deleted' AND (" . implode(" OR ", $conditions) . ")
                ORDER BY p.name
                LIMIT ?";
        
        $params[] = intval($limit);
        
        try {
            $products = $this->db->getAll($sql, $params);
            
            // Додаємо штрихкод до кожного товару
            foreach ($products as &$product) {
                $product['barcode'] = $this->generateBarcode($product['id']);
            }
            
            return $products;
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                error_log("Error in Product::searchWithBarcode: " . $e->getMessage());
            }
            return [];
        }
    }
    
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
     * @param array $fields
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