<?php
// app/controllers/OrderController.php - Контроллер для работы с заказами

class OrderController extends BaseController {
    private $orderModel;
    private $userModel;
    private $productModel;
    
    public function __construct() {
        parent::__construct();
        $this->orderModel = new Order();
        $this->userModel = new User();
        $this->productModel = new Product();
    }
    
    /**
     * Отображение списка заказов
     */
    public function index() {
        // Проверка авторизации и доступа
        if (!is_logged_in()) {
            $this->redirect('auth/login');
            return;
        }
        
        // Получение роли пользователя
        $role = get_current_user_role();
        $userId = get_current_user_id();
        
        // Получение параметров фильтрации и пагинации
        $page = intval($this->input('page', 1));
        
        $filters = [
            'status' => $this->input('status'),
            'customer_id' => $this->input('customer_id'),
            'date_from' => $this->input('date_from'),
            'date_to' => $this->input('date_to'),
            'order_number' => $this->input('order_number')
        ];
        
        // Если пользователь клиент, показываем только его заказы
        if ($role === 'customer') {
            $filters['customer_id'] = $userId;
        }
        
        // Получение заказов с пагинацией и фильтрацией
        $ordersData = $this->orderModel->getFiltered($filters, $page, ITEMS_PER_PAGE);
        
        // Передача данных в представление
        $this->data['orders'] = $ordersData['items'];
        $this->data['pagination'] = [
            'current_page' => $ordersData['current_page'],
            'per_page' => $ordersData['per_page'],
            'total_items' => $ordersData['total_items'],
            'total_pages' => $ordersData['total_pages']
        ];
        $this->data['filters'] = $filters;
        
        // Если пользователь админ или менеджер продаж, получаем список клиентов для фильтра
        if (has_role(['admin', 'sales_manager'])) {
            $this->data['customers'] = $this->userModel->getAllCustomers();
        }
        
        // Выбор соответствующего представления в зависимости от роли
        if (has_role('admin')) {
            $this->view('admin/orders/index');
        } elseif (has_role('sales_manager')) {
            $this->view('sales/orders/index');
        } elseif (has_role('warehouse_manager')) {
            $this->view('warehouse/orders/index');
        } else {
            $this->view('customer/orders/index');
        }
    }

    /**
     * Обробка замовлення (для менеджера складу)
     *
     * @param int $id
     */
    public function process($id) {
        // Перевірка прав доступу
        if (!has_role(['admin', 'warehouse_manager'])) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки');
            $this->redirect('orders');
            return;
        }
        
        // Отримання замовлення
        $order = $this->orderModel->getWithCustomer($id);
        
        if (!$order) {
            $this->setFlash('error', 'Замовлення не знайдено');
            $this->redirect('orders');
            return;
        }
        
        // Перевірка статусу замовлення
        if ($order['status'] != 'pending') {
            $this->setFlash('error', 'Це замовлення вже обробляється або виконано');
            $this->redirect('orders/view/' . $id);
            return;
        }
        
        // Отримання товарів замовлення
        $orderItems = $this->orderModel->getOrderItems($id);
        
        // Передача даних у представлення
        $this->data['order'] = $order;
        $this->data['orderItems'] = $orderItems;
        
