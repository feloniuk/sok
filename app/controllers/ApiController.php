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
}