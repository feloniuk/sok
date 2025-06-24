<?php
// app/controllers/ApiController.php - API контроллер для работы с продуктами и контейнерами

class ApiController {
    private $productModel;
    private $containerModel;
    
    public function __construct() {
        $this->productModel = new Product();
        $this->containerModel = new ProductContainer();
    }
    
    /**
     * Получение продуктов с контейнерами для создания заказа
     */
    public function productsWithContainers() {
        try {
            $search = $_GET['search'] ?? '';
            $categoryId = $_GET['category_id'] ?? '';
            
            // Базовый SQL запрос для получения продуктов
            $sql = 'SELECT DISTINCT
                        p.id,
                        p.name,
                        p.description,
                        p.image,
                        p.category_id,
                        c.name as category_name
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.is_active = 1';
            
            $params = [];
            
            // Добавляем условия поиска
            if (!empty($search)) {
                $sql .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
                $params[] = '%' . $search . '%';
                $params[] = '%' . $search . '%';
            }
            
            if (!empty($categoryId)) {
                $sql .= ' AND p.category_id = ?';
                $params[] = $categoryId;
            }
            
            $sql .= ' ORDER BY p.name';
            
            $products = $this->productModel->db->getAll($sql, $params);
            
            // Для каждого продукта получаем доступные контейнеры
            foreach ($products as &$product) {
                $containers = $this->containerModel->getAvailableVolumes($product['id']);
                
                // Добавляем информацию о минимальной цене
                if (!empty($containers)) {
                    $minPrice = min(array_column($containers, 'price'));
                    $product['min_price'] = $minPrice;
                } else {
                    // Если нет контейнеров, используем базовую цену продукта
                    $productDetails = $this->productModel->getById($product['id']);
                    $product['min_price'] = $productDetails['price'] ?? 0;
                    $containers = [];
                }
                
                $product['containers'] = $containers;
            }
            
            $this->jsonResponse(['products' => $products]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => 'Помилка отримання товарів'], 500);
        }
    }
    
    /**
     * Получение информации о конкретном контейнере
     */
    public function getContainerInfo() {
        try {
            $containerId = $_GET['container_id'] ?? 0;
            
            if (!$containerId) {
                $this->jsonResponse(['error' => 'ID контейнера не вказано'], 400);
                return;
            }
            
            $container = $this->containerModel->getById($containerId);
            
            if (!$container) {
                $this->jsonResponse(['error' => 'Контейнер не знайдено'], 404);
                return;
            }
            
            // Получаем информацию о продукте
            $product = $this->productModel->getById($container['product_id']);
            
            $response = [
                'container' => $container,
                'product' => $product,
                'price_per_liter' => $container['price'] / $container['volume'],
                'available' => $container['is_active'] && $container['stock_quantity'] > 0
            ];
            
            $this->jsonResponse($response);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => 'Помилка отримання інформації про контейнер'], 500);
        }
    }
    
    /**
     * Проверка наличия товара на складе
     */
    public function checkStock() {
        try {
            $containerId = $_POST['container_id'] ?? $_GET['container_id'] ?? 0;
            $quantity = $_POST['quantity'] ?? $_GET['quantity'] ?? 1;
            
            if (!$containerId) {
                $this->jsonResponse(['error' => 'ID контейнера не вказано'], 400);
                return;
            }
            
            $available = $this->containerModel->checkStock($containerId, $quantity);
            
            $response = [
                'available' => $available,
                'container_id' => $containerId,
                'requested_quantity' => $quantity
            ];
            
            if (!$available) {
                $container = $this->containerModel->getById($containerId);
                $response['available_quantity'] = $container['stock_quantity'] ?? 0;
            }
            
            $this->jsonResponse($response);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => 'Помилка перевірки наявності'], 500);
        }
    }
    
    /**
     * Получение рекомендуемых комбинаций продукт-контейнер
     */
    public function getRecommendations() {
        try {
            $limit = $_GET['limit'] ?? 5;
            $categoryId = $_GET['category_id'] ?? null;
            
            $sql = 'SELECT 
                        p.id as product_id,
                        p.name as product_name,
                        p.image as product_image,
                        pc.id as container_id,
                        pc.volume,
                        pc.price,
                        (pc.price / pc.volume) as price_per_liter,
                        pc.stock_quantity
                    FROM products p
                    JOIN product_containers pc ON p.id = pc.product_id
                    WHERE p.is_active = 1 
                    AND pc.is_active = 1 
                    AND pc.stock_quantity > 0';
            
            $params = [];
            
            if ($categoryId) {
                $sql .= ' AND p.category_id = ?';
                $params[] = $categoryId;
            }
            
            $sql .= ' ORDER BY (pc.price / pc.volume) ASC, pc.stock_quantity DESC LIMIT ?';
            $params[] = (int)$limit;
            
            $recommendations = $this->productModel->db->getAll($sql, $params);
            
            $this->jsonResponse(['recommendations' => $recommendations]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => 'Помилка отримання рекомендацій'], 500);
        }
    }
    
    /**
     * Получение популярных комбинаций
     */
    public function getPopularCombinations() {
        try {
            $limit = $_GET['limit'] ?? 10;
            
            $orderItemModel = new OrderItem();
            $combinations = $orderItemModel->getBestValueCombinations($limit);
            
            $this->jsonResponse(['combinations' => $combinations]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => 'Помилка отримання популярних комбінацій'], 500);
        }
    }
    
    /**
     * Валидация данных заказа
     */
    public function validateOrderData() {
        try {
            $items = $_POST['items'] ?? [];
            $errors = [];
            $totalAmount = 0;
            
            if (empty($items)) {
                $errors[] = 'Замовлення не може бути порожнім';
            }
            
            foreach ($items as $index => $item) {
                $containerId = $item['container_id'] ?? null;
                $quantity = $item['quantity'] ?? 0;
                
                if (!$containerId) {
                    $errors[] = "Товар #{$index}: не вказано контейнер";
                    continue;
                }
                
                if ($quantity <= 0) {
                    $errors[] = "Товар #{$index}: некоректна кількість";
                    continue;
                }
                
                // Проверяем наличие на складе
                $container = $this->containerModel->getById($containerId);
                if (!$container) {
                    $errors[] = "Товар #{$index}: контейнер не знайдено";
                    continue;
                }
                
                if (!$container['is_active']) {
                    $errors[] = "Товар #{$index}: контейнер неактивний";
                    continue;
                }
                
                if ($container['stock_quantity'] < $quantity) {
                    $errors[] = "Товар #{$index}: недостатньо на складі (доступно: {$container['stock_quantity']})";
                    continue;
                }
                
                $totalAmount += $container['price'] * $quantity;
            }
            
            $response = [
                'valid' => empty($errors),
                'errors' => $errors,
                'total_amount' => $totalAmount,
                'items_count' => count($items)
            ];
            
            $this->jsonResponse($response);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => 'Помилка валідації даних'], 500);
        }
    }
    
    /**
     * Отправка JSON ответа
     */
    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

   

