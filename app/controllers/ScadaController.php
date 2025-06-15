<?php
// app/controllers/ScadaController.php - Контролер для SCADA даних

class ScadaController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        
        // Перевірка прав доступу (тільки адміністратор)
        if (!has_role('admin')) {
            $this->setFlash('error', 'У вас немає доступу до цієї сторінки');
            $this->redirect('dashboard');
            return;
        }
    }
    
    /**
     * Головна сторінка SCADA з даними з таблиці data
     */
    public function index() {
        // Отримання параметрів фільтрації та пагінації
        $page = intval($this->input('page', 1));
        $perPage = intval($this->input('per_page', 50));
        $dateFrom = $this->input('date_from');
        $dateTo = $this->input('date_to');
        $nameFilter = $this->input('name_filter');
        
        // Формування умов WHERE
        $whereConditions = [];
        $params = [];
        
        if ($dateFrom) {
            $whereConditions[] = "Dates >= ?";
            $params[] = date('d.m.Y', strtotime($dateFrom));
        }
        
        if ($dateTo) {
            $whereConditions[] = "Dates <= ?";
            $params[] = date('d.m.Y', strtotime($dateTo));
        }
        
        if ($nameFilter) {
            $whereConditions[] = "Name LIKE ?";
            $params[] = '%' . $nameFilter . '%';
        }
        
        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        }
        
        // Отримання загальної кількості записів
        $countSql = "SELECT COUNT(*) FROM data $whereClause";
        $totalItems = $this->db->getValue($countSql, $params);
        
        // Розрахунок пагінації
        $totalPages = ceil($totalItems / $perPage);
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        
        // Отримання даних з пагінацією
        $dataSql = "SELECT * FROM data $whereClause ORDER BY ID DESC LIMIT $offset, $perPage";
        $scadaData = $this->db->getAll($dataSql, $params);
        
        // Отримання статистики
        $statsSql = "SELECT 
                        COUNT(*) as total_records,
                        COUNT(DISTINCT Name) as unique_parameters,
                        MIN(Parameter) as min_value,
                        MAX(Parameter) as max_value,
                        AVG(Parameter) as avg_value,
                        MIN(STR_TO_DATE(CONCAT(Dates, ' ', Times), '%d.%m.%Y %H:%i:%s')) as first_record,
                        MAX(STR_TO_DATE(CONCAT(Dates, ' ', Times), '%d.%m.%Y %H:%i:%s')) as last_record
                     FROM data $whereClause";
        $stats = $this->db->getOne($statsSql, $params);
        
        // Отримання унікальних параметрів для фільтра
        $parametersSql = "SELECT DISTINCT Name FROM data ORDER BY Name";
        $parameters = $this->db->getAll($parametersSql);
        
        // Отримання даних для графіка (останні 100 записів)
        $chartSql = "SELECT Name, Parameter, Dates, Times,
                           STR_TO_DATE(CONCAT(Dates, ' ', Times), '%d.%m.%Y %H:%i:%s') as datetime
                     FROM data $whereClause 
                     ORDER BY STR_TO_DATE(CONCAT(Dates, ' ', Times), '%d.%m.%Y %H:%i:%s') ASC 
                     LIMIT 100";
        $chartData = $this->db->getAll($chartSql, $params);
        
        // Передача даних у представлення
        $this->data['scadaData'] = $scadaData;
        $this->data['pagination'] = [
            'current_page' => $page,
            'per_page' => $perPage,
            'total_items' => $totalItems,
            'total_pages' => $totalPages
        ];
        $this->data['stats'] = $stats;
        $this->data['parameters'] = $parameters;
        $this->data['chartData'] = $chartData;
        $this->data['filters'] = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'name_filter' => $nameFilter,
            'per_page' => $perPage
        ];
        $this->data['title'] = 'SCADA Дані';
        
        $this->view('admin/scada/index');
    }
    
    /**
     * Отримання даних для графіка в реальному часі (AJAX)
     */
    public function getChartData() {
        $parameterName = $this->input('parameter', 'Температура пастерізації соку');
        $limit = intval($this->input('limit', 20));
        
        $sql = "SELECT Parameter as value, Dates, Times,
                       CONCAT(Dates, ' ', Times) as label
                FROM data 
                WHERE Name = ? 
                ORDER BY STR_TO_DATE(CONCAT(Dates, ' ', Times), '%d.%m.%Y %H:%i:%s') DESC 
                LIMIT ?";
        
        $data = $this->db->getAll($sql, [$parameterName, $limit]);
        
        // Реверсуємо масив для правильного порядку часу (від старішого до новішого)
        $data = array_reverse($data);
        
        // Форматуємо дані для графіка
        $formattedData = [];
        foreach ($data as $row) {
            $formattedData[] = [
                'value' => floatval($row['value']),
                'label' => $row['label'],
                'timestamp' => $row['Dates'] . ' ' . $row['Times']
            ];
        }
        
        $this->json([
            'success' => true,
            'data' => $formattedData,
            'parameter' => $parameterName,
            'count' => count($formattedData)
        ]);
    }
    
    /**
     * Експорт даних в CSV
     */
    public function exportCsv() {
        $dateFrom = $this->input('date_from');
        $dateTo = $this->input('date_to');
        $nameFilter = $this->input('name_filter');
        
        // Формування умов WHERE
        $whereConditions = [];
        $params = [];
        
        if ($dateFrom) {
            $whereConditions[] = "Dates >= ?";
            $params[] = date('d.m.Y', strtotime($dateFrom));
        }
        
        if ($dateTo) {
            $whereConditions[] = "Dates <= ?";
            $params[] = date('d.m.Y', strtotime($dateTo));
        }
        
        if ($nameFilter) {
            $whereConditions[] = "Name LIKE ?";
            $params[] = '%' . $nameFilter . '%';
        }
        
        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        }
        
        // Отримання всіх даних для експорту
        $sql = "SELECT * FROM data $whereClause ORDER BY ID DESC";
        $data = $this->db->getAll($sql, $params);
        
        // Встановлення заголовків для CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="scada_data_' . date('Y-m-d_H-i-s') . '.csv"');
        
        // Створення файлового потоку
        $output = fopen('php://output', 'w');
        
        // Додавання BOM для UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Запис заголовків
        fputcsv($output, ['ID', 'Назва параметра', 'Значення', 'Дата', 'Час']);
        
        // Запис даних
        foreach ($data as $row) {
            fputcsv($output, [
                $row['ID'],
                $row['Name'],
                $row['Parameter'],
                $row['Dates'],
                $row['Times']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Очищення старих даних
     */
    public function cleanup() {
        if (!$this->isPost()) {
            $this->redirect('admin/scada');
            return;
        }
        
        $this->validateCsrfToken();
        
        $daysToKeep = intval($this->input('days_to_keep', 30));
        
        if ($daysToKeep < 1) {
            $this->setFlash('error', 'Кількість днів має бути більше 0');
            $this->redirect('admin/scada');
            return;
        }
        
        $cutoffDate = date('d.m.Y', strtotime("-$daysToKeep days"));
        
        $sql = "DELETE FROM data WHERE STR_TO_DATE(Dates, '%d.%m.%Y') < STR_TO_DATE(?, '%d.%m.%Y')";
        $deletedCount = $this->db->query($sql, [$cutoffDate])->rowCount();
        
        $this->setFlash('success', "Видалено $deletedCount записів старіше $daysToKeep днів");
        $this->redirect('admin/scada');
    }
}