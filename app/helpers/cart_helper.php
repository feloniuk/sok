<?php
// app/helpers/cart_helper.php - Helper functions for shopping cart

/**
 * Cart class to manage shopping cart functionality
 */
class Cart {
    private static $instance = null;
    private $items = [];
    
    /**
     * Private constructor for singleton pattern
     */
    private function __construct() {
        $this->loadFromSession();
    }
    
    /**
     * Get instance of Cart (singleton pattern)
     *
     * @return Cart
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Load cart data from session
     */
    private function loadFromSession() {
        if (isset($_SESSION['cart_items']) && is_array($_SESSION['cart_items'])) {
            $this->items = $_SESSION['cart_items'];
        }
    }
    
    /**
     * Save cart data to session
     */
    private function saveToSession() {
        $_SESSION['cart_items'] = $this->items;
    }
    
    /**
     * Add item to cart
     *
     * @param array $product Product data
     * @param int $quantity Quantity to add
     * @return bool Success status
     */
    public function addItem($product, $quantity = 1) {
        if (!isset($product['id']) || !is_numeric($product['id'])) {
            return false;
        }
        
        $productId = $product['id'];
        
        // If item already exists in cart, update quantity
        if (isset($this->items[$productId])) {
            $this->items[$productId]['quantity'] += $quantity;
        } else {
            // Add new item
            $this->items[$productId] = [
                'id' => $productId,
                'name' => $product['name'] ?? 'Unknown Product',
                'price' => $product['price'] ?? 0,
                'quantity' => $quantity,
                'image' => $product['image'] ?? null,
                'stock_quantity' => $product['stock_quantity'] ?? 0
            ];
        }
        
        $this->saveToSession();
        return true;
    }
    
    /**
     * Update item quantity
     *
     * @param int $productId Product ID
     * @param int $quantity New quantity
     * @return bool Success status
     */
    public function updateQuantity($productId, $quantity) {
        if (!isset($this->items[$productId])) {
            return false;
        }
        
        if ($quantity <= 0) {
            return $this->removeItem($productId);
        }
        
        // Check if quantity exceeds stock
        if (isset($this->items[$productId]['stock_quantity']) && $quantity > $this->items[$productId]['stock_quantity']) {
            $quantity = $this->items[$productId]['stock_quantity'];
        }
        
        $this->items[$productId]['quantity'] = $quantity;
        $this->saveToSession();
        
        return true;
    }
    
    /**
     * Remove item from cart
     *
     * @param int $productId Product ID
     * @return bool Success status
     */
    public function removeItem($productId) {
        if (isset($this->items[$productId])) {
            unset($this->items[$productId]);
            $this->saveToSession();
            return true;
        }
        
        return false;
    }
    
    /**
     * Clear all items from cart
     */
    public function clear() {
        $this->items = [];
        $this->saveToSession();
    }
    
    /**
     * Get all items in cart
     *
     * @return array Cart items
     */
    public function getItems() {
        return $this->items;
    }
    
    /**
     * Get item count in cart
     *
     * @return int Number of items in cart
     */
    public function getItemCount() {
        return count($this->items);
    }
    
    /**
     * Get total quantity of all items
     *
     * @return int Total quantity
     */
    public function getTotalQuantity() {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item['quantity'];
        }
        return $total;
    }
    
    /**
     * Get total price of cart
     *
     * @return float Total price
     */
    public function getTotalPrice() {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
}

/**
 * Get cart instance
 *
 * @return Cart
 */
function cart() {
    return Cart::getInstance();
}