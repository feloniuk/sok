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
    
    /**
     * Отображение формы создания заказа
     */
    public function create() {
        // Проверка авторизации и доступа (только админ и менеджер продаж могут создавать заказы)
        if (!has_role(['admin', 'sales_manager', 'customer'])) {
            $this->setFlash('error', 'У вас нет доступа к этой странице.');
            $this->redirect('orders');
            return;
        }
        
        // Получение списка продуктов
        $products = $this->productModel->getAllActive();
        
        // Если пользователь админ или менеджер продаж, получаем список клиентов
        $customers = [];
        if (has_role(['admin', 'sales_manager'])) {
            $customers = $this->userModel->getAllCustomers();
        }
        
        // Передача данных в представление
        $this->data['products'] = $products;
        $this->data['customers'] = $customers;
        
        // Выбор соответствующего представления в зависимости от роли
        if (has_role(['admin', 'sales_manager'])) {
            $this->view('admin/orders/create');
        } else {
            $this->view('customer/orders/create');
        }
    }
    
    /**
     * Обработка формы создания заказа
     */
    public function store() {
        // Проверка авторизации и доступа
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
        
        // Получение товаров заказа
        $productIds = $_POST['product_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $prices = $_POST['price'] ?? [];
        
        // Валидация данных
        $errors = [];
        
        if (empty($customerId)) {
            $errors['customer_id'] = 'Выберите клиента';
        }
        
        if (empty($shippingAddress)) {
            $errors['shipping_address'] = 'Введите адрес доставки';
        }
        
        if (empty($paymentMethod)) {
            $errors['payment_method'] = 'Выберите способ оплаты';
        }
        
        if (empty($productIds)) {
            $errors['products'] = 'Добавьте хотя бы один товар в заказ';
        }
        
        // Проверка товаров
        $orderItems = [];
        $totalAmount = 0;
        
        foreach ($productIds as $index => $productId) {
            if (!isset($quantities[$index]) || $quantities[$index] <= 0) {
                $errors['quantity_' . $index] = 'Количество должно быть положительным числом';
                continue;
            }
            
            // Получение информации о продукте
            $product = $this->productModel->getById($productId);
            
            if (!$product) {
                $errors['product_' . $index] = 'Продукт не найден';
                continue;
            }
            
            // Проверка наличия на складе
            if ($quantities[$index] > $product['stock_quantity']) {
                $errors['quantity_' . $index] = 'Недостаточно товара на складе. В наличии: ' . $product['stock_quantity'];
                continue;
            }
            
            // Определение цены
            $price = isset($prices[$index]) && is_numeric($prices[$index]) ? $prices[$index] : $product['price'];
            
            // Добавление товара в заказ
            $orderItems[] = [
                'product_id' => $productId,
                'quantity' => $quantities[$index],
                'price' => $price,
                'warehouse_id' => 1 // Предполагаем, что используется основной склад
            ];
            
            // Расчет общей суммы
            $totalAmount += $price * $quantities[$index];
        }
        
        // Если есть ошибки, возвращаемся на форму
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
            $this->setFlash('success', 'Заказ успешно создан.');
            $this->redirect('orders/view/' . $orderId);
        } else {
            $this->setFlash('error', 'Ошибка при создании заказа.');
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