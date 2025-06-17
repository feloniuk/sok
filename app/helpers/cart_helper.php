<?php
// app/helpers/CartHelper.php - Простая реализация корзины через сессии

class Cart {
    private $sessionKey = 'shopping_cart';
    
    public function __construct() {
        if (!isset($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey] = [];
        }
    }
    
    /**
     * Добавить товар в корзину
     */
    public function addItem($product, $quantity = 1) {
        $key = $this->generateKey($product);
        
        if (isset($_SESSION[$this->sessionKey][$key])) {
            $_SESSION[$this->sessionKey][$key]['quantity'] += $quantity;
        } else {
            $_SESSION[$this->sessionKey][$key] = [
                'id' => $product['id'],
                'container_id' => $product['container_id'] ?? null,
                'name' => $product['name'],
                'price' => $product['price'],
                'volume' => $product['volume'] ?? 1,
                'image' => $product['image'] ?? null,
                'quantity' => $quantity
            ];
        }
    }
    
    /**
     * Обновить количество товара
     */
    public function updateQuantity($productId, $quantity, $containerId = null) {
        $key = $this->generateKey(['id' => $productId, 'container_id' => $containerId]);
        
        if (isset($_SESSION[$this->sessionKey][$key])) {
            if ($quantity <= 0) {
                unset($_SESSION[$this->sessionKey][$key]);
            } else {
                $_SESSION[$this->sessionKey][$key]['quantity'] = $quantity;
            }
        }
    }
    
    /**
     * Удалить товар из корзины
     */
    public function removeItem($productId, $containerId = null) {
        $key = $this->generateKey(['id' => $productId, 'container_id' => $containerId]);
        
        if (isset($_SESSION[$this->sessionKey][$key])) {
            unset($_SESSION[$this->sessionKey][$key]);
        }
    }
    
    /**
     * Получить все товары из корзины
     */
    public function getItems() {
        return $_SESSION[$this->sessionKey] ?? [];
    }
    
    /**
     * Очистить корзину
     */
    public function clear() {
        $_SESSION[$this->sessionKey] = [];
    }
    
    /**
     * Получить общее количество товаров
     */
    public function getTotalQuantity() {
        $total = 0;
        foreach ($_SESSION[$this->sessionKey] as $item) {
            $total += $item['quantity'];
        }
        return $total;
    }
    
    /**
     * Получить общую стоимость
     */
    public function getTotalPrice() {
        $total = 0;
        foreach ($_SESSION[$this->sessionKey] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
    
    /**
     * Проверить, пуста ли корзина
     */
    public function isEmpty() {
        return empty($_SESSION[$this->sessionKey]);
    }
    
    /**
     * Генерация уникального ключа для товара
     */
    private function generateKey($product) {
        $containerId = $product['container_id'] ?? 'default';
        return $product['id'] . '_' . $containerId;
    }
}

// Функция для получения экземпляра корзины
function cart() {
    static $cart = null;
    if ($cart === null) {
        $cart = new Cart();
    }
    return $cart;
}