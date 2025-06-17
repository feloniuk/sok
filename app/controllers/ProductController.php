<?php
// app/controllers/ProductController.php - Контролер для роботи з продуктами

class ProductController extends BaseController {
    private $productModel;
    private $categoryModel;

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
    
    public function __construct() {
        parent::__construct();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }
    
    /**
     * Відображення списку продуктів
     */
    public function index() {        
        // Отримання параметрів фільтрації та пагінації
        $page = intval($this->input('page', 1));
        
        $filters = [
            'category_id' => $this->input('category_id'),
            'keyword' => $this->input('keyword'),
            'min_price' => $this->input('min_price'),
            'max_price' => $this->input('max_price'),
            'sort' => $this->input('sort'),
            'is_featured' => $this->input('is_featured'),
            'is_active' => is_logged_in() && has_role(['admin', 'warehouse_manager']) ? null : 1 // Для звичайних користувачів показуємо тільки активні товари
        ];
        
        // Отримання продуктів з пагінацією та фільтрацією
        $productsData = $this->productModel->getFiltered($page, ITEMS_PER_PAGE, $filters);
        
        // Отримання категорій для фільтра
        $categories = $this->categoryModel->getAll();
        
        // Передача даних у представлення
        $this->data['products'] = $productsData['items'];
        $this->data['pagination'] = [
            'current_page' => $productsData['current_page'],
            'per_page' => $productsData['per_page'],
            'total_items' => $productsData['total_items'],
            'total_pages' => $productsData['total_pages']
        ];
        $this->data['categories'] = $categories;
        
        $this->view('products/index');
    }
    
    /**
     * Отримання даних про продукт у форматі JSON
     * 
     * @param int $id
     * @return void
     */
    public function getProductJson($id = null) {
        // Отримання ID з GET-параметра, якщо не передано як аргумент
        if ($id === null) {
            $id = $this->input('id');
        }
        
        if (!$id || !is_numeric($id)) {
            $this->json(['error' => 'Invalid product ID'], 400);
            return;
        }
        
        // Отримання продукту
        $product = $this->productModel->getById($id);
        
        if (!$product || !$product['is_active'] || $product['stock_quantity'] <= 0) {
            $this->json(['error' => 'Product not found or unavailable'], 404);
            return;
        }
        
        // Повернення даних про продукт
        $this->json($product);
    }

    // Исправленный метод details в ProductController.php
    
    /**
     * Відображення деталей продукту
     *
     * @param int $id
     */
    public function details($id, $data = []) {
        // Отримання даних продукту з інформацією про категорію
        $product = $this->productModel->getWithCategory($id);
        
        if (!$product) {
            $this->setFlash('error', 'Продукт не знайдено.');
            $this->redirect('products');
            return;
        }
        
        // Якщо користувач не є адміністратором або менеджером складу, перевіряємо, чи є продукт активним
        if (!(is_logged_in() && has_role(['admin', 'warehouse_manager'])) && !$product['is_active']) {
            $this->setFlash('error', 'Продукт не знайдено або він неактивний.');
            $this->redirect('products');
            return;
        }
        
        // Отримання контейнерів (об'ємів тари) для продукту
        $productContainerModel = new ProductContainer();
        $containers = $productContainerModel->getByProductId($id);
        
        // Якщо контейнерів немає, створюємо базовий контейнер з даними продукту
        if (empty($containers)) {
            $containers = [[
                'id' => 'default_' . $product['id'],
                'product_id' => $product['id'],
                'volume' => 1, // Базовий об'єм 1 літр
                'price' => $product['price'],
                'stock_quantity' => $product['stock_quantity'],
                'is_active' => $product['is_active']
            ]];
        }
        
        // Отримання пов'язаних продуктів (з тієї ж категорії)
        $relatedProducts = [];
        if ($product['category_id']) {
            $relatedSql = 'SELECT * FROM products 
                           WHERE category_id = ? AND id != ? AND is_active = 1 
                           ORDER BY RAND() LIMIT 4';
            $relatedProducts = $this->db->getAll($relatedSql, [$product['category_id'], $id]);
            
            // Для кожного пов'язаного продукту отримуємо мінімальну ціну
            foreach ($relatedProducts as &$relatedProduct) {
                $relatedContainers = $productContainerModel->getByProductId($relatedProduct['id']);
                if (!empty($relatedContainers)) {
                    $activePrices = array_column(
                        array_filter($relatedContainers, function($c) { 
                            return $c['is_active'] && $c['stock_quantity'] > 0; 
                        }), 
                        'price'
                    );
                    $relatedProduct['min_price'] = !empty($activePrices) ? min($activePrices) : $relatedProduct['price'];
                } else {
                    $relatedProduct['min_price'] = $relatedProduct['price'];
                }
            }
        }
        
        // Передача даних у представлення
        $this->data['product'] = $product;
        $this->data['containers'] = $containers;
        $this->data['relatedProducts'] = $relatedProducts;
        
        $this->view('products/view');
    }
    
