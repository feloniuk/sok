<?php
// app/controllers/ReportController.php - Контроллер для генерации отчетов

class ReportController extends BaseController {
    private $userModel;
    private $productModel;
    private $orderModel;
    private $categoryModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->categoryModel = new Category();
    }
    
    /**
     * Главная страница отчетов
     */
    public function index() {
        // Проверка прав доступа
        if (!has_role(['admin', 'sales_manager'])) {
            $this->setFlash('error', 'У вас нет доступа к этой странице.');
            $this->redirect('dashboard');
            return;
        }
        
        $this->view('reports/index');
    }
    
    /**
     * Отчет по продажам
     */
    public function sales() {
        // Проверка прав доступа
        if (!has_role(['admin', 'sales_manager'])) {
            $this->setFlash('error', 'У вас нет доступа к этой странице.');
            $this->redirect('dashboard');
            return;
        }
        
        // Получение параметров фильтрации
        $filter = [
            'period' => $this->input('period', 'month'),
            'start_date' => $this->input('start_date', date('Y-m-d', strtotime('-1 month'))),
            'end_date' => $this->input('end_date', date('Y-m-d')),
            'category_id' => $this->input('category_id')
        ];
        
        // Получение данных для отчета
        $salesData = $this->getSalesData($filter);
        
        // Получение категорий для фильтра
        $categories = $this->categoryModel->getAll();
        
        // Передача данных в представление
        $this->data['salesData'] = $salesData;
        $this->data['filter'] = $filter;
        $this->data['categories'] = $categories;
        
        $this->view('reports/sales');
    }
    
    /**
     * Отчет по продуктам
     */
    public function products() {
        // Проверка прав доступа
        if (!has_role(['admin', 'sales_manager'])) {
            $this->setFlash('error', 'У вас нет доступа к этой странице.');
            $this->redirect('dashboard');
            return;
        }
        
        // Получение параметров фильтрации
        $filter = [
            'period' => $this->input('period', 'month'),
            'start_date' => $this->input('start_date', date('Y-m-d', strtotime('-1 month'))),
            'end_date' => $this->input('end_date', date('Y-m-d')),
            'category_id' => $this->input('category_id'),
            'sort' => $this->input('sort', 'quantity_desc')
        ];
        
        // Получение данных для отчета
        $productsData = $this->getProductsData($filter);
        
        // Получение категорий для фильтра
        $categories = $this->categoryModel->getAll();
        
        // Передача данных в представление
        $this->data['productsData'] = $productsData;
        $this->data['filter'] = $filter;
        $this->data['categories'] = $categories;
        
        $this->view('reports/products');
    }
    
    /**
     * Отчет по клиентам
     */
    public function customers() {
        // Проверка прав доступа
        if (!has_role(['admin', 'sales_manager'])) {
            $this->setFlash('error', 'У вас нет доступа к этой странице.');
            $this->redirect('dashboard');
            return;
        }
        
        // Получение параметров фильтрации
        $filter = [
            'period' => $this->input('period', 'month'),
            'start_date' => $this->input('start_date', date('Y-m-d', strtotime('-1 month'))),
            'end_date' => $this->input('end_date', date('Y-m-d')),
            'sort' => $this->input('sort', 'total_desc')
        ];
        
        // Получение данных для отчета
        $customersData = $this->getCustomersData($filter);
        
        // Передача данных в представление
        $this->data['customersData'] = $customersData;
        $this->data['filter'] = $filter;
        
        $this->view('reports/customers');
    }
    
    /**
     * Страница генерации произвольного отчета
     */
    public function generate() {
        // Проверка прав доступа
        if (!has_role(['admin', 'sales_manager'])) {
            $this->setFlash('error', 'У вас нет доступа к этой странице.');
            $this->redirect('dashboard');
            return;
        }
        
        // Получение параметров отчета
        $reportType = $this->input('report_type');
        $format = $this->input('format', 'html');
        $filter = [
            'start_date' => $this->input('start_date', date('Y-m-d', strtotime('-1 month'))),
            'end_date' => $this->input('end_date', date('Y-m-d')),
            'category_id' => $this->input('category_id'),
            'status' => $this->input('status')
        ];
        
        // Если тип отчета выбран, генерируем отчет
        if ($reportType) {
            switch ($reportType) {
                case 'sales':
                    $reportData = $this->getSalesData($filter);
                    $reportTitle = 'Отчет по продажам';
                    break;
                    
                case 'products':
                    $reportData = $this->getProductsData($filter);
                    $reportTitle = 'Отчет по продуктам';
                    break;
                    
                case 'customers':
                    $reportData = $this->getCustomersData($filter);
                    $reportTitle = 'Отчет по клиентам';
                    break;
                    
                case 'inventory':
                    $reportData = $this->getInventoryData($filter);
                    $reportTitle = 'Отчет по складским запасам';
                    break;
                    
                case 'orders':
                    $reportData = $this->getOrdersData($filter);
                    $reportTitle = 'Отчет по заказам';
                    break;
                    
                default:
                    $reportData = [];
                    $reportTitle = 'Неизвестный тип отчета';
            }
            
            // Передача данных в представление
            $this->data['reportData'] = $reportData;
            $this->data['reportTitle'] = $reportTitle;
            $this->data['reportType'] = $reportType;
            
            // Выбор формата вывода
            if ($format === 'pdf') {
                $this->generatePdf($reportType, $reportTitle, $reportData, $filter);
            } elseif ($format === 'excel') {
                $this->generateExcel($reportType, $reportTitle, $reportData, $filter);
            } elseif ($format === 'csv') {
                $this->generateCsv($reportType, $reportTitle, $reportData, $filter);
            } else {
                // По умолчанию - HTML
                $this->view('reports/generated');
            }
        } else {
            // Получение категорий для фильтра
            $categories = $this->categoryModel->getAll();
            
            // Передача данных в представление
            $this->data['filter'] = $filter;
            $this->data['categories'] = $categories;
            
            $this->view('reports/generate');
        }
    }
    
    /**
     * Получение данных для отчета по продажам
     *
     * @param array $filter
     * @return array
     */
    private function getSalesData($filter) {
        // Формирование условий для SQL-запроса
        $conditions = [];
        $params = [];
        
        if (!empty($filter['start_date'])) {
            $conditions[] = 'sa.date >= ?';
            $params[] = $filter['start_date'];
        }
        
        if (!empty($filter['end_date'])) {
            $conditions[] = 'sa.date <= ?';
            $params[] = $filter['end_date'];
        }
        
        if (!empty($filter['category_id'])) {
            $conditions[] = 'p.category_id = ?';
            $params[] = $filter['category_id'];
        }
        
        // Формирование условия WHERE
        $whereClause = '';
        if (!empty($conditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }
        
        // Запрос для общих показателей
        $totalSql = "SELECT 
                        SUM(sa.quantity_sold) as total_quantity, 
                        SUM(sa.revenue) as total_revenue, 
                        SUM(sa.profit) as total_profit
                    FROM sales_analytics sa
                    JOIN products p ON sa.product_id = p.id
                    $whereClause";
        
        $totals = $this->db->getOne($totalSql, $params);
        
        // Запрос для данных по дням
        $dailySql = "SELECT 
                        sa.date, 
                        SUM(sa.quantity_sold) as quantity, 
                        SUM(sa.revenue) as revenue, 
                        SUM(sa.profit) as profit
                    FROM sales_analytics sa
                    JOIN products p ON sa.product_id = p.id
                    $whereClause
                    GROUP BY sa.date
                    ORDER BY sa.date";
        
        $dailyData = $this->db->getAll($dailySql, $params);
        
        // Запрос для данных по категориям
        $categorySql = "SELECT 
                        c.id as category_id,
                        c.name as category_name, 
                        SUM(sa.quantity_sold) as quantity, 
                        SUM(sa.revenue) as revenue, 
                        SUM(sa.profit) as profit
                    FROM sales_analytics sa
                    JOIN products p ON sa.product_id = p.id
                    JOIN categories c ON p.category_id = c.id
                    $whereClause
                    GROUP BY c.id, c.name
                    ORDER BY revenue DESC";
        
        $categoryData = $this->db->getAll($categorySql, $params);
        
        // Запрос для данных по продуктам
        $productSql = "SELECT 
                        p.id as product_id,
                        p.name as product_name, 
                        SUM(sa.quantity_sold) as quantity, 
                        SUM(sa.revenue) as revenue, 
                        SUM(sa.profit) as profit
                    FROM sales_analytics sa
                    JOIN products p ON sa.product_id = p.id
                    $whereClause
                    GROUP BY p.id, p.name
                    ORDER BY quantity DESC
                    LIMIT 10";
        
        $productData = $this->db->getAll($productSql, $params);
        
        // Формирование итогового массива данных
        return [
            'totals' => $totals,
            'daily' => $dailyData,
            'categories' => $categoryData,
            'products' => $productData
        ];
    }
    
    /**
     * Получение данных для отчета по продуктам
     *
     * @param array $filter
     * @return array
     */
    private function getProductsData($filter) {
        // Формирование условий для SQL-запроса
        $conditions = [];
        $params = [];
        
        if (!empty($filter['start_date'])) {
            $conditions[] = 'sa.date >= ?';
            $params[] = $filter['start_date'];
        }
        
        if (!empty($filter['end_date'])) {
            $conditions[] = 'sa.date <= ?';
            $params[] = $filter['end_date'];
        }
        
        if (!empty($filter['category_id'])) {
            $conditions[] = 'p.category_id = ?';
            $params[] = $filter['category_id'];
        }
        
        // Формирование условия WHERE
        $whereClause = '';
        if (!empty($conditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }
        
        // Формирование условия сортировки
        $orderClause = 'ORDER BY ';
        switch ($filter['sort'] ?? 'quantity_desc') {
            case 'quantity_asc':
                $orderClause .= 'quantity ASC';
                break;
            case 'revenue_desc':
                $orderClause .= 'revenue DESC';
                break;
            case 'revenue_asc':
                $orderClause .= 'revenue ASC';
                break;
            case 'profit_desc':
                $orderClause .= 'profit DESC';
                break;
            case 'profit_asc':
                $orderClause .= 'profit ASC';
                break;
            case 'name_asc':
                $orderClause .= 'product_name ASC';
                break;
            case 'name_desc':
                $orderClause .= 'product_name DESC';
                break;
            default:
                $orderClause .= 'quantity DESC';
        }
        
        // Запрос для данных по продуктам
        $productSql = "SELECT 
                        p.id as product_id,
                        p.name as product_name, 
                        c.name as category_name,
                        p.price as price,
                        p.stock_quantity as stock,
                        SUM(sa.quantity_sold) as quantity, 
                        SUM(sa.revenue) as revenue, 
                        SUM(sa.profit) as profit
                    FROM sales_analytics sa
                    JOIN products p ON sa.product_id = p.id
                    LEFT JOIN categories c ON p.category_id = c.id
                    $whereClause
                    GROUP BY p.id, p.name, c.name, p.price, p.stock_quantity
                    $orderClause";
        
        $productData = $this->db->getAll($productSql, $params);
        
        // Расчет процентной доли от общих продаж
        $totalQuantity = 0;
        $totalRevenue = 0;
        $totalProfit = 0;
        
        foreach ($productData as $product) {
            $totalQuantity += $product['quantity'];
            $totalRevenue += $product['revenue'];
            $totalProfit += $product['profit'];
        }
        
        foreach ($productData as &$product) {
            $product['quantity_percent'] = $totalQuantity > 0 ? round(($product['quantity'] / $totalQuantity) * 100, 2) : 0;
            $product['revenue_percent'] = $totalRevenue > 0 ? round(($product['revenue'] / $totalRevenue) * 100, 2) : 0;
            $product['profit_percent'] = $totalProfit > 0 ? round(($product['profit'] / $totalProfit) * 100, 2) : 0;
        }
        
        // Формирование итогового массива данных
        return [
            'products' => $productData,
            'totals' => [
                'quantity' => $totalQuantity,
                'revenue' => $totalRevenue,
                'profit' => $totalProfit
            ]
        ];
    }
    
    /**
     * Получение данных для отчета по клиентам
     *
     * @param array $filter
     * @return array
     */
    private function getCustomersData($filter) {
        // Формирование условий для SQL-запроса
        $conditions = [];
        $params = [];
        
        if (!empty($filter['start_date'])) {
            $conditions[] = 'o.created_at >= ?';
            $params[] = $filter['start_date'] . ' 00:00:00';
        }
        
        if (!empty($filter['end_date'])) {
            $conditions[] = 'o.created_at <= ?';
            $params[] = $filter['end_date'] . ' 23:59:59';
        }
        
        // Формирование условия WHERE
        $whereClause = '';
        if (!empty($conditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }
        
        // Формирование условия сортировки
        $orderClause = 'ORDER BY ';
        switch ($filter['sort'] ?? 'total_desc') {
            case 'total_asc':
                $orderClause .= 'total_amount ASC';
                break;
            case 'orders_desc':
                $orderClause .= 'order_count DESC';
                break;
            case 'orders_asc':
                $orderClause .= 'order_count ASC';
                break;
            case 'name_asc':
                $orderClause .= 'last_name ASC, first_name ASC';
                break;
            case 'name_desc':
                $orderClause .= 'last_name DESC, first_name DESC';
                break;
            default:
                $orderClause .= 'total_amount DESC';
        }
        
        // Запрос для данных по клиентам
        $customerSql = "SELECT 
                        u.id,
                        u.username,
                        u.email,
                        u.first_name,
                        u.last_name,
                        u.phone,
                        COUNT(o.id) as order_count,
                        SUM(o.total_amount) as total_amount,
                        MIN(o.created_at) as first_order_date,
                        MAX(o.created_at) as last_order_date,
                        ROUND(AVG(o.total_amount), 2) as average_order
                    FROM users u
                    LEFT JOIN orders o ON u.id = o.customer_id $whereClause
                    WHERE u.role = 'customer'
                    GROUP BY u.id, u.username, u.email, u.first_name, u.last_name, u.phone
                    HAVING order_count > 0
                    $orderClause";
        
        $customerData = $this->db->getAll($customerSql, $params);
        
        // Запрос для общих показателей
        $totalsSql = "SELECT 
                        COUNT(DISTINCT o.customer_id) as total_active_customers,
                        COUNT(o.id) as total_orders,
                        SUM(o.total_amount) as total_amount,
                        ROUND(AVG(o.total_amount), 2) as average_order,
                        (SELECT COUNT(*) FROM users WHERE role = 'customer') as total_customers
                    FROM orders o
                    $whereClause";
        
        $totals = $this->db->getOne($totalsSql, $params);
        
        // Формирование итогового массива данных
        return [
            'customers' => $customerData,
            'totals' => $totals
        ];
    }
    
    /**
     * Получение данных для отчета по складским запасам
     *
     * @param array $filter
     * @return array
     */
    private function getInventoryData($filter) {
        // Формирование условий для SQL-запроса
        $conditions = [];
        $params = [];
        
        if (!empty($filter['category_id'])) {
            $conditions[] = 'p.category_id = ?';
            $params[] = $filter['category_id'];
        }
        
        // Формирование условия WHERE
        $whereClause = '';
        if (!empty($conditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }
        
        // Запрос для данных по продуктам в наличии
        $inventorySql = "SELECT 
                        p.id,
                        p.name,
                        c.name as category_name,
                        p.price,
                        p.stock_quantity,
                        (p.price * p.stock_quantity) as total_value,
                        p.is_active
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    $whereClause
                    ORDER BY p.stock_quantity DESC";
        
        $inventoryData = $this->db->getAll($inventorySql, $params);
        
        // Запрос для общих показателей
        $totalsSql = "SELECT 
                        COUNT(*) as total_products,
                        SUM(stock_quantity) as total_quantity,
                        SUM(price * stock_quantity) as total_value,
                        COUNT(CASE WHEN stock_quantity <= 10 THEN 1 END) as low_stock_products
                    FROM products p
                    $whereClause";
        
        $totals = $this->db->getOne($totalsSql, $params);
        
        // Запрос для данных по категориям
        $categorySql = "SELECT 
                        c.id,
                        c.name,
                        COUNT(p.id) as product_count,
                        SUM(p.stock_quantity) as total_quantity,
                        SUM(p.price * p.stock_quantity) as total_value
                    FROM categories c
                    LEFT JOIN products p ON c.id = p.category_id $whereClause
                    GROUP BY c.id, c.name
                    ORDER BY total_quantity DESC";
        
        $categoryData = $this->db->getAll($categorySql, $params);
        
        // Формирование итогового массива данных
        return [
            'inventory' => $inventoryData,
            'categories' => $categoryData,
            'totals' => $totals
        ];
    }
    
    /**
     * Получение данных для отчета по заказам
     *
     * @param array $filter
     * @return array
     */
    private function getOrdersData($filter) {
        // Формирование условий для SQL-запроса
        $conditions = [];
        $params = [];
        
        if (!empty($filter['start_date'])) {
            $conditions[] = 'o.created_at >= ?';
            $params[] = $filter['start_date'] . ' 00:00:00';
        }
        
        if (!empty($filter['end_date'])) {
            $conditions[] = 'o.created_at <= ?';
            $params[] = $filter['end_date'] . ' 23:59:59';
        }
        
        if (!empty($filter['status'])) {
            $conditions[] = 'o.status = ?';
            $params[] = $filter['status'];
        }
        
        // Формирование условия WHERE
        $whereClause = '';
        if (!empty($conditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }
        
        // Запрос для данных по заказам
        $ordersSql = "SELECT 
                        o.id,
                        o.order_number,
                        o.customer_id,
                        CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                        o.status,
                        o.total_amount,
                        o.payment_method,
                        o.created_at,
                        o.updated_at
                    FROM orders o
                    JOIN users u ON o.customer_id = u.id
                    $whereClause
                    ORDER BY o.created_at DESC";
        
        $ordersData = $this->db->getAll($ordersSql, $params);
        
        // Запрос для общих показателей
        $totalsSql = "SELECT 
                        COUNT(*) as total_orders,
                        SUM(total_amount) as total_amount,
                        ROUND(AVG(total_amount), 2) as average_order,
                        COUNT(DISTINCT customer_id) as unique_customers
                    FROM orders o
                    $whereClause";
        
        $totals = $this->db->getOne($totalsSql, $params);
        
        // Запрос для данных по статусам
        $statusSql = "SELECT 
                        status,
                        COUNT(*) as count,
                        SUM(total_amount) as total_amount
                    FROM orders o
                    $whereClause
                    GROUP BY status";
        
        $statusData = $this->db->getAll($statusSql, $params);
        
        // Запрос для данных по дням
        $dailySql = "SELECT 
                        DATE(created_at) as date,
                        COUNT(*) as count,
                        SUM(total_amount) as total_amount
                    FROM orders o
                    $whereClause
                    GROUP BY DATE(created_at)
                    ORDER BY date";
        
        $dailyData = $this->db->getAll($dailySql, $params);
        
        // Формирование итогового массива данных
        return [
            'orders' => $ordersData,
            'status' => $statusData,
            'daily' => $dailyData,
            'totals' => $totals
        ];
    }
    
    /**
     * Генерация PDF-отчета
     *
     * @param string $reportType
     * @param string $reportTitle
     * @param array $reportData
     * @param array $filter
     */
    private function generatePdf($reportType, $reportTitle, $reportData, $filter) {
        // В реальном приложении здесь был бы код для генерации PDF-документа
        // Для этого можно использовать библиотеки, такие как TCPDF, FPDF или mPDF
        
        // Временное решение - возвращаем сообщение о том, что эта функция не реализована
        $this->setFlash('warning', 'Генерация PDF-отчетов временно недоступна.');
        $this->redirect('reports/generate');
    }
    
    /**
     * Генерация Excel-отчета
     *
     * @param string $reportType
     * @param string $reportTitle
     * @param array $reportData
     * @param array $filter
     */
    private function generateExcel($reportType, $reportTitle, $reportData, $filter) {
        // В реальном приложении здесь был бы код для генерации Excel-документа
        // Для этого можно использовать библиотеки, такие как PhpSpreadsheet
        
        // Временное решение - возвращаем сообщение о том, что эта функция не реализована
        $this->setFlash('warning', 'Генерация Excel-отчетов временно недоступна.');
        $this->redirect('reports/generate');
    }
    
    /**
     * Генерация CSV-отчета
     *
     * @param string $reportType
     * @param string $reportTitle
     * @param array $reportData
     * @param array $filter
     */
    private function generateCsv($reportType, $reportTitle, $reportData, $filter) {
        // Определяем заголовки и данные в зависимости от типа отчета
        $headers = [];
        $rows = [];
        
        switch ($reportType) {
            case 'sales':
                $headers = ['Дата', 'Количество', 'Выручка (грн)', 'Прибыль (грн)'];
                foreach ($reportData['daily'] as $item) {
                    $rows[] = [
                        $item['date'],
                        $item['quantity'],
                        $item['revenue'],
                        $item['profit']
                    ];
                }
                break;
                
            case 'products':
                $headers = ['Название', 'Категория', 'Цена (грн)', 'Количество', 'Выручка (грн)', 'Прибыль (грн)'];
                foreach ($reportData['products'] as $item) {
                    $rows[] = [
                        $item['product_name'],
                        $item['category_name'],
                        $item['price'],
                        $item['quantity'],
                        $item['revenue'],
                        $item['profit']
                    ];
                }
                break;
                
            case 'customers':
                $headers = ['ID', 'Имя', 'Email', 'Количество заказов', 'Общая сумма (грн)', 'Средний чек (грн)'];
                foreach ($reportData['customers'] as $item) {
                    $rows[] = [
                        $item['id'],
                        $item['first_name'] . ' ' . $item['last_name'],
                        $item['email'],
                        $item['order_count'],
                        $item['total_amount'],
                        $item['average_order']
                    ];
                }
                break;
                
            case 'inventory':
                $headers = ['ID', 'Название', 'Категория', 'Цена (грн)', 'Количество', 'Общая стоимость (грн)'];
                foreach ($reportData['inventory'] as $item) {
                    $rows[] = [
                        $item['id'],
                        $item['name'],
                        $item['category_name'],
                        $item['price'],
                        $item['stock_quantity'],
                        $item['total_value']
                    ];
                }
                break;
                
            case 'orders':
                $headers = ['№ заказа', 'Клиент', 'Статус', 'Сумма (грн)', 'Дата'];
                foreach ($reportData['orders'] as $item) {
                    $rows[] = [
                        $item['order_number'],
                        $item['customer_name'],
                        $item['status'],
                        $item['total_amount'],
                        $item['created_at']
                    ];
                }
                break;
        }
        
        // Устанавливаем заголовки для выходного файла
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $reportType . '_' . date('Y-m-d') . '.csv"');
        
        // Создаем файловый указатель для вывода
        $output = fopen('php://output', 'w');
        
        // Добавляем BOM для корректного отображения кириллицы в Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Записываем заголовки
        fputcsv($output, $headers);
        
        // Записываем данные
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        
        // Закрываем файловый указатель
        fclose($output);
        exit;
    }
}