        $this->view('warehouse/orders/process');
    }

    /**
     * Завершення обробки замовлення
     *
     * @param int $id
     */
    public function completeProcessing($id) {
        // Перевірка прав доступу
        if (!has_role(['admin', 'warehouse_manager'])) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки');
            $this->redirect('orders');
            return;
        }
        
        // Перевірка методу запиту
        if (!$this->isPost()) {
            $this->redirect('orders/view/' . $id);
            return;
        }
        
        // Перевірка CSRF-токена
        $this->validateCsrfToken();
        
        // Отримання замовлення
        $order = $this->orderModel->getById($id);
        
        if (!$order) {
            $this->setFlash('error', 'Замовлення не знайдено');
            $this->redirect('orders');
            return;
        }
        
        // Зміна статусу замовлення на "processing"
        $result = $this->orderModel->updateStatus($id, 'processing');
        
        if ($result) {
            $this->setFlash('success', 'Замовлення успішно оброблено і готове до відвантаження');
            $this->redirect('orders/view/' . $id);
        } else {
            $this->setFlash('error', 'Помилка при зміні статусу замовлення');
            $this->redirect('orders/process/' . $id);
        }
    }

    /**
     * Відвантаження замовлення (для менеджера складу)
     *
     * @param int $id
     */
    public function ship($id) {
        // Перевірка прав доступу
        if (!has_role(['admin', 'warehouse_manager'])) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки');
            $this->redirect('orders');
            return;
        }
        
        // Отримання замовлення
        $order = $this->orderModel->getWithCustomer($id);
        
        if (!$order) {
            $this->setFlash('error', 'Замовлення не знайдено');
            $this->redirect('orders');
            return;
        }
        
        // Перевірка статусу замовлення
        if ($order['status'] != 'processing') {
            $this->setFlash('error', 'Це замовлення не готове до відвантаження');
            $this->redirect('orders/view/' . $id);
            return;
        }
        
        // Отримання товарів замовлення
        $orderItems = $this->orderModel->getOrderItems($id);
        
        // Передача даних у представлення
        $this->data['order'] = $order;
        $this->data['orderItems'] = $orderItems;
        
        $this->view('warehouse/orders/ship');
    }
    
    /**
     * Отображение детальной информации о заказе
     *
     * @param int $id
     */
    public function details($id, $data = []) {
        // Проверка авторизации
        if (!is_logged_in()) {
            $this->redirect('auth/login');
            return;
        }
        
        // Получение заказа
        $order = $this->orderModel->getWithCustomer($id);
        
        if (!$order) {
            $this->setFlash('error', 'Заказ не найден.');
            $this->redirect('orders');
            return;
        }
        
        // Проверка доступа (клиент может просматривать только свои заказы)
        if (has_role('customer') && $order['customer_id'] != get_current_user_id()) {
            $this->setFlash('error', 'У вас нет доступа к этому заказу.');
            $this->redirect('orders');
            return;
        }
        
        // Получение товаров заказа
        $orderItems = $this->orderModel->getOrderItems($id);
        
        // Передача данных в представление
        $this->data['order'] = $order;
        $this->data['orderItems'] = $orderItems;
        
        // Выбор соответствующего представления в зависимости от роли
        if (has_role('admin')) {
            $this->view('admin/orders/view');
        } elseif (has_role('sales_manager')) {
            $this->view('sales/orders/view');
        } elseif (has_role('warehouse_manager')) {
            $this->view('warehouse/orders/view');
        } else {
            $this->view('customer/orders/view');
        }
    }

    public function checkProduct() {
        $productId = $this->input('product_id');
        $product = $this->productModel->getById($productId);
        
        $response = [
            'valid' => false,
            'message' => 'Товар недоступен'
        ];
        
        if ($product && $product['is_active'] && $product['stock_quantity'] > 0) {
            $response = [
                'valid' => true,
                'product' => [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'stock' => $product['stock_quantity']
                ]
            ];
        }
        
        $this->json($response);
    }
/**
 * AJAX методи для роботи з кошиком
 */
