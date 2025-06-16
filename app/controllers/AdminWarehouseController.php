<?php
// app/controllers/AdminWarehouseController.php

class AdminWarehouseController extends BaseController {
    private $warehouseModel;
    private $productModel;
    private $inventoryMovementModel;
    
    public function __construct() {
        parent::__construct();
        $this->warehouseModel = new Warehouse();
        $this->productModel = new Product();
        $this->inventoryMovementModel = new InventoryMovement();
        
        // Проверка прав доступа
        if (!has_role(['admin', 'warehouse_manager'])) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки');
            $this->redirect('dashboard');
            return;
        }
    }
    
    /**
     * Главная страница управления складом
     */
    public function index() {
        // Получение статистики по складу
        $warehouseStats = $this->warehouseModel->getStats();
        
        // Передача данных в представление
        $this->data['warehouseStats'] = $warehouseStats;
        $this->data['title'] = 'Управління складом';
        
        $this->view('admin/warehouse/index');
    }
    
    /**
     * Страница инвентаризации
     */
    public function inventory() {
        // Получение параметров фильтрации и пагинации
        $page = intval($this->input('page', 1));
        
        $filters = [
            'category_id' => $this->input('category_id'),
            'keyword' => $this->input('keyword'),
            'min_stock' => $this->input('min_stock'),
            'max_stock' => $this->input('max_stock'),
            'sort' => $this->input('sort', 'name_asc')
        ];
        
        // Получение продуктов с пагинацией и фильтрацией
        $productsData = $this->productModel->getFiltered($page, ITEMS_PER_PAGE, $filters);
        
        // Получение категорий для фильтра
        $categoryModel = new Category();
        $categories = $categoryModel->getAll();
        
        // Получение складов
        $warehouses = $this->warehouseModel->getAll();
        
        // Передача данных в представление
        $this->data['products'] = $productsData['items'];
        $this->data['pagination'] = [
            'current_page' => $productsData['current_page'],
            'per_page' => $productsData['per_page'],
            'total_items' => $productsData['total_items'],
            'total_pages' => $productsData['total_pages']
        ];
        $this->data['categories'] = $categories;
        $this->data['warehouses'] = $warehouses;
        $this->data['filters'] = $filters;
        $this->data['title'] = 'Інвентаризація';
        
        $this->view('admin/warehouse/inventory');
    }
    
    /**
     * Страница движения товаров
     */
    public function movements() {
        // Получение параметров фильтрации и пагинации
        $page = intval($this->input('page', 1));
        
        $filters = [
            'product_id' => $this->input('product_id'),
            'warehouse_id' => $this->input('warehouse_id'),
            'movement_type' => $this->input('movement_type'),
            'date_from' => $this->input('date_from'),
            'date_to' => $this->input('date_to'),
            'keyword' => $this->input('keyword')
        ];
        
        // Получение движений товаров с деталями
        $movementsData = $this->inventoryMovementModel->getWithDetails($filters, $page, ITEMS_PER_PAGE);
        
        // Получение продуктов для фильтра
        $products = $this->productModel->getAll();
        
        // Получение складов для фильтра
        $warehouses = $this->warehouseModel->getAll();
        
        // Передача данных в представление
        $this->data['movements'] = $movementsData['items'];
        $this->data['pagination'] = [
            'current_page' => $movementsData['current_page'],
            'per_page' => $movementsData['per_page'],
            'total_items' => $movementsData['total_items'],
            'total_pages' => $movementsData['total_pages']
        ];
        $this->data['products'] = $products;
        $this->data['warehouses'] = $warehouses;
        $this->data['filters'] = $filters;
        $this->data['title'] = 'Рух товарів';
        
        $this->view('admin/warehouse/movements');
    }
    
    public function movements2() {
        // Получение параметров фильтрации и пагинации
        $page = intval($this->input('page', 1));
        
        $filters = [
            'product_id' => $this->input('product_id'),
            'warehouse_id' => $this->input('warehouse_id'),
            'movement_type' => $this->input('movement_type'),
            'date_from' => $this->input('date_from'),
            'date_to' => $this->input('date_to'),
            'keyword' => $this->input('keyword')
        ];
        
        // Получение движений товаров с деталями
        $movementsData = $this->inventoryMovementModel->getWithDetails($filters, $page, ITEMS_PER_PAGE);
        
        // Получение продуктов для фильтра
        $products = $this->productModel->getAll();
        
        // Получение складов для фильтра
        $warehouses = $this->warehouseModel->getAll();
        
        // Передача данных в представление
        $this->data['movements'] = $movementsData['items'];
        $this->data['pagination'] = [
            'current_page' => $movementsData['current_page'],
            'per_page' => $movementsData['per_page'],
            'total_items' => $movementsData['total_items'],
            'total_pages' => $movementsData['total_pages']
        ];
        $this->data['products'] = $products;
        $this->data['warehouses'] = $warehouses;
        $this->data['filters'] = $filters;
        $this->data['title'] = 'Рух товарів';
        
        $this->view('admin/warehouse/movements');
    }
    
    /**
     * Страница добавления движения товара
     */
    public function camera() {
        $this->data['title'] = 'Відеонагляд';
        
        $this->view('admin/reports/camera');
    }
    
    /**
     * Обработка формы добавления движения товара
     */
    public function storeMovement() {
        // Проверка метода запроса
        if (!$this->isPost()) {
            $this->redirect('admin/warehouse/add_movement');
            return;
        }
        
        // Проверка CSRF-токена
        $this->validateCsrfToken();
        
        // Получение данных из формы
        $productId = $this->input('product_id');
        $warehouseId = $this->input('warehouse_id', 1);
        $quantity = floatval($this->input('quantity', 0));
        $movementType = $this->input('movement_type', 'incoming');
        $notes = $this->input('notes', '');
        
        // Валидация данных
        $errors = [];
        
        if (empty($productId)) {
            $errors['product_id'] = 'Виберіть продукт';
        }
        
        if ($quantity == 0) {
            $errors['quantity'] = 'Кількість повинна бути відмінною від нуля';
        }
        
        // Проверка доступного количества при отгрузке
        if ($movementType == 'outgoing' && $quantity > 0) {
            $quantity = -$quantity; // Для отгрузки количество должно быть отрицательным
        }
        
        if ($quantity < 0) {
            // Проверка наличия достаточного количества товара
            $product = $this->productModel->getById($productId);
            
            if (!$product) {
                $errors['product_id'] = 'Продукт не знайдено';
            } elseif (abs($quantity) > $product['stock_quantity']) {
                $errors['quantity'] = 'Недостатньо товару на складі. Доступно: ' . $product['stock_quantity'];
            }
        }
        
        // Если есть ошибки, возвращаемся к форме
        if (!empty($errors)) {
            set_form_errors($errors);
            $this->redirect('admin/warehouse/add_movement' . ($productId ? "?product_id=$productId" : ''));
            return;
        }
        
        // Создание записи о движении товара
        $movementData = [
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'quantity' => $quantity,
            'movement_type' => $quantity > 0 ? 'incoming' : 'outgoing',
            'reference_type' => 'manual',
            'notes' => $notes,
            'created_by' => get_current_user_id()
        ];
        
        $movementId = $this->inventoryMovementModel->create($movementData);
        
        if ($movementId) {
            $this->setFlash('success', 'Рух товару успішно записано.');
            $this->redirect('admin/warehouse/movements');
        } else {
            $this->setFlash('error', 'Помилка при записі руху товару.');
            $this->redirect('admin/warehouse/add_movement' . ($productId ? "?product_id=$productId" : ''));
        }
    }
    
    /**
     * Получение информации о наличии товара на складе (AJAX)
     */
    public function getProductStock() {
        // Получение ID продукта
        $productId = $this->input('product_id');
        
        if (!$productId) {
            $this->json(['error' => 'Не вказано ID продукту'], 400);
            return;
        }
        
        // Получение информации о продукте
        $product = $this->productModel->getById($productId);
        
        if (!$product) {
            $this->json(['error' => 'Продукт не знайдено'], 404);
            return;
        }
        
        // Отправка ответа
        $this->json([
            'product_id' => $product['id'],
            'name' => $product['name'],
            'stock' => $product['stock_quantity']
        ]);
    }
}