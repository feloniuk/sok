<?php
// app/controllers/ProductController.php - Контролер для роботи з продуктами

class ProductController extends BaseController {
    private $productModel;
    private $categoryModel;
    
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
     * Відображення деталей продукту
     *
     * @param int $id
     */
    public function view($id, $data = []) {
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
        
        // Отримання пов'язаних продуктів (з тієї ж категорії)
        $relatedProducts = [];
        if ($product['category_id']) {
            $relatedSql = 'SELECT * FROM products 
                           WHERE category_id = ? AND id != ? AND is_active = 1 
                           ORDER BY RAND() LIMIT 4';
            $relatedProducts = $this->db->getAll($relatedSql, [$product['category_id'], $id]);
        }
        
        // Передача даних у представлення
        $this->data['product'] = $product;
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
        $price = $this->input('price');
        $stock_quantity = $this->input('stock_quantity', 0);
        $is_featured = $this->input('is_featured') ? 1 : 0;
        $is_active = $this->input('is_active') ? 1 : 0;
        
        // Валідація даних
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
        
        // Створення нового продукту
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
        
        $productId = $this->productModel->create($productData);
        
        if ($productId) {
            // Запис руху товарів (надходження)
            if ($stock_quantity > 0) {
                $inventoryMovementModel = new InventoryMovement();
                $movementData = [
                    'product_id' => $productId,
                    'warehouse_id' => 1, // Використовуємо основний склад
                    'quantity' => $stock_quantity,
                    'movement_type' => 'incoming',
                    'reference_type' => 'product_create',
                    'notes' => 'Початкове надходження при створенні товару',
                    'created_by' => get_current_user_id()
                ];
                
                $inventoryMovementModel->create($movementData);
            }
            
            $this->setFlash('success', 'Продукт успішно створено.');
            $this->redirect('products/view/' . $productId);
        } else {
            $this->setFlash('error', 'Помилка при створенні продукту.');
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
        
        // Передача даних у представлення
        $this->data['product'] = $product;
        $this->data['categories'] = $categories;
        
        $this->view('products/form');
    }
    
    /**
     * Обробка форми редагування продукту
     *
     * @param int $id
     */
    public function update($id) {
        // Перевірка прав доступу
        if (!has_role(['admin', 'warehouse_manager'])) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки.');
            $this->redirect('products');
            return;
        }
        
        // Перевірка методу запиту
        if (!$this->isPost()) {
            $this->redirect('products/edit/' . $id);
            return;
        }
        
        // Перевірка CSRF-токена
        $this->validateCsrfToken();
        
        // Отримання продукту
        $product = $this->productModel->getById($id);
        
        if (!$product) {
            $this->setFlash('error', 'Продукт не знайдено.');
            $this->redirect('products');
            return;
        }
        
        // Отримання даних із форми
        $name = $this->input('name');
        $category_id = $this->input('category_id') ?: null;
        $description = $this->input('description');
        $price = $this->input('price');
        $stock_quantity = $this->input('stock_quantity', 0);
        $is_featured = $this->input('is_featured') ? 1 : 0;
        $is_active = $this->input('is_active') ? 1 : 0;
        
        // Валідація даних
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
        
        // Обробка завантаженого зображення
        $image = $product['image']; // За замовчуванням використовуємо поточне зображення
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $newImage = upload_file('image', 'products');
            
            if ($newImage === null) {
                $errors['image'] = 'Помилка при завантаженні зображення. Перевірте формат і розмір файлу.';
            } else {
                $image = $newImage;
            }
        }
        
        // Якщо є помилки, повертаємо користувача до форми
        if (!empty($errors)) {
            set_form_errors($errors);
            $this->redirect('products/edit/' . $id);
            return;
        }
        
        // Перевірка зміни кількості товару
        $stockDifference = $stock_quantity - $product['stock_quantity'];
        
        // Оновлення продукту
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
            // Запис руху товарів, якщо змінилася кількість
            if ($stockDifference != 0) {
                $inventoryMovementModel = new InventoryMovement();
                $movementData = [
                    'product_id' => $id,
                    'warehouse_id' => 1, // Використовуємо основний склад
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