public function cartAction() {
    // Перевірка авторизації
    if (!is_logged_in()) {
        $this->json(['error' => 'Unauthorized'], 401);
        return;
    }
    
    $action = $this->input('action');
    $productId = intval($this->input('product_id'));
    
    // Перевірка наявності продукту
    if ($productId > 0) {
        $product = $this->productModel->getById($productId);
        
        if (!$product || !$product['is_active'] || $product['stock_quantity'] <= 0) {
            $this->json(['error' => 'Продукт не знайдений або недоступний'], 404);
            return;
        }
    }
    
    switch ($action) {
        case 'add':
            $quantity = max(1, intval($this->input('quantity', 1)));
            
            // Перевірка доступної кількості
            if ($quantity > $product['stock_quantity']) {
                $quantity = $product['stock_quantity'];
            }
            
            cart()->addItem($product, $quantity);
            $this->json([
                'success' => true,
                'message' => 'Товар додано до кошика',
                'cart_count' => cart()->getTotalQuantity(),
                'cart_total' => cart()->getTotalPrice()
            ]);
            break;
            
        case 'update':
            $quantity = max(1, intval($this->input('quantity', 1)));
            cart()->updateQuantity($productId, $quantity);
            $this->json([
                'success' => true,
                'message' => 'Кількість оновлено',
                'cart_count' => cart()->getTotalQuantity(),
                'cart_total' => cart()->getTotalPrice()
            ]);
            break;
            
        case 'remove':
            cart()->removeItem($productId);
            $this->json([
                'success' => true,
                'message' => 'Товар видалено з кошика',
                'cart_count' => cart()->getTotalQuantity(),
                'cart_total' => cart()->getTotalPrice()
            ]);
            break;
            
        case 'clear':
            cart()->clear();
            $this->json([
                'success' => true,
                'message' => 'Кошик очищено',
                'cart_count' => 0,
                'cart_total' => 0
            ]);
            break;
            
        case 'get':
            $this->json([
                'success' => true,
                'items' => cart()->getItems(),
                'cart_count' => cart()->getTotalQuantity(),
                'cart_total' => cart()->getTotalPrice()
            ]);
            break;
            
        default:
            $this->json(['error' => 'Invalid action'], 400);
    }
}

    
   /**
     * Відображення форми створення замовлення
     */
    public function create() {
        // Перевірка авторизації та доступу (тільки адмін і менеджер продажів можуть створювати замовлення)
        if (!has_role(['admin', 'sales_manager', 'customer'])) {
            $this->setFlash('error', 'У вас нет доступа к этой странице.');
            $this->redirect('orders');
            return;
        }
        
        // Отримання списку продуктів
        $products = $this->productModel->getAllActive();
        
        // Якщо користувач адмін або менеджер продажів, отримуємо список клієнтів
        $customers = [];
        if (has_role(['admin', 'sales_manager'])) {
            $customers = $this->userModel->getAllCustomers();
        }
        
        // Якщо передано product_id через GET (додавання з каталогу)
        if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
            $productId = (int)$_GET['product_id'];
            $product = $this->productModel->getById($productId);
            
            if ($product && $product['is_active'] && $product['stock_quantity'] > 0) {
                // Додаємо товар до кошика
                cart()->addItem($product, 1);
            }
        }
        
        // Передача даних в представлення
        $this->data['products'] = $products;
        $this->data['customers'] = $customers;
        $this->data['cart_items'] = cart()->getItems();
        
        // Вибір відповідного представлення в залежності від ролі
        if (has_role(['admin', 'sales_manager'])) {
            $this->view('admin/orders/create');
        } else {
            $this->view('customer/orders/create');
        }
    }
    
    /**
 * Обработка формы создания заказа с поддержкой контейнеров
 */