    /**
     * Відображення форми створення продукту
     */
    public function create() {
        // Перевірка прав доступу
        if (!has_role(['admin', 'warehouse_manager'])) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки.');
            $this->redirect('products');
            return;
        }
        
        // Отримання категорій для випадаючого списку
        $categories = $this->categoryModel->getAll();
        
        // Передача даних у представлення
        $this->data['categories'] = $categories;
        $this->data['containers'] = []; // Пустий масив для нових продуктів
        
        $this->view('products/form');
    }
    
    /**
     * Обробка форми створення продукту
     */
    public function store() {
        // Перевірка прав доступу
        if (!has_role(['admin', 'warehouse_manager'])) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки.');
            $this->redirect('products');
            return;
        }
        
        // Перевірка методу запиту
        if (!$this->isPost()) {
            $this->redirect('products/create');
            return;
        }
        
        // Перевірка CSRF-токена
        $this->validateCsrfToken();
        
        // Отримання даних із форми
        $name = $this->input('name');
        $category_id = $this->input('category_id') ?: null;
        $description = $this->input('description');
        $is_featured = $this->input('is_featured') ? 1 : 0;
        $is_active = $this->input('is_active') ? 1 : 0;
        
        // Отримання даних про контейнери
        $containersData = $this->input('containers', []);
        
        // Валідація даних
        $errors = [];
        
        if (empty($name)) {
            $errors['name'] = 'Введіть назву продукту';
        }
        
        // Валідація контейнерів
        if (empty($containersData)) {
            $errors['containers'] = 'Додайте хоча б один об\'єм тари';
        } else {
            $volumes = [];
            foreach ($containersData as $container) {
                // Перевірка унікальності об'ємів
                if (in_array($container['volume'], $volumes)) {
                    $errors['containers'] = 'Об\'єми тари повинні бути унікальними';
                    break;
                }
                $volumes[] = $container['volume'];
                
                // Перевірка обов'язкових полів
                if (empty($container['price']) || $container['price'] <= 0) {
                    $errors['containers'] = 'Всі ціни повинні бути більше 0';
                    break;
                }
            }
        }
        
        // Обробка завантаженого зображення
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = upload_file('image', 'products');
            
            if ($image === null) {
                $errors['image'] = 'Помилка при завантаженні зображення. Перевірте формат і розмір файлу.';
            }
        }
        
        // Якщо є помилки, повертаємо користувача до форми
        if (!empty($errors)) {
            set_form_errors($errors);
            $this->redirect('products/create');
            return;
        }
        
        try {
            // Початок транзакції
            $this->db->beginTransaction();
            
            // Розрахунок середньої ціни та загальної кількості
            $totalQuantity = 0;
            $weightedPrice = 0;
            
            foreach ($containersData as $container) {
                $quantity = intval($container['stock_quantity'] ?? 0);
                $price = floatval($container['price']);
                
                $totalQuantity += $quantity;
                $weightedPrice += $price * $quantity;
            }
            
            $averagePrice = $totalQuantity > 0 ? $weightedPrice / $totalQuantity : floatval($containersData[0]['price']);
            
            // Створення нового продукту
            $productData = [
                'name' => $name,
                'category_id' => $category_id,
                'description' => $description,
                'price' => $averagePrice, // Середня ціна
                'stock_quantity' => $totalQuantity, // Загальна кількість
                'image' => $image,
                'is_featured' => $is_featured,
                'is_active' => $is_active
            ];
            
            $productId = $this->productModel->create($productData);
            
            if (!$productId) {
                throw new Exception('Помилка при створенні продукту');
            }
            
            // Створення контейнерів
            $productContainerModel = new ProductContainer();
            
            foreach ($containersData as $container) {
                $containerData = [
                    'product_id' => $productId,
                    'volume' => floatval($container['volume']),
                    'price' => floatval($container['price']),
                    'stock_quantity' => intval($container['stock_quantity'] ?? 0),
                    'is_active' => isset($container['is_active']) ? 1 : 0
                ];
                
                $containerId = $productContainerModel->create($containerData);
                
                if (!$containerId) {
                    throw new Exception('Помилка при створенні контейнера');
                }
                
                // Запис руху товарів, якщо є початкова кількість
                if ($containerData['stock_quantity'] > 0) {
                    $inventoryMovementModel = new InventoryMovement();
                    $movementData = [
                        'product_id' => $productId,
                        'warehouse_id' => 1, // Використовуємо основний склад
                        'quantity' => $containerData['stock_quantity'],
                        'movement_type' => 'incoming',
                        'reference_type' => 'product_create',
                        'notes' => 'Початкове надходження при створенні товару (об\'єм: ' . $containerData['volume'] . 'л)',
                        'created_by' => get_current_user_id(),
                        'skip_stock_update' => true // Не оновлювати основну таблицу продуктів
                    ];
                    
                    $inventoryMovementModel->create($movementData);
                }
            }
            
            // Завершення транзакції
            $this->db->commit();
            
            $this->setFlash('success', 'Продукт та об\'єми тари успішно створено.');
            $this->redirect('products/view/' . $productId);
            
        } catch (Exception $e) {
            // Відкат транзакції у випадку помилки
            $this->db->rollBack();
            
            if (DEBUG_MODE) {
                error_log("Error in store: " . $e->getMessage());
                error_log($e->getTraceAsString());
            }
            
            $this->setFlash('error', 'Помилка при створенні продукту: ' . $e->getMessage());
            $this->redirect('products/create');
        }
    }
    
    /**
     * Відображення форми редагування продукту
     *
     * @param int $id
     */
    public function edit($id) {
        // Перевірка прав доступу
        if (!has_role(['admin', 'warehouse_manager'])) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки.');
            $this->redirect('products');
            return;
        }
        
        // Отримання даних продукту
        $product = $this->productModel->getById($id);
        
        if (!$product) {
            $this->setFlash('error', 'Продукт не знайдено.');
            $this->redirect('products');
            return;
        }
        
        // Отримання категорій для випадаючого списку
        $categories = $this->categoryModel->getAll();
        
        // Отримання контейнерів (об'ємів тари) для продукту
        $productContainerModel = new ProductContainer();
        $containers = $productContainerModel->getByProductId($id);
        
        // Якщо контейнерів немає, створюємо базовий
        if (empty($containers)) {
            $containers = [[
                'id' => null,
                'product_id' => $id,
                'volume' => 1,
                'price' => $product['price'],
                'stock_quantity' => $product['stock_quantity'],
                'is_active' => 1
            ]];
        }
        
        // Передача даних у представлення
        $this->data['product'] = $product;
        $this->data['categories'] = $categories;
        $this->data['containers'] = $containers;
        
        $this->view('products/form');
    }
    
    /**
     * Обробка форми редагування продукту
     *
     * @param int $id
     */
    public function update($id) {
        // Включаем отладку
        error_log("ProductController::update called for ID: " . $id);
        
        // Проверка прав доступа
        if (!has_role(['admin', 'warehouse_manager'])) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки.');
            $this->redirect('products');
            return;
        }
        
        // Проверка метода запроса
        if (!$this->isPost()) {
            error_log("Not POST request, redirecting");
            $this->redirect('products/edit/' . $id);
            return;
        }
        
        // Проверка CSRF-токена
        try {
            $this->validateCsrfToken();
            error_log("CSRF token validated");
        } catch (Exception $e) {
            error_log("CSRF validation failed: " . $e->getMessage());
            $this->setFlash('error', 'CSRF token validation failed');
            $this->redirect('products/edit/' . $id);
            return;
        }
        
        // Получение продукта
        $product = $this->productModel->getById($id);
        
        if (!$product) {
            error_log("Product not found with ID: " . $id);
            $this->setFlash('error', 'Продукт не знайдено.');
            $this->redirect('products');
            return;
        }
        
        // Получение данных из формы
        $name = $this->input('name');
        $category_id = $this->input('category_id') ?: null;
        $description = $this->input('description');
        $is_featured = $this->input('is_featured') ? 1 : 0;
        $is_active = $this->input('is_active') ? 1 : 0;
        
        // Получение данных о контейнерах
        $containersData = $this->input('containers', []);
        
        error_log("Form data received:");
        error_log("Name: " . $name);
        error_log("Containers data: " . print_r($containersData, true));
        
        // Валидация данных
        $errors = [];
        
        if (empty($name)) {
            $errors['name'] = 'Введіть назву продукту';
        }
        
        // Если нет данных о контейнерах, создаем базовый контейнер
        if (empty($containersData)) {
            error_log("No containers data, creating default container");
            $containersData = [[
                'volume' => 1,
                'price' => $this->input('price', $product['price']),
                'stock_quantity' => $this->input('stock_quantity', $product['stock_quantity']),
                'is_active' => 1
            ]];
        }
        
        // Валидация контейнеров
        $volumes = [];
        foreach ($containersData as $index => $container) {
            error_log("Processing container " . $index . ": " . print_r($container, true));
            
            // Проверка унікальності об'ємів
            if (in_array($container['volume'], $volumes)) {
                $errors['containers'] = 'Об\'єми тари повинні бути унікальними';
                break;
            }
            $volumes[] = $container['volume'];
            
            // Проверка обов'язкових полів
            if (empty($container['price']) || $container['price'] <= 0) {
                $errors['containers'] = 'Всі ціни повинні бути більше 0';
                break;
            }
        }
        
        // Обработка загруженного изображения
        $image = $product['image'];
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $newImage = upload_file('image', 'products');
            
            if ($newImage === null) {
                $errors['image'] = 'Помилка при завантаженні зображення';
            } else {
                $image = $newImage;
            }
        }
        
        // Если есть ошибки, возвращаемся к форме
        if (!empty($errors)) {
            error_log("Validation errors: " . print_r($errors, true));
            set_form_errors($errors);
            $this->redirect('products/edit/' . $id);
            return;
        }
        
        try {
            error_log("Starting database transaction");
            
            // Проверяем, есть ли уже активная транзакция
            $inTransaction = false;
            try {
                $this->db->beginTransaction();
                $inTransaction = true;
            } catch (Exception $e) {
                error_log("Transaction already started or error: " . $e->getMessage());
            }
            
            // Расчет средней цены и общей количества
            $totalQuantity = 0;
            $weightedPrice = 0;
            
            foreach ($containersData as $container) {
                $quantity = intval($container['stock_quantity'] ?? 0);
                $price = floatval($container['price']);
                
                $totalQuantity += $quantity;
                $weightedPrice += $price * $quantity;
            }
            
            $averagePrice = $totalQuantity > 0 ? $weightedPrice / $totalQuantity : floatval($containersData[0]['price']);
            
            error_log("Calculated averagePrice: " . $averagePrice . ", totalQuantity: " . $totalQuantity);
            
            // Обновление основной информации о продукте
            $productData = [
                'name' => $name,
                'category_id' => $category_id,
                'description' => $description,
                'price' => $averagePrice,
                'stock_quantity' => $totalQuantity,
                'image' => $image,
                'is_featured' => $is_featured,
                'is_active' => $is_active
            ];
            
            error_log("Updating product with data: " . print_r($productData, true));
            
            $result = $this->productModel->update($id, $productData);
            
            if (!$result) {
                throw new Exception('Помилка при оновленні продукту');
            }
            
            error_log("Product updated successfully");
            
            // Обработка контейнеров
            $productContainerModel = new ProductContainer();
            
            // Получаем существующие контейнеры
            $existingContainers = $productContainerModel->getByProductId($id);
            $existingIds = array_column($existingContainers, 'id');
            $updatedIds = [];
            
            error_log("Existing containers: " . print_r($existingContainers, true));
            
            foreach ($containersData as $container) {
                $containerData = [
                    'product_id' => $id,
                    'volume' => floatval($container['volume']),
                    'price' => floatval($container['price']),
                    'stock_quantity' => intval($container['stock_quantity'] ?? 0),
                    'is_active' => isset($container['is_active']) ? 1 : 0
                ];
                
                error_log("Processing container data: " . print_r($containerData, true));
                
                if (!empty($container['id']) && is_numeric($container['id'])) {
                    // Обновление существующего контейнера
                    $containerId = intval($container['id']);
                    error_log("Updating existing container ID: " . $containerId);
                    $productContainerModel->update($containerId, $containerData);
                    $updatedIds[] = $containerId;
                } else {
                    // Создание нового контейнера
                    error_log("Creating new container");
                    $newId = $productContainerModel->create($containerData);
                    if ($newId) {
                        $updatedIds[] = $newId;
                        error_log("Created new container with ID: " . $newId);
                    }
                }
            }
            
            // Удаление контейнеров, которые не были обновлены
            $toDelete = array_diff($existingIds, $updatedIds);
            error_log("Containers to delete: " . print_r($toDelete, true));
            
            foreach ($toDelete as $deleteId) {
                error_log("Deleting container ID: " . $deleteId);
                $productContainerModel->delete($deleteId);
            }
            
            // Завершение транзакции
            if ($inTransaction) {
                $this->db->commit();
                error_log("Transaction committed");
            }
            
            $this->setFlash('success', 'Продукт та об\'єми тари успішно оновлено.');
            $this->redirect('products/view/' . $id);
            
        } catch (Exception $e) {
            // Откат транзакции в случае ошибки
            if ($inTransaction) {
                $this->db->rollBack();
                error_log("Transaction rolled back");
            }
            
            error_log("Error in update: " . $e->getMessage());
            error_log($e->getTraceAsString());
            
            $this->setFlash('error', 'Помилка при оновленні продукту: ' . $e->getMessage());
            $this->redirect('products/edit/' . $id);
        }
    }
    
    /**
     * Альтернативный простой метод обновления (без контейнеров)
     */
    public function updateSimple($id) {
        error_log("ProductController::updateSimple called for ID: " . $id);
        
        // Проверка прав доступа
        if (!has_role(['admin', 'warehouse_manager'])) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки.');
            $this->redirect('products');
            return;
        }
        
        // Проверка метода запроса
        if (!$this->isPost()) {
            $this->redirect('products/edit/' . $id);
            return;
        }
        
        // Проверка CSRF-токена
        $this->validateCsrfToken();
        
        // Получение продукта
        $product = $this->productModel->getById($id);
        
        if (!$product) {
            $this->setFlash('error', 'Продукт не знайдено.');
            $this->redirect('products');
            return;
        }
        
        // Получение данных из формы
        $name = $this->input('name');
        $category_id = $this->input('category_id') ?: null;
        $description = $this->input('description');
        $price = $this->input('price') ?: $product['price'];
        $stock_quantity = $this->input('stock_quantity') ?: $product['stock_quantity'];
        $is_featured = $this->input('is_featured') ? 1 : 0;
        $is_active = $this->input('is_active') ? 1 : 0;
        
        // Валидация данных
        $errors = [];
        
        if (empty($name)) {
            $errors['name'] = 'Введіть назву продукту';
        }
        
        if (empty($price) || !is_numeric($price) || $price <= 0) {
            $errors['price'] = 'Введіть коректну ціну';
        }
        
        if (!is_numeric($stock_quantity) || $stock_quantity < 0) {
            $errors['stock_quantity'] = 'Кількість на складі повинна бути невід\'ємним числом';
        }
        
        // Обработка загруженного изображения
        $image = $product['image'];
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $newImage = upload_file('image', 'products');
            
            if ($newImage === null) {
                $errors['image'] = 'Помилка при завантаженні зображення';
            } else {
                $image = $newImage;
            }
        }
        
        // Если есть ошибки, возвращаемся к форме
        if (!empty($errors)) {
            set_form_errors($errors);
            $this->redirect('products/edit/' . $id);
            return;
        }
        
        // Проверка изменения количества товара
        $stockDifference = $stock_quantity - $product['stock_quantity'];
        
        // Обновление продукта
        $productData = [
            'name' => $name,
            'category_id' => $category_id,
            'description' => $description,
            'price' => $price,
            'stock_quantity' => $stock_quantity,
            'image' => $image,
            'is_featured' => $is_featured,
            'is_active' => $is_active
        ];
        
        $result = $this->productModel->update($id, $productData);
        
        if ($result) {
            // Запись движения товаров, если изменилось количество
            if ($stockDifference != 0) {
                $inventoryMovementModel = new InventoryMovement();
                $movementData = [
                    'product_id' => $id,
                    'warehouse_id' => 1,
                    'quantity' => $stockDifference,
                    'movement_type' => $stockDifference > 0 ? 'incoming' : 'outgoing',
                    'reference_type' => 'product_update',
                    'notes' => 'Коригування кількості при редагуванні товару',
                    'created_by' => get_current_user_id()
                ];
                
                $inventoryMovementModel->create($movementData);
            }
            
            $this->setFlash('success', 'Продукт успішно оновлено.');
            $this->redirect('products/view/' . $id);
        } else {
            $this->setFlash('error', 'Помилка при оновленні продукту.');
            $this->redirect('products/edit/' . $id);
        }
    }
    
    /**
     * Видалення продукту
     *
     * @param int $id
     */
    public function delete($id) {
        // Перевірка прав доступу
        if (!has_role('admin')) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки.');
            $this->redirect('products');
            return;
        }
        
        // Отримання продукту
        $product = $this->productModel->getById($id);
        
        if (!$product) {
            $this->setFlash('error', 'Продукт не знайдено.');
            $this->redirect('products');
            return;
        }
        
        // Видалення продукту
        $result = $this->productModel->delete($id);
        
        if ($result) {
            $this->setFlash('success', 'Продукт успішно видалено.');
        } else {
            $this->setFlash('error', 'Помилка при видаленні продукту.');
        }
        
        $this->redirect('products');
    }
    
    /**
     * Пошук продуктів (AJAX)
     */
    public function search() {
        // Отримання запиту
        $keyword = $this->input('term', '');
        
        if (empty($keyword)) {
            $this->json([]);
            return;
        }
        
        // Пошук продуктів
        $products = $this->productModel->search($keyword);
        
        // Формування результатів для autocomplete
        $results = [];
        
        foreach ($products as $product) {
            $results[] = [
                'id' => $product['id'],
                'label' => $product['name'] . ' (' . number_format($product['price'], 2) . ' грн.)',
                'value' => $product['name'],
                'price' => $product['price'],
                'stock' => $product['stock_quantity'],
                'image' => $product['image'] ? upload_url($product['image']) : asset_url('images/no-image.jpg')
            ];
        }
        
        $this->json($results);
    }
}