/**
 * Поиск товаров для автокомплита
 */
public function searchProducts() {
    try {
        $query = $_GET['q'] ?? $_GET['search'] ?? '';
        $limit = intval($_GET['limit'] ?? 10);
        
        if (strlen($query) < 2) {
            $this->jsonResponse(['products' => []]);
            return;
        }
        
        $sql = 'SELECT 
                    p.id,
                    p.name,
                    p.description,
                    p.image,
                    p.price,
                    p.stock_quantity,
                    c.name as category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.is_active = 1 
                AND (p.name LIKE ? OR p.description LIKE ?)
                ORDER BY 
                    CASE 
                        WHEN p.name LIKE ? THEN 1
                        WHEN p.name LIKE ? THEN 2
                        ELSE 3
                    END,
                    p.name
                LIMIT ?';
        
        $searchParam = '%' . $query . '%';
        $startParam = $query . '%';
        
        $products = $this->productModel->db->getAll($sql, [
            $searchParam, $searchParam, $startParam, $searchParam, $limit
        ]);
        
        // Получаем минимальную цену для каждого продукта
        foreach ($products as &$product) {
            $containers = $this->containerModel->getAvailableVolumes($product['id']);
            
            if (!empty($containers)) {
                $activePrices = array_column(
                    array_filter($containers, function($c) { 
                        return $c['is_active'] && $c['stock_quantity'] > 0; 
                    }), 
                    'price'
                );
                $product['min_price'] = !empty($activePrices) ? min($activePrices) : $product['price'];
                $product['has_containers'] = true;
                $product['containers_count'] = count($containers);
            } else {
                $product['min_price'] = $product['price'];
                $product['has_containers'] = false;
                $product['containers_count'] = 0;
            }
        }
        
        $this->jsonResponse(['products' => $products]);
        
    } catch (Exception $e) {
        error_log("Error in searchProducts: " . $e->getMessage());
        $this->jsonResponse(['error' => 'Помилка пошуку товарів'], 500);
    }
}

/**
 * Получение детальной информации о продукте с контейнерами
 */