public function store() {
    // Проверка авторизации
    if (!has_role(['admin', 'sales_manager', 'customer'])) {
        $this->json(['error' => 'У вас нет доступа к этой странице.'], 403);
        return;
    }
    
    // Проверка метода запроса
    if (!$this->isPost()) {
        $this->json(['error' => 'Неправильный метод запроса'], 405);
        return;
    }
    
    // Проверка CSRF-токена
    try {
        $this->validateCsrfToken();
    } catch (Exception $e) {
        $this->json(['error' => 'CSRF token validation failed'], 403);
        return;
    }
    
    // Получение данных из формы
    $customerId = has_role('customer') ? get_current_user_id() : $this->input('customer_id');
    $shippingAddress = $this->input('shipping_address');
    $paymentMethod = $this->input('payment_method');
    $notes = $this->input('notes');
    
    // Получение товаров из корзины
    $cartItemsJson = $this->input('cart_items', []);
    
    // Валидация данных
    $errors = [];
    
    if (empty($customerId)) {
        $errors['customer_id'] = 'Виберіть клієнта';
    }
    
    if (empty($shippingAddress)) {
        $errors['shipping_address'] = 'Введіть адресу доставки';
    }
    
    if (empty($paymentMethod)) {
        $errors['payment_method'] = 'Виберіть спосіб оплати';
    }
    
    if (empty($cartItemsJson)) {
        $errors['products'] = 'Додайте хоча б один товар до замовлення';
    }
    
    // Если есть ошибки, возвращаем их
    if (!empty($errors)) {
        $this->json(['errors' => $errors], 400);
        return;
    }
    
    // Парсинг товаров из корзины
    $orderItems = [];
    $totalAmount = 0;
    $productContainerModel = new ProductContainer();
    
    foreach ($cartItemsJson as $itemJson) {
        $item = json_decode($itemJson, true);
        
        if (!$item || !isset($item['product_id'])) {
            continue;
        }
        
        $productId = $item['product_id'];
        $containerId = $item['container_id'] ?? null;
        $quantity = intval($item['quantity'] ?? 1);
        $price = floatval($item['price'] ?? 0);
        $volume = floatval($item['volume'] ?? 1);
        
        // Валидация товара
        $product = $this->productModel->getById($productId);
        if (!$product || !$product['is_active']) {
            $errors['product_' . $productId] = 'Товар недоступний або неактивний';
            continue;
        }
        
        // Если используется контейнер
        if ($containerId && $containerId !== 'default') {
            $container = $productContainerModel->getById($containerId);
            
            if (!$container || !$container['is_active'] || $container['product_id'] != $productId) {
                $errors['container_' . $containerId] = 'Обраний об\'єм недоступний';
                continue;
            }
            
            if ($container['stock_quantity'] < $quantity) {
                $errors['quantity_' . $productId] = 'Недостатньо товару на складі. Доступно: ' . $container['stock_quantity'];
                continue;
            }
            
            // Проверяем соответствие цены и объема
            if (abs($container['price'] - $price) > 0.01 || abs($container['volume'] - $volume) > 0.01) {
                $errors['price_' . $productId] = 'Невідповідність ціни або об\'єму';
                continue;
            }
            
            $orderItems[] = [
                'product_id' => $productId,
                'container_id' => $containerId,
                'quantity' => $quantity,
                'price' => $container['price'],
                'volume' => $container['volume'],
                'warehouse_id' => 1
            ];
            
            $totalAmount += $container['price'] * $quantity;
            
        } else {
            // Базовый режим без контейнеров
            if ($product['stock_quantity'] < $quantity) {
                $errors['quantity_' . $productId] = 'Недостатньо товару на складі. Доступно: ' . $product['stock_quantity'];
                continue;
            }
            
            // Проверяем соответствие цены
            if (abs($product['price'] - $price) > 0.01) {
                $errors['price_' . $productId] = 'Невідповідність ціни';
                continue;
            }
            
            $orderItems[] = [
                'product_id' => $productId,
                'container_id' => null,
                'quantity' => $quantity,
                'price' => $product['price'],
                'volume' => 1,
                'warehouse_id' => 1
            ];
            
            $totalAmount += $product['price'] * $quantity;
        }
    }
    
    // Если есть ошибки после проверки товаров
    if (!empty($errors)) {
        $this->json(['errors' => $errors], 400);
        return;
    }
    
    if (empty($orderItems)) {
        $this->json(['errors' => ['products' => 'Не вдалося обробити товари з кошика']], 400);
        return;
    }
    
    try {
        // Создание заказа
        $orderData = [
            'customer_id' => $customerId,
            'status' => 'pending',
            'total_amount' => $totalAmount,
            'payment_method' => $paymentMethod,
            'shipping_address' => $shippingAddress,
            'notes' => $notes
        ];
        
        $orderId = $this->orderModel->createWithItems($orderData, $orderItems);
        
        if ($orderId) {
            // Обновляем остатки в контейнерах
            foreach ($orderItems as $item) {
                if (!empty($item['container_id'])) {
                    $productContainerModel->updateStock($item['container_id'], $item['quantity'], 'subtract');
                }
            }
            
            $this->json([
                'success' => true,
                'message' => 'Замовлення успішно створено',
                'order_id' => $orderId
            ]);
        } else {
            $this->json(['error' => 'Помилка при створенні замовлення'], 500);
        }
        
    } catch (Exception $e) {
        if (DEBUG_MODE) {
            error_log("Error in OrderController::store: " . $e->getMessage());
            error_log($e->getTraceAsString());
        }
        
        $this->json(['error' => 'Помилка при створенні замовлення: ' . $e->getMessage()], 500);
    }
}

    /**
     * Изменение статуса заказа
     *
     * @param int $id
     */
    public function updateStatus($id) {
        // Проверка авторизации и доступа (только админ и менеджер продаж могут изменять статус)
        if (!has_role(['admin', 'sales_manager', 'warehouse_manager'])) {
            $this->setFlash('error', 'У вас нет доступа к этой странице.');
            $this->redirect('orders');
            return;
        }
        
        // Проверка метода запроса
        if (!$this->isPost()) {
            $this->redirect('orders/view/' . $id);
            return;
        }
        
        // Проверка CSRF-токена
        $this->validateCsrfToken();
        
        // Получение данных из формы
        $status = $this->input('status');
        
        // Получение заказа
        $order = $this->orderModel->getById($id);
        
        if (!$order) {
            $this->setFlash('error', 'Заказ не найден.');
            $this->redirect('orders');
            return;
        }
        
        // Валидация статуса
        $availableStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $availableStatuses)) {
            $this->setFlash('error', 'Некорректный статус заказа.');
            $this->redirect('orders/view/' . $id);
            return;
        }
        
        // Обновление статуса
        $result = $this->orderModel->updateStatus($id, $status);
        
        if ($result) {
            $this->setFlash('success', 'Статус заказа успешно обновлен.');
        } else {
            $this->setFlash('error', 'Ошибка при обновлении статуса заказа.');
        }
        
        $this->redirect('orders/view/' . $id);
    }
    
    /**
     * Отмена заказа
     *
     * @param int $id
     */
    public function cancel($id) {
        // Проверка авторизации
        if (!is_logged_in()) {
            $this->redirect('auth/login');
            return;
        }
        
        // Получение заказа
        $order = $this->orderModel->getById($id);
        
        if (!$order) {
            $this->setFlash('error', 'Заказ не найден.');
            $this->redirect('orders');
            return;
        }
        
        // Проверка доступа (клиент может отменять только свои заказы)
        if (has_role('customer') && $order['customer_id'] != get_current_user_id()) {
            $this->setFlash('error', 'У вас нет доступа к этому заказу.');
            $this->redirect('orders');
            return;
        }
        
        // Проверка, можно ли отменить заказ
        if ($order['status'] == 'shipped' || $order['status'] == 'delivered' || $order['status'] == 'cancelled') {
            $this->setFlash('error', 'Невозможно отменить заказ в текущем статусе.');
            $this->redirect('orders/view/' . $id);
            return;
        }
        
        // Отмена заказа
        $result = $this->orderModel->updateStatus($id, 'cancelled');
        
        if ($result) {
            // Возврат товаров на склад
            $orderItems = $this->orderModel->getOrderItems($id);
            
            foreach ($orderItems as $item) {
                // Обновление количества товара на складе
                $this->productModel->updateStock($item['product_id'], $item['quantity']);
                
                // Запись движения товара
                $inventoryMovementModel = new InventoryMovement();
                $movementData = [
                    'product_id' => $item['product_id'],
                    'warehouse_id' => 1, // Предполагаем, что используется основной склад
                    'quantity' => $item['quantity'],
                    'movement_type' => 'incoming',
                    'reference_id' => $id,
                    'reference_type' => 'order_cancel',
                    'notes' => 'Возврат товара при отмене заказа',
                    'created_by' => get_current_user_id()
                ];
                
                $inventoryMovementModel->create($movementData);
            }
            
            $this->setFlash('success', 'Заказ успешно отменен.');
        } else {
            $this->setFlash('error', 'Ошибка при отмене заказа.');
        }
        
        $this->redirect('orders/view/' . $id);
    }
    
    /**
     * Получение продуктов для автозаполнения
     */
    public function getProductsJson() {
        // Проверка авторизации
        if (!is_logged_in()) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }
        
        // Получение параметра поиска
        $keyword = $this->input('term');
        
        // Поиск продуктов
        $products = $this->productModel->search($keyword);
        
        // Форматирование результатов
        $result = [];
        
        foreach ($products as $product) {
            $result[] = [
                'id' => $product['id'],
                'label' => $product['name'],
                'value' => $product['name'],
                'price' => $product['price'],
                'stock' => $product['stock_quantity'],
                'image' => $product['image'] ? upload_url($product['image']) : asset_url('images/no-image.jpg')
            ];
        }
        
        // Отправка результатов в формате JSON
        $this->json($result);
    }
    
    /**
     * Печать заказа
     *
     * @param int $id
     */
    public function print($id) {
        // Проверка авторизации
        if (!is_logged_in()) {
            $this->redirect('auth/login');
            return;
        }
        
        // Получение заказа
        $order = $this->orderModel->getWithCustomer($id);
        
        if (!$order) {
            $this->setFlash('error', 'Заказ не найден.');
            $this->redirect('orders');
            return;
        }
        
        // Проверка доступа (клиент может просматривать только свои заказы)
        if (has_role('customer') && $order['customer_id'] != get_current_user_id()) {
            $this->setFlash('error', 'У вас нет доступа к этому заказу.');
            $this->redirect('orders');
            return;
        }
        
        // Получение товаров заказа
        $orderItems = $this->orderModel->getOrderItems($id);
        
        // Передача данных в представление
        $this->data['order'] = $order;
        $this->data['orderItems'] = $orderItems;
        
        // Загрузка представления для печати
        $this->view('orders/print');
    }

    /**
 * Добавление товара в корзину (обновленный метод для работы с контейнерами)
 */
