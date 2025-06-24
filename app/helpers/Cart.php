<?php
// app/helpers/cart_helper.php - Усовершенствованный helper для корзины

/**
 * Класс для работы с корзиной заказов
 */
class Cart {
    private $items = [];
    private $sessionKey = 'order_cart';
    
    public function __construct() {
        $this->loadFromSession();
    }
    
    /**
     * Добавление товара в корзину
     */
    public function addItem($product, $quantity = 1, $containerId = null, $volume = 1) {
        $cartKey = $this->generateCartKey($product['id'], $containerId);
        
        if (isset($this->items[$cartKey])) {
            $this->items[$cartKey]['quantity'] += $quantity;
        } else {
            $this->items[$cartKey] = [
                'id' => $product['id'],
                'container_id' => $containerId,
                'name' => $product['name'],
                'price' => $product['price'],
                'volume' => $volume,
                'quantity' => $quantity,
                'image' => $product['image'] ?? null,
                'stock_quantity' => $product['stock_quantity'] ?? 0
            ];
        }
        
        $this->saveToSession();
        return true;
    }
    
    /**
     * Обновление количества товара
     */
    public function updateQuantity($productId, $quantity, $containerId = null) {
        $cartKey = $this->generateCartKey($productId, $containerId);
        
        if (isset($this->items[$cartKey])) {
            if ($quantity <= 0) {
                unset($this->items[$cartKey]);
            } else {
                $this->items[$cartKey]['quantity'] = $quantity;
            }
            $this->saveToSession();
            return true;
        }
        
        return false;
    }
    
    /**
     * Удаление товара из корзины
     */
    public function removeItem($productId, $containerId = null) {
        $cartKey = $this->generateCartKey($productId, $containerId);
        
        if (isset($this->items[$cartKey])) {
            unset($this->items[$cartKey]);
            $this->saveToSession();
            return true;
        }
        
        return false;
    }
    
    /**
     * Получение всех товаров в корзине
     */
    public function getItems() {
        return $this->items;
    }
    