public function getProductDetails() {
    try {
        $productId = $_GET['product_id'] ?? 0;
        
        if (!$productId) {
            $this->jsonResponse(['error' => 'ID товару не вказано'], 400);
            return;
        }
        
        // Получаем информацию о продукте
        $product = $this->productModel->getWithCategory($productId);
        
        if (!$product || !$product['is_active']) {
            $this->jsonResponse(['error' => 'Товар не знайдено або неактивний'], 404);
            return;
        }
        
        // Получаем все контейнеры для продукта
        $containers = $this->containerModel->getByProductId($productId);
        
        // Если контейнеров нет, создаем базовый
        if (empty($containers)) {
            $containers = [[
                'id' => 'default_' . $product['id'],
                'product_id' => $product['id'],
                'volume' => 1.0,
                'price' => $product['price'],
                'stock_quantity' => $product['stock_quantity'],
                'is_active' => 1,
                'price_per_liter' => $product['price']
            ]];
        } else {
            // Добавляем цену за литр
            foreach ($containers as &$container) {
                $container['price_per_liter'] = $container['volume'] > 0 ? 
                    $container['price'] / $container['volume'] : 0;
            }
        }
        
        // Сортируем контейнеры по объему
        usort($containers, function($a, $b) {
            return $a['volume'] <=> $b['volume'];
        });
        
        $response = [
            'product' => $product,
            'containers' => $containers,
            'available_containers' => array_filter($containers, function($c) {
                return $c['is_active'] && $c['stock_quantity'] > 0;
            })
        ];
        
        $this->jsonResponse($response);
        
    } catch (Exception $e) {
        error_log("Error in getProductDetails: " . $e->getMessage());
        $this->jsonResponse(['error' => 'Помилка отримання деталей товару'], 500);
    }
}

/**
 * Получение популярных товаров для рекомендаций
 */
public function getPopularProducts() {
    try {
        $limit = intval($_GET['limit'] ?? 6);
        $categoryId = $_GET['category_id'] ?? '';
        
        $sql = 'SELECT 
                    p.id,
                    p.name,
                    p.description,
                    p.image,
                    p.price,
                    p.stock_quantity,
                    c.name as category_name,
                    COALESCE(SUM(sa.quantity_sold), 0) as total_sold
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN sales_analytics sa ON p.id = sa.product_id AND sa.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                WHERE p.is_active = 1 AND p.stock_quantity > 0';
        
        $params = [];
        
        if (!empty($categoryId)) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        
        $sql .= ' GROUP BY p.id, p.name, p.description, p.image, p.price, p.stock_quantity, c.name
                  ORDER BY total_sold DESC, p.is_featured DESC, p.name
                  LIMIT ?';
        
        $params[] = $limit;
        
        $products = $this->productModel->db->getAll($sql, $params);
        
        // Получаем минимальную цену для каждого продукта
        foreach ($products as &$product) {
            $minPrice = $this->containerModel->getMinPrice($product['id']);
            $product['min_price'] = $minPrice > 0 ? $minPrice : $product['price'];
        }
        
        $this->jsonResponse(['products' => $products]);
        
    } catch (Exception $e) {
        error_log("Error in getPopularProducts: " . $e->getMessage());
        $this->jsonResponse(['error' => 'Помилка отримання популярних товарів'], 500);
    }
}

/**
 * Проверка доступности товара/контейнера
 */