public function addToCart() {
    // Проверка авторизации
    if (!is_logged_in()) {
        $this->json(['error' => 'Unauthorized'], 401);
        return;
    }
    
    // Проверка метода запроса
    if (!$this->isPost()) {
        $this->redirect('products');
        return;
    }
    
    // Проверка CSRF-токена
    try {
        $this->validateCsrfToken();
    } catch (Exception $e) {
        $this->setFlash('error', 'Помилка безпеки. Спробуйте ще раз.');
        $this->redirect('products');
        return;
    }
    
    $productId = intval($this->input('product_id'));
    $containerId = $this->input('container_id');
    $quantity = max(1, intval($this->input('quantity', 1)));
    
    if (!$productId) {
        $this->setFlash('error', 'Товар не вказано');
        $this->redirect('products');
        return;
    }
    
    // Получение информации о продукте
    $product = $this->productModel->getById($productId);
    if (!$product || !$product['is_active']) {
        $this->setFlash('error', 'Товар недоступний');
        $this->redirect('products');
        return;
    }
    
    // Если указан ID контейнера, работаем с контейнерами
    if ($containerId && !str_starts_with($containerId, 'default_')) {
        $productContainerModel = new ProductContainer();
        $container = $productContainerModel->getById($containerId);
        
        if (!$container || !$container['is_active'] || $container['product_id'] != $productId) {
            $this->setFlash('error', 'Обраний об\'єм недоступний');
            $this->redirect('products/view/' . $productId);
            return;
        }
        
        if ($container['stock_quantity'] < $quantity) {
            $this->setFlash('error', 'Недостатньо товару на складі. Доступно: ' . $container['stock_quantity']);
            $this->redirect('products/view/' . $productId);
            return;
        }
        
        // Добавляем в корзину с данными контейнера
        cart()->addItem([
            'id' => $productId,
            'container_id' => $containerId,
            'name' => $product['name'],
            'price' => $container['price'],
            'volume' => $container['volume'],
            'image' => $product['image']
        ], $quantity);
        
    } else {
        // Работаем без контейнеров (базовый режим)
        if ($product['stock_quantity'] < $quantity) {
            $this->setFlash('error', 'Недостатньо товару на складі. Доступно: ' . $product['stock_quantity']);
            $this->redirect('products/view/' . $productId);
            return;
        }
        
        // Добавляем в корзину базовый продукт
        cart()->addItem([
            'id' => $productId,
            'container_id' => null,
            'name' => $product['name'],
            'price' => $product['price'],
            'volume' => 1, // Базовый объем
            'image' => $product['image']
        ], $quantity);
    }
    
    $this->setFlash('success', 'Товар додано до кошика');
    $this->redirect('products/view/' . $productId);
}