    /**
     * Получение количества различных товаров
     */
    public function getItemsCount() {
        return count($this->items);
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
     * Получение общей стоимости корзины
     */
    public function getTotalPrice() {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
    
    /**
     * Получение общего объема корзины
     */
    public function getTotalVolume() {
        $total = 0;
        foreach ($this->items as $item) {
            $volume = $item['volume'] ?? 1;
            $total += $volume * $item['quantity'];
        }
        return $total;
    }
    
    /**
     * Проверка, есть ли товар в корзине
     */
    public function hasItem($productId, $containerId = null) {
        $cartKey = $this->generateCartKey($productId, $containerId);
        return isset($this->items[$cartKey]);
    }
    
    /**
     * Получение конкретного товара из корзины
     */
    public function getItem($productId, $containerId = null) {
        $cartKey = $this->generateCartKey($productId, $containerId);
        return $this->items[$cartKey] ?? null;
    }
    
    /**
     * Очистка корзины
     */
    public function clear() {
        $this->items = [];
        $this->saveToSession();
        return true;
    }
    
    /**
     * Проверка, пуста ли корзина
     */
    public function isEmpty() {
        return empty($this->items);
    }
    
    /**
     * Валидация корзины (проверка доступности товаров)
     */
    public function validate() {
        $errors = [];
        $productModel = new Product();
        $containerModel = new ProductContainer();
        
        foreach ($this->items as $cartKey => $item) {
            // Проверяем продукт
            $product = $productModel->getById($item['id']);
            
            if (!$product || !$product['is_active']) {
                $errors[] = "Товар '{$item['name']}' более недоступен";
                unset($this->items[$cartKey]);
                continue;
            }
            
            // Проверяем контейнер, если используется
            if (!empty($item['container_id']) && $item['container_id'] !== 'default') {
                $container = $containerModel->getById($item['container_id']);
                
                if (!$container || !$container['is_active']) {
                    $errors[] = "Выбранный объем для товара '{$item['name']}' недоступен";
                    unset($this->items[$cartKey]);
                    continue;
                }
                
                // Проверяем количество на складе
                if ($container['stock_quantity'] < $item['quantity']) {
                    if ($container['stock_quantity'] > 0) {
                        $this->items[$cartKey]['quantity'] = $container['stock_quantity'];
                        $errors[] = "Количество '{$item['name']}' уменьшено до {$container['stock_quantity']} (доступно на складе)";
                    } else {
                        $errors[] = "Товар '{$item['name']}' закончился на складе";
                        unset($this->items[$cartKey]);
                    }
                }
                
                // Обновляем цену, если изменилась
                if (abs($container['price'] - $item['price']) > 0.01) {
                    $this->items[$cartKey]['price'] = $container['price'];
                    $errors[] = "Цена товара '{$item['name']}' была обновлена";
                }
                
            } else {
                // Проверяем базовый продукт
                if ($product['stock_quantity'] < $item['quantity']) {
                    if ($product['stock_quantity'] > 0) {
                        $this->items[$cartKey]['quantity'] = $product['stock_quantity'];
                        $errors[] = "Количество '{$item['name']}' уменьшено до {$product['stock_quantity']} (доступно на складе)";
                    } else {
                        $errors[] = "Товар '{$item['name']}' закончился на складе";
                        unset($this->items[$cartKey]);
                    }
                }
                
                // Обновляем цену, если изменилась
                if (abs($product['price'] - $item['price']) > 0.01) {
                    $this->items[$cartKey]['price'] = $product['price'];
                    $errors[] = "Цена товара '{$item['name']}' была обновлена";
                }
            }
        }
        
        if (!empty($errors)) {
            $this->saveToSession();
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'updated_items' => $this->items
        ];
    }
    
    /**
     * Преобразование корзины в формат для создания заказа
     */
    public function toOrderItems() {
        $orderItems = [];
        
        foreach ($this->items as $item) {
            $orderItems[] = [
                'product_id' => $item['id'],
                'container_id' => $item['container_id'] === 'default' ? null : $item['container_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'volume' => $item['volume'] ?? 1
            ];
        }
        
        return $orderItems;
    }
    
    /**
     * Генерация ключа для товара в корзине
     */
    private function generateCartKey($productId, $containerId = null) {
        if ($containerId) {
            return $productId . '_' . $containerId;
        }
        return $productId . '_default';
    }
    
    /**
     * Сохранение корзины в сессию
     */
    private function saveToSession() {
        $_SESSION[$this->sessionKey] = $this->items;
    }
    
    /**
     * Загрузка корзины из сессии
     */
    private function loadFromSession() {
        if (isset($_SESSION[$this->sessionKey])) {
            $this->items = $_SESSION[$this->sessionKey];
        }
    }
    
    /**
     * Получение информации о корзине для отладки
     */
    public function getDebugInfo() {
        return [
            'items_count' => $this->getItemsCount(),
            'total_quantity' => $this->getTotalQuantity(),
            'total_price' => $this->getTotalPrice(),
            'total_volume' => $this->getTotalVolume(),
            'items' => $this->items
        ];
    }
}

/**
 * Глобальная функция для получения экземпляра корзины
 */
function cart() {
    static $cart = null;
    if ($cart === null) {
        $cart = new Cart();
    }
    return $cart;
}

/**
 * Быстрое добавление товара в корзину
 */
function add_to_cart($product, $quantity = 1, $containerId = null, $volume = 1) {
    return cart()->addItem($product, $quantity, $containerId, $volume);
}

/**
 * Быстрое получение количества товаров в корзине
 */
function cart_count() {
    return cart()->getTotalQuantity();
}

/**
 * Быстрое получение общей стоимости корзины
 */
function cart_total() {
    return cart()->getTotalPrice();
}

/**
 * Проверка, пуста ли корзина
 */
function cart_is_empty() {
    return cart()->isEmpty();
}

/**
 * Очистка корзины
 */
function clear_cart() {
    return cart()->clear();
}