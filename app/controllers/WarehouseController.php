<?php
// app/controllers/WarehouseController.php - Контролер для управління складом

class WarehouseController extends BaseController {
    private $warehouseModel;
    private $productModel;
    private $inventoryMovementModel;
    
    public function __construct() {
        parent::__construct();
        $this->warehouseModel = new Warehouse();
        $this->productModel = new Product();
        $this->inventoryMovementModel = new InventoryMovement();
    }

    /**
 * Експорт звіту про рух товарів
 */
public function exportMovements() {
    $filters = [
        'product_id' => $this->input('product_id'),
        'warehouse_id' => $this->input('warehouse_id'),
        'movement_type' => $this->input('movement_type'),
        'date_from' => $this->input('date_from'),
        'date_to' => $this->input('date_to'),
        'keyword' => $this->input('keyword')
    ];
    
    $format = $this->input('format', 'csv');
    
    $movementsData = $this->inventoryMovementModel->getWithDetails($filters);
    
    // Логіка експорту в різних форматах
    switch ($format) {
        case 'csv':
            $this->exportCsv($movementsData['items']);
            break;
        case 'excel':
            $this->exportExcel($movementsData['items']);
            break;
        case 'pdf':
            $this->exportPdf($movementsData['items']);
            break;
    }
}

private function exportCsv($data) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="movements_report.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Дата', 'Продукт', 'Склад', 'Тип руху', 'Кількість', 'Примітки']);
    
    foreach ($data as $row) {
        fputcsv($output, [
            $row['id'], 
            $row['created_at'], 
            $row['product_name'], 
            $row['warehouse_name'], 
            $row['movement_type'], 
            $row['quantity'], 
            $row['notes']
        ]);
    }
    