/**
 * Проверка доступности товара с контейнером
 */
public function checkProductAvailability() {
    $productId = $this->input('product_id');
    $containerId = $this->input('container_id');
    $quantity = intval($this->input('quantity', 1));
    
    if (!$productId) {
        $this->json(['error' => 'Не вказано ID продукту'], 400);
        return;
    }
    
    $product = $this->productModel->getById($productId);
    if (!$product || !$product['is_active']) {
        $this->json(['error' => 'Продукт недоступний'], 404);
        return;
    }
    
    // Если работаем с контейнерами
    if ($containerId && !str_starts_with($containerId, 'default_')) {
        $productContainerModel = new ProductContainer();
        $container = $productContainerModel->getById($containerId);
        
        if (!$container || !$container['is_active'] || $container['product_id'] != $productId) {
            $this->json(['error' => 'Контейнер недоступний'], 404);
            return;
        }
        
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
        
    } else {
        // Базовый режим без контейнеров
        if ($product['stock_quantity'] < $quantity) {
            $this->json([
                'success' => false,
                'error' => 'Недостатньо товару на складі',
                'available' => $product['stock_quantity']
            ], 400);
            return;
        }
        
        $this->json([
            'success' => true,
            'available' => true,
            'stock' => $product['stock_quantity'],
            'price' => $product['price'],
            'volume' => 1
        ]);
    }
}

/**
 * Обновленный метод создания заказа с поддержкой контейнеров
 */
