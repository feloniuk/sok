<?php
// app/helpers/Cart.php - Класс для работы с корзиной

class Cart {
    private $items = [];
    
    public function __construct() {
        if (isset($_SESSION['cart'])) {
            $this->items = $_SESSION['cart'];
        }
    }
    
    /**
     * Добавление товара в корзину
     */
    public function addItem($product, $quantity = 1) {
        $key = $this->generateKey($product['id'], $product['container_id'] ?? null);
        
        if (isset($this->items[$key])) {
            $this->items[$key]['quantity'] += $quantity;
        } else {
            $this->items[$key] = [
                'id' => $product['id'],
                'container_id' => $product['container_id'] ?? null,
                'name' => $product['name'],
                'price' => $product['price'],
                'volume' => $product['volume'] ?? 1,
                'image' => $product['image'],
                'quantity' => $quantity
            ];
        }
        
        $this->save();
    }
    
    /**
     * Обновление количества товара
     */
    public function updateQuantity($productId, $quantity, $containerId = null) {
        $key = $this->generateKey($productId, $containerId);
        
        if (isset($this->items[$key])) {
            if ($quantity > 0) {
                $this->items[$key]['quantity'] = $quantity;
            } else {
                unset($this->items[$key]);
            }
            $this->save();
        }
    }
    
    /**
     * Удаление товара из корзины
     */
    public function removeItem($productId, $containerId = null) {
        $key = $this->generateKey($productId, $containerId);
        
        if (isset($this->items[$key])) {
            unset($this->items[$key]);
            $this->save();
        }
    }
    
    /**
     * Очистка корзины
     */
    public function clear() {
        $this->items = [];
        $this->save();
    }
    
    /**
     * Получение всех товаров
     */
    public function getItems() {
        return $this->items;
    }
    
    /**
     * Получение общего количества товаров
     */
    public function getTotalQuantity() {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item['quantity'];
        }
        return $total;
    }
    
    /**
     * Получение общей суммы
     */
    public function getTotalPrice() {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
    
    /**
     * Получение общего объема
     */
    public function getTotalVolume() {
        $total = 0;
        foreach ($this->items as $item) {
            $total += ($item['volume'] ?? 1) * $item['quantity'];
        }
        return $total;
    }
    
    /**
     * Генерация уникального ключа для товара
     */
    private function generateKey($productId, $containerId = null) {
        return $productId . '_' . ($containerId ?? 'base');
    }
    
    /**
     * Сохранение корзины в сессию
     */
    private function save() {
        $_SESSION['cart'] = $this->items;
    }
}

// Глобальная функция для получения корзины
function cart() {
    static $cart = null;
    if ($cart === null) {
        $cart = new Cart();
    }
    return $cart;
}