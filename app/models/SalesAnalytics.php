<?php
// app/models/SalesAnalytics.php - Модель для аналітики продажів

class SalesAnalytics extends BaseModel {
    protected $table = 'sales_analytics';
    protected $fillable = [
        'date', 'product_id', 'quantity_sold', 'revenue', 'cost', 'profit'
    ];
    
    /**
     * Оновлення або створення запису аналітики продажів
     * 
     * @param string $date Дата у форматі Y-m-d
     * @param int $productId ID продукту
     * @param int $quantity Кількість проданих одиниць
     * @param float $revenue Виручка
     * @param float $cost Собівартість
     * @param float $profit Прибуток
     * @return bool
     */
    public function updateSalesData($date, $productId, $quantity, $revenue, $cost, $profit) {
        // Перевірка існування запису
        $existingRecord = $this->findOne('date = ? AND product_id = ?', [$date, $productId]);
        
        if ($existingRecord) {
            // Оновлення існуючого запису
            $data = [
                'quantity_sold' => $existingRecord['quantity_sold'] + $quantity,
                'revenue' => $existingRecord['revenue'] + $revenue,
                'cost' => $existingRecord['cost'] + $cost,
                'profit' => $existingRecord['profit'] + $profit
            ];
            
            return $this->update($existingRecord['id'], $data);
        } else {
            // Створення нового запису
            $data = [
                'date' => $date,
                'product_id' => $productId,
                'quantity_sold' => $quantity,
                'revenue' => $revenue,
                'cost' => $cost,
                'profit' => $profit
            ];
            
            return $this->create($data) ? true : false;
        }
    }
    
    /**
     * Отримання даних продажів за період
     * 
     * @param string $startDate Початкова дата
     * @param string $endDate Кінцева дата
     * @param int|null $productId ID продукту (необов'язково)
     * @return array
     */
    public function getSalesForPeriod($startDate, $endDate, $productId = null) {
        $sql = 'SELECT sa.date, sa.product_id, p.name as product_name, 
                sa.quantity_sold, sa.revenue, sa.cost, sa.profit
                FROM sales_analytics sa
                JOIN products p ON sa.product_id = p.id
                WHERE sa.date BETWEEN ? AND ?';
        
        $params = [$startDate, $endDate];
        
        if ($productId) {
            $sql .= ' AND sa.product_id = ?';
            $params[] = $productId;
        }
        
        $sql .= ' ORDER BY sa.date, p.name';
        
        return $this->db->getAll($sql, $params);
    }
    
    /**
     * Отримання загальних показників за період
     * 
     * @param string $startDate Початкова дата
     * @param string $endDate Кінцева дата
     * @return array
     */
    public function getTotalsByPeriod($startDate, $endDate) {
        $sql = 'SELECT 
                SUM(quantity_sold) as total_quantity, 
                SUM(revenue) as total_revenue, 
                SUM(cost) as total_cost, 
                SUM(profit) as total_profit,
                ROUND(SUM(profit) / SUM(revenue) * 100, 2) as profit_margin
                FROM sales_analytics
                WHERE date BETWEEN ? AND ?';
        
        return $this->db->getOne($sql, [$startDate, $endDate]);
    }
    
    /**
     * Отримання топ продуктів за продажами
     * 
     * @param string $startDate Початкова дата
     * @param string $endDate Кінцева дата
     * @param int $limit Кількість записів
     * @return array
     */
    public function getTopProducts($startDate, $endDate, $limit = 10) {
        $sql = 'SELECT p.id, p.name, p.image, c.name as category_name,
                SUM(sa.quantity_sold) as total_quantity, 
                SUM(sa.revenue) as total_revenue, 
                SUM(sa.profit) as total_profit,
                ROUND(SUM(sa.profit) / SUM(sa.revenue) * 100, 2) as profit_margin
                FROM sales_analytics sa
                JOIN products p ON sa.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE sa.date BETWEEN ? AND ?
                GROUP BY p.id, p.name, p.image, c.name
                ORDER BY total_quantity DESC
                LIMIT ?';
        
        return $this->db->getAll($sql, [$startDate, $endDate, $limit]);
    }
    
    /**
     * Отримання продажів за категоріями
     * 
     * @param string $startDate Початкова дата
     * @param string $endDate Кінцева дата
     * @return array
     */
    public function getSalesByCategory($startDate, $endDate) {
        $sql = 'SELECT c.id, c.name, 
                SUM(sa.quantity_sold) as total_quantity, 
                SUM(sa.revenue) as total_revenue, 
                SUM(sa.profit) as total_profit,
                ROUND(SUM(sa.profit) / SUM(sa.revenue) * 100, 2) as profit_margin
                FROM sales_analytics sa
                JOIN products p ON sa.product_id = p.id
                JOIN categories c ON p.category_id = c.id
                WHERE sa.date BETWEEN ? AND ?
                GROUP BY c.id, c.name
                ORDER BY total_revenue DESC';
        
        return $this->db->getAll($sql, [$startDate, $endDate]);
    }
    
    /**
     * Отримання щоденних продажів за період
     * 
     * @param string $startDate Початкова дата
     * @param string $endDate Кінцева дата
     * @return array
     */
    public function getDailySales($startDate, $endDate) {
        $sql = 'SELECT date, 
                SUM(quantity_sold) as total_quantity, 
                SUM(revenue) as total_revenue, 
                SUM(profit) as total_profit
                FROM sales_analytics
                WHERE date BETWEEN ? AND ?
                GROUP BY date
                ORDER BY date';
        
        return $this->db->getAll($sql, [$startDate, $endDate]);
    }
    
    /**
     * Отримання щомісячних продажів за рік
     * 
     * @param int $year Рік
     * @return array
     */
    public function getMonthlySales($year) {
        $sql = 'SELECT 
                MONTH(date) as month, 
                SUM(quantity_sold) as total_quantity, 
                SUM(revenue) as total_revenue, 
                SUM(profit) as total_profit
                FROM sales_analytics
                WHERE YEAR(date) = ?
                GROUP BY MONTH(date)
                ORDER BY MONTH(date)';
        
        return $this->db->getAll($sql, [$year]);
    }
}