public function storeWithContainers() {
    // Проверка авторизации
    if (!has_role(['admin', 'sales_manager', 'customer'])) {
        $this->setFlash('error', 'У вас нет доступа к этой странице.');
        $this->redirect('orders');
        return;
    }
    
    // Проверка метода запроса
    if (!$this->isPost()) {
        $this->redirect('orders/create');
        return;
    }
    
    // Проверка CSRF-токена
    $this->validateCsrfToken();
    
    // Получение данных из формы
    $customerId = has_role('customer') ? get_current_user_id() : $this->input('customer_id');
    $shippingAddress = $this->input('shipping_address');
    $paymentMethod = $this->input('payment_method');
    $notes = $this->input('notes');
    
    // Валидация данных
    $errors = [];
    
    if (empty($customerId)) {
        $errors['customer_id'] = 'Виберіть клієнта';
    }
    
    if (empty($shippingAddress)) {
        $errors['shipping_address'] = 'Введіть адресу доставки';
    }
    
    if (empty($paymentMethod)) {
        $errors['payment_method'] = 'Виберіть спосіб оплати';
    }
    
    // Получение товаров из корзины
    $cartItems = cart()->getItems();
    
    if (empty($cartItems)) {
        $errors['products'] = 'Додайте хоча б один товар до замовлення';
    }
    
    // Если есть ошибки, возвращаемся к форме
    if (!empty($errors)) {
        set_form_errors($errors);
        $this->redirect('orders/create');
        return;
    }
    
    // Подготовка товаров для заказа
    $orderItems = [];
    $totalAmount = 0;
    $productContainerModel = new ProductContainer();
    
    foreach ($cartItems as $item) {
        // Если используется контейнер
        if (!empty($item['container_id'])) {
            $container = $productContainerModel->getById($item['container_id']);
            
            if (!$container) {
                $errors['container_' . $item['container_id']] = 'Контейнер не знайдено';
                continue;
            }
            
            // Проверка количества в контейнере
            if ($item['quantity'] > $container['stock_quantity']) {
                $errors['quantity_' . $item['id']] = 'Недостатньо товару на складі. В наявності: ' . $container['stock_quantity'];
                continue;
            }
            
            $orderItems[] = [
                'product_id' => $item['id'],
                'container_id' => $item['container_id'],
                'quantity' => $item['quantity'],
                'price' => $container['price'],
                'volume' => $container['volume'],
                'warehouse_id' => 1
            ];
            
            $totalAmount += $container['price'] * $item['quantity'];
            
        } else {
            // Базовый режим без контейнеров
            $product = $this->productModel->getById($item['id']);
            
            if (!$product) {
                $errors['product_' . $item['id']] = 'Продукт не знайдено';
                continue;
            }
            
            if ($item['quantity'] > $product['stock_quantity']) {
                $errors['quantity_' . $item['id']] = 'Недостатньо товару на складі. В наявності: ' . $product['stock_quantity'];
                continue;
            }
            
            $orderItems[] = [
                'product_id' => $item['id'],
                'container_id' => null,
                'quantity' => $item['quantity'],
                'price' => $product['price'],
                'volume' => 1,
                'warehouse_id' => 1
            ];
            
            $totalAmount += $product['price'] * $item['quantity'];
        }
    }
    
    // Если есть ошибки после проверки товаров
    if (!empty($errors)) {
        set_form_errors($errors);
        $this->redirect('orders/create');
        return;
    }
    
    // Создание заказа
    $orderData = [
        'customer_id' => $customerId,
        'status' => 'pending',
        'total_amount' => $totalAmount,
        'payment_method' => $paymentMethod,
        'shipping_address' => $shippingAddress,
        'notes' => $notes
    ];
    
    $orderId = $this->orderModel->createWithItems($orderData, $orderItems);
    
    if ($orderId) {
        // Обновляем остатки в контейнерах
        foreach ($orderItems as $item) {
            if (!empty($item['container_id'])) {
                $productContainerModel->updateStock($item['container_id'], $item['quantity'], 'subtract');
            }
        }
        
        // Очищаем корзину
        cart()->clear();
        
        $this->setFlash('success', 'Замовлення успішно створено.');
        $this->redirect('orders/view/' . $orderId);
    } else {
        $this->setFlash('error', 'Помилка при створенні замовлення.');
        $this->redirect('orders/create');
    }
}


public function getProductWithContainers() {
    $productId = $this->input('product_id');
    
    if (!$productId) {
        $this->json(['error' => 'Не вказано ID продукту'], 400);
        return;
    }
    
    // Получение информации о продукте
    $product = $this->productModel->getWithCategory($productId);
    
    if (!$product || !$product['is_active']) {
        $this->json(['error' => 'Товар не знайдено або неактивний'], 404);
        return;
    }
    
    // Получение контейнеров для продукта
    $productContainerModel = new ProductContainer();
    $containers = $productContainerModel->getByProductId($productId);
    
    // Если контейнеров нет, создаем базовый
    if (empty($containers)) {
        $containers = [[
            'id' => 'default',
            'product_id' => $productId,
            'volume' => 1,
            'price' => $product['price'],
            'stock_quantity' => $product['stock_quantity'],
            'is_active' => $product['is_active']
        ]];
    }
    
    $response = [
        'product' => $product,
        'containers' => $containers
    ];
    
    $this->json($response);
}