public function checkProductAvailability() {
    try {
        $productId = $_GET['product_id'] ?? 0;
        $containerId = $_GET['container_id'] ?? '';
        $quantity = intval($_GET['quantity'] ?? 1);
        
        if (!$productId) {
            $this->jsonResponse(['error' => 'ID товару не вказано'], 400);
            return;
        }
        
        // Получаем информацию о продукте
        $product = $this->productModel->getById($productId);
        
        if (!$product || !$product['is_active']) {
            $this->jsonResponse([
                'available' => false,
                'reason' => 'Товар недоступний або неактивний'
            ]);
            return;
        }
        
        // Проверяем контейнер
        if (!empty($containerId) && $containerId !== 'default_' . $productId) {
            $container = $this->containerModel->getById($containerId);
            
            if (!$container || !$container['is_active']) {
                $this->jsonResponse([
                    'available' => false,
                    'reason' => 'Обраний об\'єм недоступний'
                ]);
                return;
            }
            
            if ($container['stock_quantity'] < $quantity) {
                $this->jsonResponse([
                    'available' => false,
                    'reason' => 'Недостатньо товару на складі',
                    'available_quantity' => $container['stock_quantity'],
                    'requested_quantity' => $quantity
                ]);
                return;
            }
            
            $this->jsonResponse([
                'available' => true,
                'stock_quantity' => $container['stock_quantity'],
                'price' => $container['price'],
                'volume' => $container['volume'],
                'price_per_liter' => $container['volume'] > 0 ? $container['price'] / $container['volume'] : 0
            ]);
            
        } else {
            // Проверяем базовый продукт
            if ($product['stock_quantity'] < $quantity) {
                $this->jsonResponse([
                    'available' => false,
                    'reason' => 'Недостатньо товару на складі',
                    'available_quantity' => $product['stock_quantity'],
                    'requested_quantity' => $quantity
                ]);
                return;
            }
            
            $this->jsonResponse([
                'available' => true,
                'stock_quantity' => $product['stock_quantity'],
                'price' => $product['price'],
                'volume' => 1,
                'price_per_liter' => $product['price']
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Error in checkProductAvailability: " . $e->getMessage());
        $this->jsonResponse(['error' => 'Помилка перевірки доступності'], 500);
    }
}

/**
 * Получение категорий с количеством товаров
 */
public function getCategories() {
    try {
        $sql = 'SELECT 
                    c.id,
                    c.name,
                    c.description,
                    c.image,
                    COUNT(p.id) as products_count
                FROM categories c
                LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
                GROUP BY c.id, c.name, c.description, c.image
                HAVING products_count > 0
                ORDER BY c.name';
        
        $categories = $this->productModel->db->getAll($sql);
        
        $this->jsonResponse(['categories' => $categories]);
        
    } catch (Exception $e) {
        error_log("Error in getCategories: " . $e->getMessage());
        $this->jsonResponse(['error' => 'Помилка отримання категорій'], 500);
    }
}

/**
 * Валидация корзины перед оформлением заказа
 */
public function validateCart() {
    try {
        $cartItems = $_POST['cart_items'] ?? [];
        
        if (empty($cartItems)) {
            $this->jsonResponse([
                'valid' => false,
                'errors' => ['Кошик порожній']
            ]);
            return;
        }
        
        $errors = [];
        $totalAmount = 0;
        $totalItems = 0;
        
        foreach ($cartItems as $itemData) {
            $item = is_string($itemData) ? json_decode($itemData, true) : $itemData;
            
            if (!$item || !isset($item['product_id'])) {
                $errors[] = 'Некоректні дані товару';
                continue;
            }
            
            $productId = $item['product_id'];
            $containerId = $item['container_id'] ?? null;
            $quantity = intval($item['quantity'] ?? 1);
            
            // Проверяем продукт
            $product = $this->productModel->getById($productId);
            if (!$product || !$product['is_active']) {
                $errors[] = "Товар недоступний";
                continue;
            }
            
            // Проверяем контейнер или базовый продукт
            if ($containerId && !str_starts_with($containerId, 'default_')) {
                $container = $this->containerModel->getById($containerId);
                
                if (!$container || !$container['is_active'] || $container['product_id'] != $productId) {
                    $errors[] = "Об'єм для товару '{$product['name']}' недоступний";
                    continue;
                }
                
                if ($container['stock_quantity'] < $quantity) {
                    $errors[] = "Недостатньо '{$product['name']}' на складі (доступно: {$container['stock_quantity']})";
                    continue;
                }
                
                $totalAmount += $container['price'] * $quantity;
                
            } else {
                if ($product['stock_quantity'] < $quantity) {
                    $errors[] = "Недостатньо '{$product['name']}' на складі (доступно: {$product['stock_quantity']})";
                    continue;
                }
                
                $totalAmount += $product['price'] * $quantity;
            }
            
            $totalItems += $quantity;
        }
        
        $this->jsonResponse([
            'valid' => empty($errors),
            'errors' => $errors,
            'total_amount' => round($totalAmount, 2),
            'total_items' => $totalItems,
            'items_count' => count($cartItems)
        ]);
        
    } catch (Exception $e) {
        error_log("Error in validateCart: " . $e->getMessage());
        $this->jsonResponse(['error' => 'Помилка валідації кошика'], 500);
    }
}

/**
 * Получение информации о скидках и акциях
 */
public function getPromotions() {
    try {
        $sql = 'SELECT 
                    pr.id,
                    pr.name,
                    pr.description,
                    pr.discount_type,
                    pr.discount_value,
                    pr.start_date,
                    pr.end_date
                FROM promotions pr
                WHERE pr.is_active = 1 
                AND pr.start_date <= CURDATE() 
                AND pr.end_date >= CURDATE()
                ORDER BY pr.discount_value DESC';
        
        $promotions = $this->productModel->db->getAll($sql);
        
        // Для каждой акции получаем товары
        foreach ($promotions as &$promotion) {
            $productsSql = 'SELECT p.id, p.name, p.image
                           FROM promotion_products pp
                           JOIN products p ON pp.product_id = p.id
                           WHERE pp.promotion_id = ? AND p.is_active = 1
                           LIMIT 5';
            
            $promotion['products'] = $this->productModel->db->getAll($productsSql, [$promotion['id']]);
        }
        
        $this->jsonResponse(['promotions' => $promotions]);
        
    } catch (Exception $e) {
        error_log("Error in getPromotions: " . $e->getMessage());
        $this->jsonResponse(['error' => 'Помилка отримання акцій'], 500);
    }
}

/**
 * Получение статистики для дашборда API
 */
public function getDashboardStats() {
    try {
        // Только для авторизованных пользователей
        if (!is_logged_in()) {
            $this->jsonResponse(['error' => 'Необхідна авторизація'], 401);
            return;
        }
        
        $stats = [];
        
        // Общая статистика продуктов
        $stats['products'] = [
            'total' => $this->productModel->count('is_active = 1'),
            'low_stock' => count($this->productModel->getLowStockProducts(10)),
            'categories' => $this->productModel->db->getValue('SELECT COUNT(DISTINCT category_id) FROM products WHERE is_active = 1')
        ];
        
        // Статистика контейнеров
        $stats['containers'] = [
            'total' => $this->containerModel->db->getValue('SELECT COUNT(*) FROM product_containers WHERE is_active = 1'),
            'active_with_stock' => $this->containerModel->db->getValue('SELECT COUNT(*) FROM product_containers WHERE is_active = 1 AND stock_quantity > 0')
        ];
        
        // Для клиентов - их статистика заказов
        if (has_role('customer')) {
            $customerId = get_current_user_id();
            $orderModel = new Order();
            
            $stats['customer'] = [
                'total_orders' => $orderModel->db->getValue('SELECT COUNT(*) FROM orders WHERE customer_id = ?', [$customerId]),
                'total_spent' => $orderModel->db->getValue('SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE customer_id = ? AND status != "cancelled"', [$customerId]),
                'pending_orders' => $orderModel->db->getValue('SELECT COUNT(*) FROM orders WHERE customer_id = ? AND status = "pending"', [$customerId])
            ];
        }
        
        $this->jsonResponse(['stats' => $stats]);
        
    } catch (Exception $e) {
        error_log("Error in getDashboardStats: " . $e->getMessage());
        $this->jsonResponse(['error' => 'Помилка отримання статистики'], 500);
    }
}

/**
 * Проверка совместимости товаров в корзине
 */
public function checkCartCompatibility() {
    try {
        $cartItems = $_POST['cart_items'] ?? [];
        
        if (empty($cartItems)) {
            $this->jsonResponse(['compatible' => true, 'warnings' => []]);
            return;
        }
        
        $warnings = [];
        $categories = [];
        $totalVolume = 0;
        $totalWeight = 0; // Если нужно
        
        foreach ($cartItems as $itemData) {
            $item = is_string($itemData) ? json_decode($itemData, true) : $itemData;
            
            if (!$item || !isset($item['product_id'])) {
                continue;
            }
            
            $product = $this->productModel->getWithCategory($item['product_id']);
            if (!$product) {
                continue;
            }
            
            // Собираем категории
            if ($product['category_id']) {
                $categories[$product['category_id']] = $product['category_name'];
            }
            
            // Подсчитываем общий объем
            $volume = floatval($item['volume'] ?? 1);
            $quantity = intval($item['quantity'] ?? 1);
            $totalVolume += $volume * $quantity;
        }
        
        // Проверяем различные условия
        if (count($categories) > 3) {
            $warnings[] = 'У вашому кошику товари з багатьох категорій. Можливо, варто розділити на кілька замовлень.';
        }
        
        if ($totalVolume > 50) {
            $warnings[] = 'Загальний об\'єм замовлення перевищує 50 літрів. Врахуйте додаткові витрати на доставку.';
        }
        
        $this->jsonResponse([
            'compatible' => true,
            'warnings' => $warnings,
            'total_volume' => $totalVolume,
            'categories_count' => count($categories),
            'categories' => array_values($categories)
        ]);
        
    } catch (Exception $e) {
        error_log("Error in checkCartCompatibility: " . $e->getMessage());
        $this->jsonResponse(['error' => 'Помилка перевірки сумісності'], 500);
    }
}
}