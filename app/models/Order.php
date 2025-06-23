<?php
// app/models/Order.php - Модель для работы с заказами

class Order extends BaseModel {
    protected $table = 'orders';
    protected $fillable = [
        'customer_id', 'order_number', 'status', 'total_amount', 
        'payment_method', 'shipping_address', 'notes'
    ];
    
    /**
     * Создание нового заказа с товарами
     *
     * @param array $orderData
     * @param array $items
     * @return int|bool
     */
    public function createWithItems($orderData, $orderItems) {
        try {
            // Начинаем транзакцию
            $this->db->beginTransaction();
            
            // Генерация номера заказа
            $orderData['order_number'] = $this->generateOrderNumber();
            
            // Создание заказа
            $orderId = $this->create($orderData);
            
            if (!$orderId) {
                throw new Exception('Ошибка создания заказа');
            }
            
            // Добавление товаров к заказу
            foreach ($orderItems as $item) {
                $itemData = [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'container_id' => $item['container_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'volume' => $item['volume'] ?? 1,
                    'warehouse_id' => $item['warehouse_id'] ?? 1
                ];
                
                $sql = 'INSERT INTO order_items (order_id, product_id, container_id, quantity, price, volume, warehouse_id) 
                        VALUES (:order_id, :product_id, :container_id, :quantity, :price, :volume, :warehouse_id)';
                
                if (!$this->db->query($sql, $itemData)) {
                    throw new Exception('Ошибка добавления товара к заказу');
                }
                
                // Если используется контейнер, НЕ обновляем основную таблицу products
                // Обновление stock_quantity в product_containers происходит отдельно
                if (empty($item['container_id'])) {
                    // Только если НЕ используется контейнер, обновляем основную таблицу
                    $productModel = new Product();
                    if (!$productModel->updateStock($item['product_id'], -$item['quantity'])) {
                        throw new Exception('Ошибка обновления остатков товара');
                    }
                }
                
                // Создание записи о движении товара
                $inventoryMovementModel = new InventoryMovement();
                $movementData = [
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $item['warehouse_id'],
                    'quantity' => -$item['quantity'],
                    'movement_type' => 'outgoing',
                    'reference_id' => $orderId,
                    'reference_type' => 'order',
                    'notes' => 'Списание по заказу ' . $orderData['order_number'] . 
                              (!empty($item['container_id']) ? ' (тара ' . $item['volume'] . ' л)' : ''),
                    'created_by' => get_current_user_id()
                ];
                
                $inventoryMovementModel->create($movementData);
            }
            
            // Обновление аналитики продаж
            $this->updateSalesAnalytics($orderId);
            
            // Подтверждаем транзакцию
            $this->db->commit();
            
            return $orderId;
            
        } catch (Exception $e) {
            // Откатываем транзакцию в случае ошибки
            $this->db->rollBack();
            
            if (DEBUG_MODE) {
                error_log("Error in createWithItems: " . $e->getMessage());
                error_log($e->getTraceAsString());
            }
            
            return false;
        }
    }
    
    /**
     * Генерация номера заказа
     *
     * @return string
     */
    private function generateOrderNumber() {
        $prefix = 'ORD-';
        $date = date('Ymd');
        
        // Получение максимального номера заказа за день
        $sql = "SELECT MAX(order_number) FROM orders WHERE order_number LIKE ?";
        $maxNumber = $this->db->getValue($sql, [$prefix . $date . '%']);
        
        if ($maxNumber) {
            // Извлечение последнего числа и увеличение на 1
            $lastNumber = intval(substr($maxNumber, -3));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        // Форматирование номера с ведущими нулями
        return $prefix . $date . sprintf('%03d', $nextNumber);
    }
    
    /**
     * Обновление статуса заказа
     *
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus($id, $status) {
        $sql = 'UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?';
        $this->db->query($sql, [$status, $id]);
        
        return true;
    }
    
    /**
     * Получение заказа с информацией о клиенте
     *
     * @param int $id
     * @return array|null
     */
    public function getWithCustomer($id) {
        $sql = 'SELECT o.*, u.first_name, u.last_name, u.email, u.phone 
                FROM orders o 
                JOIN users u ON o.customer_id = u.id 
                WHERE o.id = ?';
        
        return $this->db->getOne($sql, [$id]);
    }
    
    /**
     * Получение товаров заказа
     *
     * @param int $orderId
     * @return array
     */
    public function getOrderItems($orderId) {
        $sql = 'SELECT oi.*, p.name as product_name, p.image, pc.volume as container_volume
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                LEFT JOIN product_containers pc ON oi.container_id = pc.id
                WHERE oi.order_id = ?';
        
        $items = $this->db->getAll($sql, [$orderId]);
        
        // Для совместимости, если volume не указан, берем из контейнера или устанавливаем 1
        foreach ($items as &$item) {
            if (empty($item['volume'])) {
                $item['volume'] = $item['container_volume'] ?? 1;
            }
        }
        
        return $items;
    }
    
    /**
     * Получение заказов клиента
     *
     * @param int $customerId
     * @return array
     */
    public function getCustomerOrders($customerId) {
        return $this->where('customer_id = ? ORDER BY created_at DESC', [$customerId]);
    }
    
    /**
     * Получение отфильтрованных заказов
     *
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getFiltered($filters = [], $page = 1, $perPage = ITEMS_PER_PAGE) {
        $conditions = [];
        $params = [];
        
        // Фильтр по статусу
        if (!empty($filters['status'])) {
            $conditions[] = 'o.status = ?';
            $params[] = $filters['status'];
        }
        
        // Фильтр по клиенту
        if (!empty($filters['customer_id'])) {
            $conditions[] = 'o.customer_id = ?';
            $params[] = $filters['customer_id'];
        }
        
        // Фильтр по дате (с)
        if (!empty($filters['date_from'])) {
            $conditions[] = 'DATE(o.created_at) >= ?';
            $params[] = $filters['date_from'];
        }
        
        // Фильтр по дате (по)
        if (!empty($filters['date_to'])) {
            $conditions[] = 'DATE(o.created_at) <= ?';
            $params[] = $filters['date_to'];
        }
        
        // Поиск по номеру заказа
        if (!empty($filters['order_number'])) {
            $conditions[] = 'o.order_number LIKE ?';
            $params[] = '%' . $filters['order_number'] . '%';
        }
        
        // Формирование условия
        $whereClause = '';
        if (!empty($conditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }
        
        // SQL-запрос для получения заказов с данными о клиенте
        $sql = 'SELECT o.*, u.first_name, u.last_name 
        FROM orders o 
        JOIN users u ON o.customer_id = u.id 
        ' . $whereClause . ' 
        ORDER BY o.created_at DESC';

    // Пагинация с использованием нового метода
    return $this->paginateQuery($sql, $params, $page, $perPage);
    }
    
    /**
     * Обновление аналитики продаж
     *
     * @param array $items
     * @return bool
     */
    private function updateSalesAnalytics($orderId) {
        // Получаем информацию о заказе
        $order = $this->getById($orderId);
        if (!$order) {
            return;
        }
        
        // Получаем товары заказа
        $orderItems = $this->getOrderItems($orderId);
        
        // Обновляем аналитику для каждого товара
        foreach ($orderItems as $item) {
            $analyticsData = [
                'date' => date('Y-m-d'),
                'product_id' => $item['product_id'],
                'quantity_sold' => $item['quantity'],
                'revenue' => $item['price'] * $item['quantity'],
                'cost' => ($item['price'] * $item['quantity']) * 0.5, // Примерная себестоимость 50%
                'profit' => ($item['price'] * $item['quantity']) * 0.5
            ];
            
            // Проверяем, есть ли уже запись за сегодня
            $sql = 'SELECT id FROM sales_analytics WHERE date = ? AND product_id = ?';
            $existing = $this->db->getOne($sql, [$analyticsData['date'], $analyticsData['product_id']]);
            
            if ($existing) {
                // Обновляем существующую запись
                $sql = 'UPDATE sales_analytics 
                        SET quantity_sold = quantity_sold + ?, 
                            revenue = revenue + ?, 
                            cost = cost + ?, 
                            profit = profit + ?
                        WHERE id = ?';
                
                $this->db->query($sql, [
                    $analyticsData['quantity_sold'],
                    $analyticsData['revenue'],
                    $analyticsData['cost'],
                    $analyticsData['profit'],
                    $existing['id']
                ]);
            } else {
                // Создаем новую запись
                $sql = 'INSERT INTO sales_analytics (date, product_id, quantity_sold, revenue, cost, profit) 
                        VALUES (:date, :product_id, :quantity_sold, :revenue, :cost, :profit)';
                
                $this->db->query($sql, $analyticsData);
            }
        }
    }
    
    /**
     * Получение статистики заказов
     *
     * @param string $period
     * @return array
     */
    public function getOrderStats($period = 'month') {
        $data = [];
        
        switch ($period) {
            case 'week':
                $interval = 'INTERVAL 7 DAY';
                break;
            case 'month':
                $interval = 'INTERVAL 1 MONTH';
                break;
            case 'quarter':
                $interval = 'INTERVAL 3 MONTH';
                break;
            case 'year':
                $interval = 'INTERVAL 1 YEAR';
                break;
            default:
                $interval = 'INTERVAL 1 MONTH';
        }
        
        // Общее количество заказов
        $sql = 'SELECT COUNT(*) FROM orders WHERE created_at >= DATE_SUB(CURDATE(), ' . $interval . ')';
        $data['totalOrders'] = $this->db->getValue($sql);
        
        // Сумма заказов
        $sql = 'SELECT SUM(total_amount) FROM orders WHERE created_at >= DATE_SUB(CURDATE(), ' . $interval . ')';
        $data['totalAmount'] = $this->db->getValue($sql) ?: 0;
        
        // Заказы по статусам
        $sql = 'SELECT status, COUNT(*) as count 
                FROM orders 
                WHERE created_at >= DATE_SUB(CURDATE(), ' . $interval . ') 
                GROUP BY status';
        $data['ordersByStatus'] = $this->db->getAll($sql);
        
        // Динамика заказов по дням
        $sql = 'SELECT DATE(created_at) as date, COUNT(*) as count, SUM(total_amount) as amount 
                FROM orders 
                WHERE created_at >= DATE_SUB(CURDATE(), ' . $interval . ') 
                GROUP BY DATE(created_at) 
                ORDER BY date';
        $data['ordersByDate'] = $this->db->getAll($sql);
        
        return $data;
    }
}