/**
 * Проверка доступности товара/контейнера (AJAX)
 */
public function checkAvailability() {
    $productId = $this->input('product_id');
    $containerId = $this->input('container_id');
    $quantity = intval($this->input('quantity', 1));
    
    if (!$productId) {
        $this->json(['error' => 'Не вказано ID продукту'], 400);
        return;
    }
    
    $product = $this->productModel->getById($productId);
    if (!$product || !$product['is_active']) {
        $this->json(['available' => false, 'reason' => 'Товар недоступний'], 200);
        return;
    }
    
    if ($containerId && $containerId !== 'default') {
        // Проверка контейнера
        $productContainerModel = new ProductContainer();
        $container = $productContainerModel->getById($containerId);
        
        if (!$container || !$container['is_active'] || $container['product_id'] != $productId) {
            $this->json(['available' => false, 'reason' => 'Контейнер недоступний'], 200);
            return;
        }
        
        if ($container['stock_quantity'] < $quantity) {
            $this->json([
                'available' => false, 
                'reason' => 'Недостатньо на складі',
                'available_quantity' => $container['stock_quantity']
            ], 200);
            return;
        }
        
        $this->json([
            'available' => true,
            'stock_quantity' => $container['stock_quantity'],
            'price' => $container['price'],
            'volume' => $container['volume']
        ]);
        
    } else {
        // Проверка базового продукта
        if ($product['stock_quantity'] < $quantity) {
            $this->json([
                'available' => false, 
                'reason' => 'Недостатньо на складі',
                'available_quantity' => $product['stock_quantity']
            ], 200);
            return;
        }
        
        $this->json([
            'available' => true,
            'stock_quantity' => $product['stock_quantity'],
            'price' => $product['price'],
            'volume' => 1
        ]);
    }
}

/**
 * Получение рекомендованных товаров для страницы создания заказа
 */
public function getRecommendedProducts() {
    $limit = intval($this->input('limit', 6));
    $categoryId = $this->input('category_id');
    
    $filters = ['is_featured' => 1, 'is_active' => 1];
    if ($categoryId) {
        $filters['category_id'] = $categoryId;
    }
    
    $products = $this->productModel->getFiltered(1, $limit, $filters)['items'];
    
    // Для каждого продукта получаем минимальную цену
    $productContainerModel = new ProductContainer();
    
    foreach ($products as &$product) {
        $containers = $productContainerModel->getByProductId($product['id']);
        if (!empty($containers)) {
            $activePrices = array_column(
                array_filter($containers, function($c) { 
                    return $c['is_active'] && $c['stock_quantity'] > 0; 
                }), 
                'price'
            );
            $product['min_price'] = !empty($activePrices) ? min($activePrices) : $product['price'];
        } else {
            $product['min_price'] = $product['price'];
        }
    }
    
    $this->json(['products' => $products]);
}

/**
 * Валидация данных корзины (AJAX)
 */
public function validateCart() {
    $cartItemsJson = $this->input('cart_items', []);
    
    if (empty($cartItemsJson)) {
        $this->json(['valid' => false, 'errors' => ['Кошик порожній']], 200);
        return;
    }
    
    $errors = [];
    $totalAmount = 0;
    $productContainerModel = new ProductContainer();
    
    foreach ($cartItemsJson as $itemJson) {
        $item = json_decode($itemJson, true);
        
        if (!$item || !isset($item['product_id'])) {
            $errors[] = 'Некоректні дані товару';
            continue;
        }
        
        $productId = $item['product_id'];
        $containerId = $item['container_id'] ?? null;
        $quantity = intval($item['quantity'] ?? 1);
        
        // Проверка продукта
        $product = $this->productModel->getById($productId);
        if (!$product || !$product['is_active']) {
            $errors[] = "Товар '{$product['name']}' недоступний";
            continue;
        }
        
        // Проверка контейнера
        if ($containerId && $containerId !== 'default') {
            $container = $productContainerModel->getById($containerId);
            
            if (!$container || !$container['is_active']) {
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
    }
    
    $this->json([
        'valid' => empty($errors),
        'errors' => $errors,
        'total_amount' => $totalAmount,
        'items_count' => count($cartItemsJson)
    ]);
}
}