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
 * Обробка форми створення замовлення
 */
public function store() {
    // Перевірка авторизації та доступу
    if (!has_role(['admin', 'sales_manager', 'customer'])) {
        $this->setFlash('error', 'У вас нет доступа к этой странице.');
        $this->redirect('orders');
        return;
    }
    
    // Перевірка методу запиту
    if (!$this->isPost()) {
        $this->redirect('orders/create');
        return;
    }
    
    // Перевірка CSRF-токена
    $this->validateCsrfToken();
    
    // Отримання даних із форми
    $customerId = has_role('customer') ? get_current_user_id() : $this->input('customer_id');
    $shippingAddress = $this->input('shipping_address');
    $paymentMethod = $this->input('payment_method');
    $notes = $this->input('notes');
    
    // Валідація даних
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
    
    // Отримання товарів з кошика
    $cartItems = cart()->getItems();
    
    if (empty($cartItems)) {
        $errors['products'] = 'Додайте хоча б один товар до замовлення';
    }
    
    // Якщо є помилки, повертаємось до форми
    if (!empty($errors)) {
        set_form_errors($errors);
        $this->redirect('orders/create');
        return;
    }
    
    // Підготовка товарів для замовлення
    $orderItems = [];
    $totalAmount = 0;
    
    foreach ($cartItems as $item) {
        // Перевірка наявності на складі
        $product = $this->productModel->getById($item['id']);
        
        if (!$product) {
            $errors['product_' . $item['id']] = 'Продукт не знайдено';
            continue;
        }
        
        // Перевірка кількості
        if ($item['quantity'] > $product['stock_quantity']) {
            $errors['quantity_' . $item['id']] = 'Недостатньо товару на складі. В наявності: ' . $product['stock_quantity'];
            continue;
        }
        
        // Додавання товару до замовлення
        $orderItems[] = [
            'product_id' => $item['id'],
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'warehouse_id' => 1 // Використовуємо основний склад
        ];
        
        // Розрахунок загальної суми
        $totalAmount += $item['price'] * $item['quantity'];
    }
    
    // Якщо є помилки, повертаємось до форми
    if (!empty($errors)) {
        set_form_errors($errors);
        $this->redirect('orders/create');
        return;
    }
    
    // Створення замовлення
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
        // Очищаємо кошик після створення замовлення
        cart()->clear();
        
        $this->setFlash('success', 'Замовлення успішно створено.');
        $this->redirect('orders/view/' . $orderId);
    } else {
        $this->setFlash('error', 'Помилка при створенні замовлення.');
        $this->redirect('orders/create');
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
}