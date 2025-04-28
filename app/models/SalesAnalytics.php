<?php
// app/models/SalesAnalytics.php - Модель для аналитики продаж

class SalesAnalytics extends BaseModel {
    protected $table = 'sales_analytics';
    protected $fillable = [
        'date', 'product_id', 'quantity_sold', 'revenue', 'cost', 'profit'
    ];
    
    /**
     * Обновление данных продаж для продукта на указанную дату
     *
     * @param string $date
     * @param int $productId
     * @param int $quantity
     * @param float $revenue
     * @param float $cost
     * @param float $profit
     * @return bool
     */
    public function updateSalesData($date, $productId, $quantity, $revenue, $cost, $profit) {
        // Поиск существующей записи
        $record = $this->findOne('date = ? AND product_id = ?', [$date, $productId]);
        
        if ($record) {
            // Обновление существующей записи
            $data = [
                'quantity_sold' => $record['quantity_sold'] + $quantity,
                'revenue' => $record['revenue'] + $revenue,
                'cost' => $record['cost'] + $cost,
                'profit' => $record['profit'] + $profit
            ];
            
            return $this->update($record['id'], $data);
        } else {
            // Создание новой записи
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
     * Получение данных продаж за период
     *
     * @param string $startDate
     * @param string $endDate
     * @param int $productId
     * @return array
     */
    public function getSalesForPeriod($startDate, $endDate, $productId = null) {
        $sql = 'SELECT sa.date, sa.quantity_sold, sa.revenue, sa.cost, sa.profit, p.name as product_name 
                FROM sales_analytics sa 
                JOIN products p ON sa.product_id = p.id 
                WHERE sa.date BETWEEN ? AND ?';
        $params = [$startDate, $endDate];
        
        if ($productId) {
            $sql .= ' AND sa.product_id = ?';
            $params[] = $productId;
        }
        
        $sql .= ' ORDER BY sa.date';
        
        return $this->db->getAll($sql, $params);
    }
    
    /**
     * Получение общих показателей продаж за период
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getTotalsForPeriod($startDate, $endDate) {
        $sql = 'SELECT SUM(quantity_sold) as total_quantity, 
                       SUM(revenue) as total_revenue, 
                       SUM(cost) as total_cost, 
                       SUM(profit) as total_profit 
                FROM sales_analytics 
                WHERE date BETWEEN ? AND ?';
        
        return $this->db->getOne($sql, [$startDate, $endDate]);
    }
    
    /**
     * Получение топ продуктов по продажам
     *
     * @param string $startDate
     * @param string $endDate
     * @param int $limit
     * @return array
     */
    public function getTopSellingProducts($startDate, $endDate, $limit = 10) {
        $sql = 'SELECT p.id, p.name, 
                       SUM(sa.quantity_sold) as total_quantity, 
                       SUM(sa.revenue) as total_revenue, 
                       SUM(sa.profit) as total_profit 
                FROM sales_analytics sa 
                JOIN products p ON sa.product_id = p.id 
                WHERE sa.date BETWEEN ? AND ? 
                GROUP BY p.id, p.name 
                ORDER BY total_quantity DESC 
                LIMIT ?';
        
        return $this->db->getAll($sql, [$startDate, $endDate, $limit]);
    }
    
    /**
     * Получение данных продаж по категориям
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getSalesByCategory($startDate, $endDate) {
        $sql = 'SELECT c.id, c.name, 
                       SUM(sa.quantity_sold) as total_quantity, 
                       SUM(sa.revenue) as total_revenue, 
                       SUM(sa.profit) as total_profit 
                FROM sales_analytics sa 
                JOIN products p ON sa.product_id = p.id 
                JOIN categories c ON p.category_id = c.id 
                WHERE sa.date BETWEEN ? AND ? 
                GROUP BY c.id, c.name 
                ORDER BY total_revenue DESC';
        
        return $this->db->getAll($sql, [$startDate, $endDate]);
    }
    
    /**
     * Получение динамики продаж по дням
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getDailySales($startDate, $endDate) {
        $sql = 'SELECT date, 
                       SUM(quantity_sold) as quantity, 
                       SUM(revenue) as revenue, 
                       SUM(profit) as profit 
                FROM sales_analytics 
                WHERE date BETWEEN ? AND ? 
                GROUP BY date 
                ORDER BY date';
        
        return $this->db->getAll($sql, [$startDate, $endDate]);
    }
    
    /**
     * Получение динамики продаж по месяцам
     *
     * @param int $year
     * @return array
     */
    public function getMonthlySales($year) {
        $sql = 'SELECT MONTH(date) as month, 
                       SUM(quantity_sold) as quantity, 
                       SUM(revenue) as revenue, 
                       SUM(profit) as profit 
                FROM sales_analytics 
                WHERE YEAR(date) = ? 
                GROUP BY MONTH(date) 
                ORDER BY MONTH(date)';
        
        return $this->db->getAll($sql, [$year]);
    }
    
    /**
     * Получение данных для анализа эффективности продаж продукта
     *
     * @param int $productId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getProductPerformance($productId, $startDate, $endDate) {
        // Получаем данные продаж по дням
        $dailySales = $this->getSalesForPeriod($startDate, $endDate, $productId);
        
        // Получаем общие показатели
        $totals = $this->db->getOne(
            'SELECT SUM(quantity_sold) as total_quantity, 
                    SUM(revenue) as total_revenue, 
                    SUM(profit) as total_profit,
                    ROUND(AVG(quantity_sold), 2) as avg_daily_quantity,
                    ROUND(AVG(revenue), 2) as avg_daily_revenue,
                    ROUND(AVG(profit), 2) as avg_daily_profit
             FROM sales_analytics 
             WHERE product_id = ? AND date BETWEEN ? AND ?',
            [$productId, $startDate, $endDate]
        );
        
        // Расчет маржинальности (отношение прибыли к выручке)
        $marginality = 0;
        if (!empty($totals) && $totals['total_revenue'] > 0) {
            $marginality = round(($totals['total_profit'] / $totals['total_revenue']) * 100, 2);
        }
        
        // Формирование результата
        return [
            'daily_sales' => $dailySales,
            'totals' => $totals,
            'marginality' => $marginality
        ];
    }
}