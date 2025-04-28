<?php
// config/database.php - Класс для работы с базой данных

class Database {
    private $host = 'localhost';
    private $db_name = 'juice_sales_db';
    private $username = 'root';
    private $password = '';
    private $conn;
    private static $instance = null;
    
    // Приватный конструктор для паттерна Singleton
    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=$this->host;dbname=$this->db_name;charset=utf8",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Ошибка подключения к базе данных: " . $e->getMessage());
            } else {
                die("Ошибка подключения к базе данных. Пожалуйста, попробуйте позже.");
            }
        }
    }
    
    // Получение единственного экземпляра класса
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Проверка, есть ли уже активная транзакция
     *
     * @return bool
     */
    public function inTransaction() {
        return $this->conn->inTransaction();
    }

    // Получение соединения
    public function getConnection() {
        return $this->conn;
    }
    
    // Выполнение запроса
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Ошибка выполнения запроса: " . $e->getMessage() . "<br>SQL: $sql");
            } else {
                die("Произошла ошибка при выполнении запроса. Пожалуйста, попробуйте позже.");
            }
        }
    }
    
    // Получение всех записей
    public function getAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    // Получение одной записи
    public function getOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    // Получение значения из одной ячейки
    public function getValue($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }
    
    // Получение последнего вставленного ID
    public function getLastId() {
        return $this->conn->lastInsertId();
    }
    
    // Транзакции
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    public function commit() {
        return $this->conn->commit();
    }
    
    public function rollBack() {
        return $this->conn->rollBack();
    }
}