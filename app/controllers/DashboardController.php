<?php
// app/controllers/DashboardController.php - Контроллер для панели управления

class DashboardController extends BaseController {
    private $userModel;
    private $productModel;
    private $orderModel;
    private $warehouseModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->warehouseModel = new Warehouse();
    }
    
    /**
     * Отображение панели управления с учетом роли пользователя
     */
    public function index() {
        // Проверка авторизации
        if (!is_logged_in()) {
            $this->redirect('auth/login');
            return;
        }
        
        // Получение роли пользователя
        $role = get_current_user_role();
        
        // Получение данных в зависимости от роли
        switch ($role) {
            case 'admin':
                $this->adminDashboard();
                break;
                
            case 'sales_manager':
                $this->salesManagerDashboard();
                break;
                
            case 'warehouse_manager':
                $this->warehouseManagerDashboard();
                break;
                
            case 'customer':
                $this->customerDashboard();
                break;
                
            default:
                $this->redirect('auth/login');
        }
    }
    
    /**
     * Панель управления для администратора
     */
    private function adminDashboard() {
        // Получение статистики пользователей
        $userStats = $this->userModel->getDashboardData('admin');
        
        // Получение статистики продуктов
        $productCount = $this->productModel->count();
        $lowStockProducts = $this->productModel->getLowStockProducts();
        
        // Получение статистики заказов
        $orderStats = $this->orderModel->getOrderStats();
        
        // Получение статистики продаж
        $salesStats = $this->productModel->getAnalyticsData();
        
        // Передача данных в представление
        $this->data['userStats'] = $userStats;
        $this->data['productCount'] = $productCount;
        $this->data['lowStockProducts'] = $lowStockProducts;
        $this->data['orderStats'] = $orderStats;
        $this->data['salesStats'] = $salesStats;
        
        $this->view('admin/dashboard');
    }
    
    /**
     * Панель управления для менеджера продаж
     */
    private function salesManagerDashboard() {
        // Получение статистики продаж
        $salesStats = $this->productModel->getAnalyticsData();
        
        // Получение статистики заказов
        $orderStats = $this->orderModel->getOrderStats();
        
        // Получение последних заказов
        $recentOrders = $this->getRecentOrders(5);
        
        // Получение топовых клиентов
        $topCustomers = $this->getTopCustomers(5);
        
        // Передача данных в представление
        $this->data['salesStats'] = $salesStats;
        $this->data['orderStats'] = $orderStats;
        $this->data['recentOrders'] = $recentOrders;
        $this->data['topCustomers'] = $topCustomers;
        
        $this->view('sales/dashboard');
    }
    
    /**
     * Панель управления для менеджера склада
     */
    private function warehouseManagerDashboard() {
        // Получение статистики склада
        $warehouseStats = $this->warehouseModel->getStats();
        
        // Получение продуктов с низким запасом
        $lowStockProducts = $this->productModel->getLowStockProducts();
        
        // Получение последних движений товаров
        $recentMovements = $this->getRecentInventoryMovements(10);
        
        // Передача данных в представление
        $this->data['warehouseStats'] = $warehouseStats;
        $this->data['lowStockProducts'] = $lowStockProducts;
        $this->data['recentMovements'] = $recentMovements;
        
        $this->view('warehouse/dashboard');
    }
    
    /**
     * Панель управления для клиента
     */
    private function customerDashboard() {
        // Получение ID текущего пользователя
        $customerId = get_current_user_id();
        
        // Получение последних заказов клиента
        $customerOrders = $this->orderModel->getCustomerOrders($customerId);
        
        // Получение рекомендуемых продуктов
        $recommendedProducts = $this->productModel->getFeatured();
        
        // Передача данных в представление
        $this->data['customerOrders'] = $customerOrders;
        $this->data['recommendedProducts'] = $recommendedProducts;
        
        $this->view('customer/dashboard');
    }
    
    /**
     * Получение последних заказов
     *
     * @param int $limit
     * @return array
     */
    private function getRecentOrders($limit = 5) {
        $sql = 'SELECT o.*, u.first_name, u.last_name 
                FROM orders o 
                JOIN users u ON o.customer_id = u.id 
                ORDER BY o.created_at DESC 
                LIMIT ?';
        
        return $this->db->getAll($sql, [$limit]);
    }
    
    /**
     * Получение топовых клиентов по сумме заказов
     *
     * @param int $limit
     * @return array
     */
    private function getTopCustomers($limit = 5) {
        $sql = 'SELECT u.id, u.first_name, u.last_name, COUNT(o.id) as order_count, SUM(o.total_amount) as total_spent 
                FROM users u 
                JOIN orders o ON u.id = o.customer_id 
                GROUP BY u.id, u.first_name, u.last_name 
                ORDER BY total_spent DESC 
                LIMIT ?';
        
        return $this->db->getAll($sql, [$limit]);
    }
    
    /**
     * Получение последних движений товаров
     *
     * @param int $limit
     * @return array
     */
    private function getRecentInventoryMovements($limit = 10) {
        $sql = 'SELECT im.*, p.name as product_name, w.name as warehouse_name, u.first_name, u.last_name 
                FROM inventory_movements im 
                JOIN products p ON im.product_id = p.id 
                JOIN warehouses w ON im.warehouse_id = w.id 
                JOIN users u ON im.created_by = u.id 
                ORDER BY im.created_at DESC 
                LIMIT ?';
        
        return $this->db->getAll($sql, [$limit]);
    }
    
    /**
     * Получение аналитических данных для графиков
     */
    public function getChartData() {
        // Проверка авторизации
        if (!is_logged_in()) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }
        
        // Получение параметра типа графика
        $type = $this->input('type') ?: 'sales';
        $period = $this->input('period') ?: 'month';
        
        $data = [];
        
        switch ($type) {
            case 'sales':
                // Данные продаж по дням
                $sql = 'SELECT DATE(date) as day, SUM(revenue) as revenue, SUM(profit) as profit 
                        FROM sales_analytics 
                        WHERE date >= DATE_SUB(CURDATE(), INTERVAL 1 ' . strtoupper($period) . ') 
                        GROUP BY day 
                        ORDER BY day';
                
                $data = $this->db->getAll($sql);
                break;
                
            case 'orders':
                // Данные заказов по дням
                $sql = 'SELECT DATE(created_at) as day, COUNT(*) as count, SUM(total_amount) as amount 
                        FROM orders 
                        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 ' . strtoupper($period) . ') 
                        GROUP BY day 
                        ORDER BY day';
                
                $data = $this->db->getAll($sql);
                break;
                
            case 'products':
                // Топ продуктов по продажам
                $sql = 'SELECT p.name, SUM(sa.quantity_sold) as quantity 
                        FROM products p 
                        JOIN sales_analytics sa ON p.id = sa.product_id 
                        WHERE sa.date >= DATE_SUB(CURDATE(), INTERVAL 1 ' . strtoupper($period) . ') 
                        GROUP BY p.id, p.name 
                        ORDER BY quantity DESC 
                        LIMIT 10';
                
                $data = $this->db->getAll($sql);
                break;
                
            case 'categories':
                // Продажи по категориям
                $sql = 'SELECT c.name, SUM(sa.revenue) as revenue 
                        FROM categories c 
                        JOIN products p ON c.id = p.category_id 
                        JOIN sales_analytics sa ON p.id = sa.product_id 
                        WHERE sa.date >= DATE_SUB(CURDATE(), INTERVAL 1 ' . strtoupper($period) . ') 
                        GROUP BY c.id, c.name 
                        ORDER BY revenue DESC';
                
                $data = $this->db->getAll($sql);
                break;
        }
        
        // Отправка данных в формате JSON
        $this->json($data);
    }
}