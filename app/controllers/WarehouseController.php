<?php
// app/controllers/WarehouseController.php - Оновлений контролер для керування складом

class WarehouseController extends BaseController {
    private $warehouseModel;
    private $productModel;
    private $inventoryMovementModel;
    private $categoryModel;
    
    public function __construct() {
        parent::__construct();
        $this->warehouseModel = new Warehouse();
        $this->productModel = new Product();
        $this->inventoryMovementModel = new InventoryMovement();
        $this->categoryModel = new Category();
        
        // Перевірка прав доступу
        if (!has_role(['admin', 'warehouse_manager'])) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки');
            $this->redirect('dashboard');
            return;
        }
    }
    
    /**
     * Відображення панелі керування складом
     */
    public function index() {
        // Отримання статистики по складу
        $warehouseStats = $this->warehouseModel->getStats();
        
        // Отримання товарів з низьким запасом
        $lowStockProducts = $this->productModel->getLowStockProducts();
        
        // Отримання останніх рухів товарів
        $recentMovements = $this->inventoryMovementModel->getRecent(10);
        
        // Отримання замовлень, які очікують обробки
        $orderModel = new Order();
        $pendingOrders = $orderModel->getFiltered(['status' => 'pending'], 1, 5)['items'];
        
        // Передача даних у представлення
        $this->data['warehouseStats'] = $warehouseStats;
        $this->data['lowStockProducts'] = $lowStockProducts;
        $this->data['recentMovements'] = $recentMovements;
        $this->data['pendingOrders'] = $pendingOrders;
        $this->data['title'] = 'Панель керування складом';
        
        $this->view('warehouse/dashboard');
    }
    
    /**
     * Сторінка інвентаризації
     */
    public function inventory() {
        // Отримання параметрів фільтрації та пагінації
        $page = intval($this->input('page', 1));
        
        $filters = [
            'category_id' => $this->input('category_id'),
            'keyword' => $this->input('keyword'),
            'min_stock' => $this->input('min_stock'),
            'max_stock' => $this->input('max_stock'),
            'sort' => $this->input('sort', 'name_asc'),
            'low_stock' => $this->input('low_stock')
        ];
        
        // Якщо вибрано фільтр "тільки товари з низьким запасом"
        if (!empty($filters['low_stock'])) {
            $filters['max_stock'] = 10; // Встановлюємо максимальне значення 10 як "низький запас"
        }
        
        // Отримання продуктів з пагінацією та фільтрацією
        $productsData = $this->productModel->getFiltered($page, ITEMS_PER_PAGE, $filters);
        
        // Отримання категорій для фільтра
        $categories = $this->categoryModel->getAll();
        
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
        $this->data['title'] = 'Інвентаризація складу';
        
        $this->view('warehouse/inventory');
    }
    
    /**
     * Відображення сторінки руху товарів
     */
    public function movements() {
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
        $this->data['title'] = 'Рух товарів';
        
        $this->view('warehouse/movements');
    }
    
    /**
     * Відображення форми додавання руху товару
     */
    public function addMovement() {
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
        $this->data['title'] = 'Додавання руху товару';
        
        $this->view('warehouse/add_movement');
    }
    
    /**
     * Обробка форми додавання руху товару
     */
    public function storeMovement() {
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
            'reference_type' => $this->input('reference_type', 'manual'),
            'reference_id' => $this->input('reference_id'),
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
        
        // Отримання даних для експорту
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
            default:
                $this->redirect('warehouse/movements');
        }
    }
    
    /**
     * Експорт даних у CSV
     *
     * @param array $data
     */
    private function exportCsv($data) {
        // Встановлення заголовків
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="movements_report_' . date('Y-m-d') . '.csv"');
        
        // Створення файлового потоку
        $output = fopen('php://output', 'w');
        
        // Додавання BOM для UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Запис заголовків
        fputcsv($output, ['ID', 'Дата', 'Продукт', 'Склад', 'Тип руху', 'Кількість', 'Примітки', 'Користувач']);
        
        // Запис даних
        foreach ($data as $row) {
            $movement_type = '';
            
            switch ($row['movement_type']) {
                case 'incoming':
                    $movement_type = 'Надходження';
                    break;
                case 'outgoing':
                    $movement_type = 'Витрата';
                    break;
                case 'adjustment':
                    $movement_type = 'Коригування';
                    break;
                default:
                    $movement_type = $row['movement_type'];
            }
            
            fputcsv($output, [
                $row['id'],
                date('d.m.Y H:i', strtotime($row['created_at'])),
                $row['product_name'],
                $row['warehouse_name'],
                $movement_type,
                $row['quantity'],
                $row['notes'],
                $row['first_name'] . ' ' . $row['last_name']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Експорт даних у Excel
     *
     * @param array $data
     */
    private function exportExcel($data) {
        // В реальному проекті тут був би код для створення Excel-файлу
        // Для демонстрації використовуємо CSV
        $this->setFlash('info', 'Експорт у Excel не реалізовано. Використовуємо CSV формат.');
        $this->exportCsv($data);
    }
    
    /**
     * Експорт даних у PDF
     *
     * @param array $data
     */
    private function exportPdf($data) {
        // В реальному проекті тут був би код для створення PDF-файлу
        // Для демонстрації використовуємо CSV
        $this->setFlash('info', 'Експорт у PDF не реалізовано. Використовуємо CSV формат.');
        $this->exportCsv($data);
    }
    
    /**
     * Коригування кількості товару (інвентаризація)
     */
    public function adjustInventory() {
        // Перевірка методу запиту
        if (!$this->isPost()) {
            $this->redirect('warehouse/inventory');
            return;
        }
        
        // Перевірка CSRF-токена
        $this->validateCsrfToken();
        
        // Отримання даних із форми
        $productId = $this->input('product_id');
        $warehouseId = $this->input('warehouse_id', 1);
        $newQuantity = intval($this->input('new_quantity', 0));
        $notes = $this->input('notes', 'Коригування при інвентаризації');
        
        // Валідація даних
        $errors = [];
        
        if (empty($productId)) {
            $errors['product_id'] = 'Виберіть продукт';
        }
        
        if ($newQuantity < 0) {
            $errors['new_quantity'] = 'Кількість не може бути від\'ємною';
        }
        
        // Якщо є помилки, повертаємо користувача до форми
        if (!empty($errors)) {
            set_form_errors($errors);
            $this->redirect('warehouse/inventory');
            return;
        }
        
        // Створення запису про коригування
        $result = $this->inventoryMovementModel->adjustInventory(
            $productId,
            $warehouseId,
            $newQuantity,
            $notes,
            get_current_user_id()
        );
        
        if ($result) {
            $this->setFlash('success', 'Кількість товару успішно відкориговано.');
        } else {
            $this->setFlash('error', 'Помилка при коригуванні кількості товару.');
        }
        
        $this->redirect('warehouse/inventory');
    }
    
    /**
     * Сторінка управління складами
     */
    public function manageWarehouses() {
        // Отримання списку складів
        $warehouses = $this->warehouseModel->getAllWithManagers();
        
        // Передача даних у представлення
        $this->data['warehouses'] = $warehouses;
        $this->data['title'] = 'Управління складами';
        
        $this->view('warehouse/warehouses');
    }
    
    /**
     * Відображення форми створення складу
     */
    public function createWarehouse() {
        // Перевірка прав доступу (тільки адміністратор може створювати склади)
        if (!has_role('admin')) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки');
            $this->redirect('warehouse/warehouses');
            return;
        }
        
        // Отримання списку менеджерів для вибору
        $userModel = new User();
        $managers = $userModel->getUsersByRole('warehouse_manager');
        
        // Передача даних у представлення
        $this->data['managers'] = $managers;
        $this->data['title'] = 'Створення нового складу';
        
        $this->view('warehouse/warehouse_form');
    }
    
    /**
     * Обробка форми створення складу
     */
    public function storeWarehouse() {
        // Перевірка прав доступу
        if (!has_role('admin')) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки');
            $this->redirect('warehouse/warehouses');
            return;
        }
        
        // Перевірка методу запиту
        if (!$this->isPost()) {
            $this->redirect('warehouse/create_warehouse');
            return;
        }
        
        // Перевірка CSRF-токена
        $this->validateCsrfToken();
        
        // Отримання даних із форми
        $name = $this->input('name');
        $address = $this->input('address');
        $managerId = $this->input('manager_id') ?: null;
        
        // Валідація даних
        $errors = [];
        
        if (empty($name)) {
            $errors['name'] = 'Введіть назву складу';
        }
        
        if (empty($address)) {
            $errors['address'] = 'Введіть адресу складу';
        }
        
        // Перевірка, чи не існує вже склад з такою назвою
        if ($this->warehouseModel->getByName($name)) {
            $errors['name'] = 'Склад з такою назвою вже існує';
        }
        
        // Якщо є помилки, повертаємо користувача до форми
        if (!empty($errors)) {
            set_form_errors($errors);
            $this->redirect('warehouse/create_warehouse');
            return;
        }
        
        // Створення нового складу
        $warehouseData = [
            'name' => $name,
            'address' => $address,
            'manager_id' => $managerId
        ];
        
        $warehouseId = $this->warehouseModel->create($warehouseData);
        
        if ($warehouseId) {
            $this->setFlash('success', 'Склад успішно створено.');
            $this->redirect('warehouse/warehouses');
        } else {
            $this->setFlash('error', 'Помилка при створенні складу.');
            $this->redirect('warehouse/create_warehouse');
        }
    }
    
    /**
     * Відображення форми редагування складу
     *
     * @param int $id
     */
    public function editWarehouse($id) {
        // Перевірка прав доступу
        if (!has_role('admin')) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки');
            $this->redirect('warehouse/warehouses');
            return;
        }
        
        // Отримання даних про склад
        $warehouse = $this->warehouseModel->getById($id);
        
        if (!$warehouse) {
            $this->setFlash('error', 'Склад не знайдено');
            $this->redirect('warehouse/warehouses');
            return;
        }
        
        // Отримання списку менеджерів для вибору
        $userModel = new User();
        $managers = $userModel->getUsersByRole('warehouse_manager');
        
        // Передача даних у представлення
        $this->data['warehouse'] = $warehouse;
        $this->data['managers'] = $managers;
        $this->data['title'] = 'Редагування складу: ' . $warehouse['name'];
        
        $this->view('warehouse/warehouse_form');
    }
    
    /**
     * Обробка форми редагування складу
     *
     * @param int $id
     */
    public function updateWarehouse($id) {
        // Перевірка прав доступу
        if (!has_role('admin')) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки');
            $this->redirect('warehouse/warehouses');
            return;
        }
        
        // Перевірка методу запиту
        if (!$this->isPost()) {
            $this->redirect('warehouse/edit_warehouse/' . $id);
            return;
        }
        
        // Перевірка CSRF-токена
        $this->validateCsrfToken();
        
        // Отримання даних про склад
        $warehouse = $this->warehouseModel->getById($id);
        
        if (!$warehouse) {
            $this->setFlash('error', 'Склад не знайдено');
            $this->redirect('warehouse/warehouses');
            return;
        }
        
        // Отримання даних із форми
        $name = $this->input('name');
        $address = $this->input('address');
        $managerId = $this->input('manager_id') ?: null;
        
        // Валідація даних
        $errors = [];
        
        if (empty($name)) {
            $errors['name'] = 'Введіть назву складу';
        }
        
        if (empty($address)) {
            $errors['address'] = 'Введіть адресу складу';
        }
        
        // Перевірка, чи не існує вже склад з такою назвою (крім поточного)
        $existingWarehouse = $this->warehouseModel->getByName($name);
        if ($existingWarehouse && $existingWarehouse['id'] != $id) {
            $errors['name'] = 'Склад з такою назвою вже існує';
        }
        
        // Якщо є помилки, повертаємо користувача до форми
        if (!empty($errors)) {
            set_form_errors($errors);
            $this->redirect('warehouse/edit_warehouse/' . $id);
            return;
        }
        
        // Оновлення даних про склад
        $warehouseData = [
            'name' => $name,
            'address' => $address,
            'manager_id' => $managerId
        ];
        
        $result = $this->warehouseModel->update($id, $warehouseData);
        
        if ($result) {
            $this->setFlash('success', 'Інформацію про склад успішно оновлено.');
            $this->redirect('warehouse/warehouses');
        } else {
            $this->setFlash('error', 'Помилка при оновленні інформації про склад.');
            $this->redirect('warehouse/edit_warehouse/' . $id);
        }
    }
    
    /**
     * Видалення складу
     *
     * @param int $id
     */
    public function deleteWarehouse($id) {
        // Перевірка прав доступу
        if (!has_role('admin')) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки');
            $this->redirect('warehouse/warehouses');
            return;
        }
        
        // Отримання даних про склад
        $warehouse = $this->warehouseModel->getById($id);
        
        if (!$warehouse) {
            $this->setFlash('error', 'Склад не знайдено');
            $this->redirect('warehouse/warehouses');
            return;
        }
        
        // Перевірка, чи є товари на складі
        $inventory = $this->warehouseModel->getInventory($id);
        
        if (!empty($inventory)) {
            $this->setFlash('error', 'Неможливо видалити склад, оскільки на ньому є товари. Спочатку перемістіть всі товари.');
            $this->redirect('warehouse/warehouses');
            return;
        }
        
        // Видалення складу
        $result = $this->warehouseModel->delete($id);
        
        if ($result) {
            $this->setFlash('success', 'Склад успішно видалено.');
        } else {
            $this->setFlash('error', 'Помилка при видаленні складу.');
        }
        
        $this->redirect('warehouse/warehouses');
    }
    
    /**
     * Сторінка перегляду товарів на складі
     *
     * @param int $id
     */
    public function viewWarehouseInventory($id) {
        // Отримання даних про склад
        $warehouse = $this->warehouseModel->getById($id);
        
        if (!$warehouse) {
            $this->setFlash('error', 'Склад не знайдено');
            $this->redirect('warehouse/warehouses');
            return;
        }
        
        // Отримання товарів на складі
        $inventory = $this->warehouseModel->getInventory($id);
        
        // Передача даних у представлення
        $this->data['warehouse'] = $warehouse;
        $this->data['inventory'] = $inventory;
        $this->data['title'] = 'Товари на складі: ' . $warehouse['name'];
        
        $this->view('warehouse/warehouse_inventory');
    }
    
    /**
     * Сторінка перенесення товарів між складами
     */
    public function transferProducts() {
        // Отримання списку складів
        $warehouses = $this->warehouseModel->getAll();
        
        // Отримання списку продуктів
        $products = $this->productModel->getAll();
        
        // Передача даних у представлення
        $this->data['warehouses'] = $warehouses;
        $this->data['products'] = $products;
        $this->data['title'] = 'Перенесення товарів між складами';
        
        $this->view('warehouse/transfer_products');
    }
    
    /**
     * Обробка форми перенесення товарів
     */
    public function storeTransfer() {
        // Перевірка методу запиту
        if (!$this->isPost()) {
            $this->redirect('warehouse/transfer_products');
            return;
        }
        
        // Перевірка CSRF-токена
        $this->validateCsrfToken();
        
        // Отримання даних із форми
        $productId = $this->input('product_id');
        $fromWarehouseId = $this->input('from_warehouse_id');
        $toWarehouseId = $this->input('to_warehouse_id');
        $quantity = intval($this->input('quantity', 0));
        $notes = $this->input('notes', 'Перенесення між складами');
        
        // Валідація даних
        $errors = [];
        
        if (empty($productId)) {
            $errors['product_id'] = 'Виберіть продукт';
        }
        
        if (empty($fromWarehouseId)) {
            $errors['from_warehouse_id'] = 'Виберіть склад-джерело';
        }
        
        if (empty($toWarehouseId)) {
            $errors['to_warehouse_id'] = 'Виберіть склад-приймач';
        }
        
        if ($fromWarehouseId == $toWarehouseId) {
            $errors['to_warehouse_id'] = 'Склад-джерело та склад-приймач не можуть бути однаковими';
        }
        
        if ($quantity <= 0) {
            $errors['quantity'] = 'Кількість має бути більше нуля';
        }
        
        // Перевірка наявності достатньої кількості товару на складі-джерелі
        if (!empty($fromWarehouseId) && !empty($productId) && $quantity > 0) {
            if (!$this->warehouseModel->hasEnoughStock($fromWarehouseId, $productId, $quantity)) {
                $errors['quantity'] = 'Недостатньо товару на складі-джерелі';
            }
        }
        
        // Якщо є помилки, повертаємо користувача до форми
        if (!empty($errors)) {
            set_form_errors($errors);
            $this->redirect('warehouse/transfer_products');
            return;
        }
        
        try {
            // Починаємо транзакцію
            $this->db->beginTransaction();
            
            // Створення запису про відвантаження зі складу-джерела
            $outgoingData = [
                'product_id' => $productId,
                'warehouse_id' => $fromWarehouseId,
                'quantity' => -$quantity,
                'movement_type' => 'outgoing',
                'reference_type' => 'transfer',
                'reference_id' => time(), // Використовуємо timestamp як ID перенесення
                'notes' => $notes . ' (Відвантаження)',
                'created_by' => get_current_user_id()
            ];
            
            $outgoingId = $this->inventoryMovementModel->create($outgoingData);
            
            if (!$outgoingId) {
                throw new Exception('Помилка при створенні запису про відвантаження');
            }
            
            // Створення запису про надходження на склад-приймач
            $incomingData = [
                'product_id' => $productId,
                'warehouse_id' => $toWarehouseId,
                'quantity' => $quantity,
                'movement_type' => 'incoming',
                'reference_type' => 'transfer',
                'reference_id' => time(), // Той же ID перенесення
                'notes' => $notes . ' (Надходження)',
                'created_by' => get_current_user_id()
            ];
            
            $incomingId = $this->inventoryMovementModel->create($incomingData);
            
            if (!$incomingId) {
                throw new Exception('Помилка при створенні запису про надходження');
            }
            
            // Фіксуємо транзакцію
            $this->db->commit();
            
            $this->setFlash('success', 'Перенесення товару між складами успішно виконано.');
            $this->redirect('warehouse/movements');
            
        } catch (Exception $e) {
            // Відкочуємо транзакцію у випадку помилки
            $this->db->rollBack();
            
            if (DEBUG_MODE) {
                error_log("Error in storeTransfer: " . $e->getMessage());
                error_log($e->getTraceAsString());
            }
            
            $this->setFlash('error', 'Помилка при перенесенні товару: ' . $e->getMessage());
            $this->redirect('warehouse/transfer_products');
        }
    }
    
    /**
     * Сторінка інвентаризації (перевірка фактичної наявності)
     */
    public function stocktaking() {
        // Отримання параметрів фільтрації
        $filters = [
            'category_id' => $this->input('category_id'),
            'keyword' => $this->input('keyword')
        ];
        
        // Отримання продуктів для інвентаризації
        $products = $this->productModel->getFiltered(1, 1000, $filters)['items'];
        
        // Отримання категорій для фільтра
        $categories = $this->categoryModel->getAll();
        
        // Передача даних у представлення
        $this->data['products'] = $products;
        $this->data['categories'] = $categories;
        $this->data['filters'] = $filters;
        $this->data['title'] = 'Інвентаризація (перевірка фактичної наявності)';
        
        $this->view('warehouse/stocktaking');
    }
    
    /**
     * Обробка результатів інвентаризації
     */
    public function storeStocktaking() {
        // Перевірка методу запиту
        if (!$this->isPost()) {
            $this->redirect('warehouse/stocktaking');
            return;
        }
        
        // Перевірка CSRF-токена
        $this->validateCsrfToken();
        
        // Отримання даних із форми
        $productIds = $this->input('product_id', []);
        $actualQuantities = $this->input('actual_quantity', []);
        $notes = $this->input('notes', []);
        
        // Перевірка наявності даних
        if (empty($productIds) || empty($actualQuantities)) {
            $this->setFlash('error', 'Не вказано жодного товару для інвентаризації');
            $this->redirect('warehouse/stocktaking');
            return;
        }
        
        // Обробка кожного товару
        $success = true;
        $updatedCount = 0;
        
        try {
            // Починаємо транзакцію
            $this->db->beginTransaction();
            
            foreach ($productIds as $index => $productId) {
                // Отримання поточних даних про товар
                $product = $this->productModel->getById($productId);
                
                if (!$product) {
                    continue;
                }
                
                // Конвертація фактичної кількості в ціле число
                $actualQuantity = intval($actualQuantities[$index]);
                
                // Якщо кількість не змінилася, пропускаємо
                if ($actualQuantity == $product['stock_quantity']) {
                    continue;
                }
                
                // Формування приміток
                $noteText = !empty($notes[$index]) ? $notes[$index] : 'Коригування за результатами інвентаризації';
                
                // Створення запису про коригування
                $movementType = $actualQuantity > $product['stock_quantity'] ? 'incoming' : 'outgoing';
                $quantityDifference = $actualQuantity - $product['stock_quantity'];
                
                $movementData = [
                    'product_id' => $productId,
                    'warehouse_id' => 1, // Основний склад
                    'quantity' => $quantityDifference,
                    'movement_type' => $movementType,
                    'reference_type' => 'stocktaking',
                    'notes' => $noteText,
                    'created_by' => get_current_user_id()
                ];
                
                $movementId = $this->inventoryMovementModel->create($movementData);
                
                if (!$movementId) {
                    throw new Exception('Помилка при створенні запису про коригування для товару ID: ' . $productId);
                }
                
                $updatedCount++;
            }
            
            // Фіксуємо транзакцію
            $this->db->commit();
            
            if ($updatedCount > 0) {
                $this->setFlash('success', 'Інвентаризація успішно завершена. Оновлено кількість для ' . $updatedCount . ' товарів.');
            } else {
                $this->setFlash('info', 'Інвентаризація завершена. Розбіжностей не виявлено.');
            }
            
            $this->redirect('warehouse/inventory');
            
        } catch (Exception $e) {
            // Відкочуємо транзакцію у випадку помилки
            $this->db->rollBack();
            
            if (DEBUG_MODE) {
                error_log("Error in storeStocktaking: " . $e->getMessage());
                error_log($e->getTraceAsString());
            }
            
            $this->setFlash('error', 'Помилка при обробці результатів інвентаризації: ' . $e->getMessage());
            $this->redirect('warehouse/stocktaking');
        }
    }
    
    /**
     * Отримання детальної інформації про рух товару (AJAX)
     */
    public function getMovementDetails() {
        // Отримання ID руху
        $movementId = $this->input('movement_id');
        
        if (!$movementId) {
            $this->json(['error' => 'Не вказано ID руху'], 400);
            return;
        }
        
        // Отримання інформації про рух
        $movement = $this->db->getOne('SELECT im.*, p.name as product_name, w.name as warehouse_name, 
                                      u.first_name, u.last_name 
                                      FROM inventory_movements im 
                                      JOIN products p ON im.product_id = p.id 
                                      JOIN warehouses w ON im.warehouse_id = w.id 
                                      JOIN users u ON im.created_by = u.id 
                                      WHERE im.id = ?', [$movementId]);
        
        if (!$movement) {
            $this->json(['error' => 'Рух не знайдено'], 404);
            return;
        }
        
        // Форматування даних для відповіді
        $formattedMovement = [
            'id' => $movement['id'],
            'date' => date('d.m.Y H:i', strtotime($movement['created_at'])),
            'product' => [
                'id' => $movement['product_id'],
                'name' => $movement['product_name']
            ],
            'warehouse' => [
                'id' => $movement['warehouse_id'],
                'name' => $movement['warehouse_name']
            ],
            'movement_type' => $movement['movement_type'],
            'quantity' => $movement['quantity'],
            'reference_type' => $movement['reference_type'],
            'reference_id' => $movement['reference_id'],
            'notes' => $movement['notes'],
            'user' => $movement['first_name'] . ' ' . $movement['last_name']
        ];
        
        // Якщо це перенесення, отримуємо зв'язаний запис
        if ($movement['reference_type'] == 'transfer' && $movement['reference_id']) {
            $relatedMovement = $this->db->getOne('SELECT im.*, w.name as warehouse_name 
                                                 FROM inventory_movements im 
                                                 JOIN warehouses w ON im.warehouse_id = w.id 
                                                 WHERE im.reference_type = ? AND im.reference_id = ? AND im.id != ?', 
                                                ['transfer', $movement['reference_id'], $movement['id']]);
            
            if ($relatedMovement) {
                $formattedMovement['related_movement'] = [
                    'id' => $relatedMovement['id'],
                    'date' => date('d.m.Y H:i', strtotime($relatedMovement['created_at'])),
                    'warehouse' => [
                        'id' => $relatedMovement['warehouse_id'],
                        'name' => $relatedMovement['warehouse_name']
                    ],
                    'movement_type' => $relatedMovement['movement_type'],
                    'quantity' => $relatedMovement['quantity']
                ];
            }
        }
        
        // Відправка відповіді
        $this->json($formattedMovement);
    }
    
    /**
     * Відображення аналітики руху товарів
     */
    public function movementsAnalytics() {
        // Отримання параметрів фільтрації
        $period = $this->input('period', 'month');
        $productId = $this->input('product_id');
        $warehouseId = $this->input('warehouse_id');
        
        // Визначення дат для фільтрації
        $endDate = date('Y-m-d');
        $startDate = '';
        
        switch ($period) {
            case 'week':
                $startDate = date('Y-m-d', strtotime('-7 days'));
                break;
            case 'month':
                $startDate = date('Y-m-d', strtotime('-30 days'));
                break;
            case 'quarter':
                $startDate = date('Y-m-d', strtotime('-90 days'));
                break;
            case 'year':
                $startDate = date('Y-m-d', strtotime('-365 days'));
                break;
            default:
                $startDate = date('Y-m-d', strtotime('-30 days'));
        }
        
        // Підготовка фільтрів
        $filters = [
            'date_from' => $startDate,
            'date_to' => $endDate,
            'product_id' => $productId,
            'warehouse_id' => $warehouseId
        ];
        
        // Отримання статистики руху товарів
        $movementsData = $this->inventoryMovementModel->getWithDetails($filters, 1, 1000);
        
        // Агрегація даних для графіків
        $dailyData = [];
        $productData = [];
        $warehouseData = [];
        $typeData = [
            'incoming' => 0,
            'outgoing' => 0,
            'adjustment' => 0
        ];
        
        foreach ($movementsData['items'] as $movement) {
            // Дані по днях
            $date = date('Y-m-d', strtotime($movement['created_at']));
            
            if (!isset($dailyData[$date])) {
                $dailyData[$date] = [
                    'incoming' => 0,
                    'outgoing' => 0,
                    'adjustment' => 0
                ];
            }
            
            if ($movement['movement_type'] == 'incoming') {
                $dailyData[$date]['incoming'] += $movement['quantity'];
            } elseif ($movement['movement_type'] == 'outgoing') {
                $dailyData[$date]['outgoing'] += abs($movement['quantity']);
            } elseif ($movement['movement_type'] == 'adjustment') {
                if ($movement['quantity'] > 0) {
                    $dailyData[$date]['adjustment'] += $movement['quantity'];
                } else {
                    $dailyData[$date]['adjustment'] -= abs($movement['quantity']);
                }
            }
            
            // Дані по продуктах
            if (!isset($productData[$movement['product_id']])) {
                $productData[$movement['product_id']] = [
                    'name' => $movement['product_name'],
                    'incoming' => 0,
                    'outgoing' => 0,
                    'total' => 0
                ];
            }
            
            if ($movement['movement_type'] == 'incoming') {
                $productData[$movement['product_id']]['incoming'] += $movement['quantity'];
                $productData[$movement['product_id']]['total'] += $movement['quantity'];
            } elseif ($movement['movement_type'] == 'outgoing') {
                $productData[$movement['product_id']]['outgoing'] += abs($movement['quantity']);
                $productData[$movement['product_id']]['total'] -= abs($movement['quantity']);
            }
            
            // Дані по складах
            if (!isset($warehouseData[$movement['warehouse_id']])) {
                $warehouseData[$movement['warehouse_id']] = [
                    'name' => $movement['warehouse_name'],
                    'incoming' => 0,
                    'outgoing' => 0,
                    'total' => 0
                ];
            }
            
            if ($movement['movement_type'] == 'incoming') {
                $warehouseData[$movement['warehouse_id']]['incoming'] += $movement['quantity'];
                $warehouseData[$movement['warehouse_id']]['total'] += $movement['quantity'];
            } elseif ($movement['movement_type'] == 'outgoing') {
                $warehouseData[$movement['warehouse_id']]['outgoing'] += abs($movement['quantity']);
                $warehouseData[$movement['warehouse_id']]['total'] -= abs($movement['quantity']);
            }
            
            // Дані по типах руху
            if ($movement['movement_type'] == 'incoming') {
                $typeData['incoming'] += $movement['quantity'];
            } elseif ($movement['movement_type'] == 'outgoing') {
                $typeData['outgoing'] += abs($movement['quantity']);
            } elseif ($movement['movement_type'] == 'adjustment') {
                if ($movement['quantity'] > 0) {
                    $typeData['adjustment'] += $movement['quantity'];
                } else {
                    $typeData['adjustment'] -= abs($movement['quantity']);
                }
            }
        }
        
        // Сортування даних
        ksort($dailyData); // Сортуємо за датою (ключ)
        
        // Сортуємо продукти за загальним обсягом руху (надходження + витрати)
        uasort($productData, function($a, $b) {
            return ($b['incoming'] + $b['outgoing']) - ($a['incoming'] + $a['outgoing']);
        });
        
        // Сортуємо склади за загальним обсягом руху (надходження + витрати)
        uasort($warehouseData, function($a, $b) {
            return ($b['incoming'] + $b['outgoing']) - ($a['incoming'] + $a['outgoing']);
        });
        
        // Отримання продуктів для фільтра
        $products = $this->productModel->getAll();
        
        // Отримання складів для фільтра
        $warehouses = $this->warehouseModel->getAll();
        
        // Передача даних у представлення
        $this->data['dailyData'] = $dailyData;
        $this->data['productData'] = $productData;
        $this->data['warehouseData'] = $warehouseData;
        $this->data['typeData'] = $typeData;
        $this->data['products'] = $products;
        $this->data['warehouses'] = $warehouses;
        $this->data['filters'] = $filters;
        $this->data['period'] = $period;
        $this->data['title'] = 'Аналітика руху товарів';
        
        $this->view('warehouse/movements_analytics');
    }
    
    /**
     * Відображення сторінки замовлення товарів у постачальників
     */
    public function orderProducts() {
        // Отримання товарів з низьким запасом
        $lowStockProducts = $this->productModel->getLowStockProducts();
        
        // Отримання всіх продуктів
        $products = $this->productModel->getAll();
        
        // Передача даних у представлення
        $this->data['lowStockProducts'] = $lowStockProducts;
        $this->data['products'] = $products;
        $this->data['title'] = 'Замовлення товарів у постачальників';
        
        $this->view('warehouse/order_products');
    }
    
    /**
     * Обробка форми замовлення товарів
     */
    public function storeOrder() {
        // Перевірка методу запиту
        if (!$this->isPost()) {
            $this->redirect('warehouse/order_products');
            return;
        }
        
        // Перевірка CSRF-токена
        $this->validateCsrfToken();
        
        // Отримання даних із форми
        $productIds = $this->input('product_id', []);
        $quantities = $this->input('quantity', []);
        $supplierName = $this->input('supplier_name');
        $notes = $this->input('notes', '');
        
        // Перевірка наявності даних
        if (empty($productIds) || empty($quantities) || empty($supplierName)) {
            $this->setFlash('error', 'Необхідно вказати товари, кількість та постачальника');
            $this->redirect('warehouse/order_products');
            return;
        }
        
        // В реальній системі тут був би код для відправки замовлення постачальнику
        // Для демонстрації ми просто створимо запис про очікуване надходження
        
        try {
            // Починаємо транзакцію
            $this->db->beginTransaction();
            
            // Створення "замовлення постачальнику" - в даному випадку це просто запис у логі
            $referenceId = time(); // Використовуємо timestamp як ID замовлення
            
            foreach ($productIds as $index => $productId) {
                if (empty($quantities[$index]) || intval($quantities[$index]) <= 0) {
                    continue;
                }
                
                // Створення запису про очікуване надходження
                $movementData = [
                    'product_id' => $productId,
                    'warehouse_id' => 1, // Основний склад
                    'quantity' => intval($quantities[$index]),
                    'movement_type' => 'incoming',
                    'reference_type' => 'supplier_order',
                    'reference_id' => $referenceId,
                    'notes' => 'Замовлення постачальнику: ' . $supplierName . '. ' . $notes,
                    'created_by' => get_current_user_id(),
                    'skip_stock_update' => true // Не оновлювати кількість товару, оскільки це лише замовлення
                ];
                
                $movementId = $this->inventoryMovementModel->create($movementData);
                
                if (!$movementId) {
                    throw new Exception('Помилка при створенні запису про замовлення для товару ID: ' . $productId);
                }
            }
            
            // Фіксуємо транзакцію
            $this->db->commit();
            
            $this->setFlash('success', 'Замовлення успішно відправлено постачальнику.');
            $this->redirect('warehouse/movements');
            
        } catch (Exception $e) {
            // Відкочуємо транзакцію у випадку помилки
            $this->db->rollBack();
            
            if (DEBUG_MODE) {
                error_log("Error in storeOrder: " . $e->getMessage());
                error_log($e->getTraceAsString());
            }
            
            $this->setFlash('error', 'Помилка при створенні замовлення: ' . $e->getMessage());
            $this->redirect('warehouse/order_products');
        }
    }
    
    /**
     * Відображення сторінки звітів складу
     */
    public function reports() {
        // Отримання параметрів фільтрації
        $reportType = $this->input('report_type', 'inventory');
        $startDate = $this->input('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDate = $this->input('end_date', date('Y-m-d'));
        $categoryId = $this->input('category_id');
        $productId = $this->input('product_id');
        $warehouseId = $this->input('warehouse_id');
        
        // Підготовка фільтрів
        $filters = [
            'date_from' => $startDate,
            'date_to' => $endDate,
            'category_id' => $categoryId,
            'product_id' => $productId,
            'warehouse_id' => $warehouseId
        ];
        
        // Отримання даних для звіту
        $reportData = [];
        
        switch ($reportType) {
            case 'inventory':
                // Звіт по поточним запасам
                $inventoryData = $this->productModel->getFiltered(1, 1000, [
                    'category_id' => $categoryId
                ])['items'];
                
                $reportData = [
                    'inventory' => $inventoryData,
                    'totalValue' => array_sum(array_map(function($item) {
                        return $item['price'] * $item['stock_quantity'];
                    }, $inventoryData))
                ];
                break;
                
            case 'movements':
                // Звіт по руху товарів
                $movementsData = $this->inventoryMovementModel->getWithDetails($filters, 1, 1000);
                
                // Агрегація даних
                $totalIncoming = 0;
                $totalOutgoing = 0;
                
                foreach ($movementsData['items'] as $movement) {
                    if ($movement['movement_type'] == 'incoming') {
                        $totalIncoming += $movement['quantity'];
                    } elseif ($movement['movement_type'] == 'outgoing') {
                        $totalOutgoing += abs($movement['quantity']);
                    }
                }
                
                $reportData = [
                    'movements' => $movementsData['items'],
                    'totalIncoming' => $totalIncoming,
                    'totalOutgoing' => $totalOutgoing,
                    'balance' => $totalIncoming - $totalOutgoing
                ];
                break;
                
            case 'low_stock':
                // Звіт по товарам з низьким запасом
                $lowStockProducts = $this->productModel->getLowStockProducts();
                
                $reportData = [
                    'lowStock' => $lowStockProducts,
                    'count' => count($lowStockProducts)
                ];
                break;
                
            case 'category':
                // Звіт по категоріям
                $categories = $this->categoryModel->getAllWithProductCount();
                
                $totalProducts = 0;
                $totalValue = 0;
                
                foreach ($categories as &$category) {
                    $categoryProducts = $this->productModel->getFiltered(1, 1000, [
                        'category_id' => $category['id']
                    ])['items'];
                    
                    $categoryValue = array_sum(array_map(function($item) {
                        return $item['price'] * $item['stock_quantity'];
                    }, $categoryProducts));
                    
                    $category['total_value'] = $categoryValue;
                    $totalProducts += $category['product_count'];
                    $totalValue += $categoryValue;
                }
                
                $reportData = [
                    'categories' => $categories,
                    'totalProducts' => $totalProducts,
                    'totalValue' => $totalValue
                ];
                break;
        }
        
        // Отримання категорій для фільтра
        $categories = $this->categoryModel->getAll();
        
        // Отримання продуктів для фільтра
        $products = $this->productModel->getAll();
        
        // Отримання складів для фільтра
        $warehouses = $this->warehouseModel->getAll();
        
        // Передача даних у представлення
        $this->data['reportType'] = $reportType;
        $this->data['reportData'] = $reportData;
        $this->data['categories'] = $categories;
        $this->data['products'] = $products;
        $this->data['warehouses'] = $warehouses;
        $this->data['filters'] = $filters;
        $this->data['title'] = 'Складські звіти';
        
        $this->view('warehouse/reports');
    }
}