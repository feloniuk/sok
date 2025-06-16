<?php
// app/controllers/ApiController.php - API контролер для AJAX запитів

class ApiController extends BaseController {
    private $productModel;
    private $productContainerModel;
    
    public function __construct() {
        parent::__construct();
        $this->productModel = new Product();
        $this->productContainerModel = new ProductContainer();
    }
    
    /**
     * Отримання продуктів з контейнерами для створення замовлення
     */
    public function productsWithContainers() {
        try {
            // Отримуємо всі активні продукти
            $products = $this->productModel->getAllActive();
            
            $productsWithContainers = [];
            
            foreach ($products as $product) {
                // Отримуємо контейнери для кожного продукту
                $containers = $this->productContainerModel->getByProductId($product['id']);
                
                // Фільтруємо тільки активні контейнери з запасами
                $activeContainers = array_filter($containers, function($container) {
                    return $container['is_active'] && $container['stock_quantity'] > 0;
                });
                
                // Додаємо продукт тільки якщо є доступні контейнери
                if (!empty($activeContainers)) {
                    $productsWithContainers[] = [
                        'id' => $product['id'],
                        'name' => $product['name'],
                        'description' => $product['description'],
                        'image' => $product['image'] ? upload_url($product['image']) : asset_url('images/no-image.jpg'),
                        'category_name' => $product['category_name'] ?? '',
                        'containers' => array_values($activeContainers),
                        'min_price' => min(array_column($activeContainers, 'price'))
                    ];
                }
            }
            
            $this->json([
                'success' => true,
                'products' => $productsWithContainers
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'error' => 'Помилка при отриманні продуктів: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Отримання контейнерів для конкретного продукту
     */
    public function productContainers($productId) {
        try {
            if (!$productId || !is_numeric($productId)) {
                $this->json([
                    'success' => false,
                    'error' => 'Невірний ID продукту'
                ], 400);
                return;
            }
            
            // Перевіряємо чи існує продукт
            $product = $this->productModel->getById($productId);
            if (!$product || !$product['is_active']) {
                $this->json([
                    'success' => false,
                    'error' => 'Продукт не знайдено або неактивний'
                ], 404);
                return;
            }
            
            // Отримуємо контейнери
            $containers = $this->productContainerModel->getByProductId($productId);
            
            // Фільтруємо активні контейнери
            $activeContainers = array_filter($containers, function($container) {
                return $container['is_active'];
            });
            
            $this->json([
                'success' => true,
                'product' => [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'image' => $product['image'] ? upload_url($product['image']) : asset_url('images/no-image.jpg')
                ],
                'containers' => array_values($activeContainers)
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'error' => 'Помилка при отриманні контейнерів: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Перевірка наявності товару
     */
    public function checkProductAvailability() {
        try {
            $productId = $this->input('product_id');
            $containerId = $this->input('container_id');
            $quantity = intval($this->input('quantity', 1));
            
            if (!$productId || !$containerId) {
                $this->json([
                    'success' => false,
                    'error' => 'Не вказано ID продукту або контейнера'
                ], 400);
                return;
            }
            
            // Перевіряємо продукт
            $product = $this->productModel->getById($productId);
            if (!$product || !$product['is_active']) {
                $this->json([
                    'success' => false,
                    'error' => 'Продукт недоступний'
                ], 404);
                return;
            }
            
            // Перевіряємо контейнер
            $container = $this->productContainerModel->getById($containerId);
            if (!$container || !$container['is_active'] || $container['product_id'] != $productId) {
                $this->json([
                    'success' => false,
                    'error' => 'Контейнер недоступний'
                ], 404);
                return;
            }
            
            // Перевіряємо наявність
            if ($container['stock_quantity'] < $quantity) {
                $this->json([
                    'success' => false,
                    'error' => 'Недостатньо товару на складі',
                    'available' => $container['stock_quantity']
                ], 400);
                return;
            }
            
            $this->json([
                'success' => true,
                'available' => true,
                'stock' => $container['stock_quantity'],
                'price' => $container['price'],
                'volume' => $container['volume']
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'error' => 'Помилка при перевірці наявності: ' . $e->getMessage()
            ], 500);
        }
    }
}