    fclose($output);
    exit;
}
    
    /**
     * Відображення панелі керування складом
     */
    public function index() {
        // Перевірка прав доступу
        if (!has_role(['admin', 'warehouse_manager'])) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки.');
            $this->redirect('dashboard');
            return;
        }
        
        // Перенаправлення на складську панель керування
        $this->redirect('dashboard');
    }
    
    /**
     * Відображення сторінки інвентаризації
     */
    public function inventory() {
        // Перевірка прав доступу
        if (!has_role(['admin', 'warehouse_manager'])) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки.');
            $this->redirect('dashboard');
            return;
        }
        
        // Отримання параметрів фільтрації та пагінації
        $page = intval($this->input('page', 1));
        
        $filters = [
            'category_id' => $this->input('category_id'),
            'keyword' => $this->input('keyword'),
            'min_stock' => $this->input('min_stock'),
            'max_stock' => $this->input('max_stock'),
            'sort' => $this->input('sort', 'name_asc')
        ];
        
        // Отримання продуктів з пагінацією та фільтрацією
        $productsData = $this->productModel->getFiltered($page, ITEMS_PER_PAGE, $filters);
        
        // Отримання категорій для фільтра
        $categoryModel = new Category();
        $categories = $categoryModel->getAll();
        
        // Отримання складів
        $warehouses = $this->warehouseModel->getAll();
        
        // Передача даних у представлення
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
        
        $this->view('warehouse/inventory');
    }
    
    /**
     * Відображення сторінки руху товарів
     */
    public function movements() {
        // Перевірка прав доступу
        if (!has_role(['admin', 'warehouse_manager'])) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки.');
            $this->redirect('dashboard');
            return;
        }
        
        // Отримання параметрів фільтрації та пагінації
        $page = intval($this->input('page', 1));
        
        $filters = [
            'product_id' => $this->input('product_id'),
            'warehouse_id' => $this->input('warehouse_id'),
            'movement_type' => $this->input('movement_type'),
            'date_from' => $this->input('date_from'),
            'date_to' => $this->input('date_to'),
            'keyword' => $this->input('keyword')
        ];
        
        // Отримання рухів товарів з деталями
        $movementsData = $this->inventoryMovementModel->getWithDetails($filters, $page, ITEMS_PER_PAGE);
        
        // Отримання продуктів для фільтра
        $products = $this->productModel->getAll();
        
        // Отримання складів для фільтра
        $warehouses = $this->warehouseModel->getAll();
        
        // Передача даних у представлення
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
        
        $this->view('warehouse/movements');
    }
    
    /**
     * Відображення форми додавання руху товару
     */
    public function addMovement() {
        // Перевірка прав доступу
        if (!has_role(['admin', 'warehouse_manager'])) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки.');
            $this->redirect('dashboard');
            return;
        }
        
        // Отримання продукту, якщо він вказаний в URL
        $productId = $this->input('product_id');
        $product = null;
        
        if ($productId) {
            $product = $this->productModel->getById($productId);
        }
        
        // Отримання списку продуктів
        $products = $this->productModel->getAll();
        
        // Отримання списку складів
        $warehouses = $this->warehouseModel->getAll();
        
        // Передача даних у представлення
        $this->data['product'] = $product;
        $this->data['products'] = $products;
        $this->data['warehouses'] = $warehouses;
        
        $this->view('warehouse/add_movement');
    }
    
    /**
     * Обробка форми додавання руху товару
     */
    public function storeMovement() {
        // Перевірка прав доступу
        if (!has_role(['admin', 'warehouse_manager'])) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки.');
            $this->redirect('dashboard');
            return;
        }
        
        // Перевірка методу запиту
        if (!$this->isPost()) {
            $this->redirect('warehouse/add_movement');
            return;
        }
        
        // Перевірка CSRF-токена
        $this->validateCsrfToken();
        
        // Отримання даних із форми
        $productId = $this->input('product_id');
        $warehouseId = $this->input('warehouse_id', 1);
        $quantity = floatval($this->input('quantity', 0));
        $movementType = $this->input('movement_type', 'incoming');
        $notes = $this->input('notes', '');
        
        // Валідація даних
        $errors = [];
        
        if (empty($productId)) {
            $errors['product_id'] = 'Виберіть продукт';
        }
        
        if ($quantity == 0) {
            $errors['quantity'] = 'Кількість повинна бути відмінною від нуля';
        }
        
        // Перевірка доступної кількості при відвантаженні
        if ($movementType == 'outgoing' && $quantity > 0) {
            $quantity = -$quantity; // Для відвантаження кількість має бути від'ємною
        }
        
        if ($quantity < 0) {
            // Перевірка наявності достатньої кількості товару
            $product = $this->productModel->getById($productId);
            
            if (!$product) {
                $errors['product_id'] = 'Продукт не знайдено';
            } elseif (abs($quantity) > $product['stock_quantity']) {
                $errors['quantity'] = 'Недостатньо товару на складі. Доступно: ' . $product['stock_quantity'];
            }
        }
        
        // Якщо є помилки, повертаємо користувача до форми
        if (!empty($errors)) {
            set_form_errors($errors);
            $this->redirect('warehouse/add_movement' . ($productId ? "?product_id=$productId" : ''));
            return;
        }
        
        // Створення запису про рух товару
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
            $this->redirect('warehouse/movements');
        } else {
            $this->setFlash('error', 'Помилка при записі руху товару.');
            $this->redirect('warehouse/add_movement' . ($productId ? "?product_id=$productId" : ''));
        }
    }
    
    /**
     * Отримання інформації про наявність товару на складі (AJAX)
     */
    public function getProductStock() {
        // Перевірка прав доступу
        if (!has_role(['admin', 'warehouse_manager'])) {
            $this->json(['error' => 'Недостатньо прав доступу'], 403);
            return;
        }
        
        // Отримання ID продукту
        $productId = $this->input('product_id');
        
        if (!$productId) {
            $this->json(['error' => 'Не вказано ID продукту'], 400);
            return;
        }
        
        // Отримання інформації про продукт
        $product = $this->productModel->getById($productId);
        
        if (!$product) {
            $this->json(['error' => 'Продукт не знайдено'], 404);
            return;
        }
        
        // Відправка відповіді
        $this->json([
            'product_id' => $product['id'],
            'name' => $product['name'],
            'stock' => $product['stock_quantity']
        ]);
    }
}