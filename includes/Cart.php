<?php
/**
 * Shopping Cart Management
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

class Cart {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Add item to cart
     */
    public function addItem($productId, $quantity = 1) {
        $userId = getCurrentUserId();
        $sessionId = getCartSessionId();
        
        // Check if product exists and has stock
        $stmt = $this->db->prepare("SELECT id, stock FROM products WHERE id = ? AND status = 'active'");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'محصول یافت نشد'];
        }
        
        $product = $result->fetch_assoc();
        
        if ($product['stock'] < $quantity) {
            return ['success' => false, 'message' => 'موجودی کافی نیست'];
        }
        
        // Check if item already in cart
        $stmt = $this->db->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ?");
        $stmt->bind_param("si", $sessionId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update quantity
            $cartItem = $result->fetch_assoc();
            $newQuantity = $cartItem['quantity'] + $quantity;
            
            if ($product['stock'] < $newQuantity) {
                return ['success' => false, 'message' => 'موجودی کافی نیست'];
            }
            
            $stmt = $this->db->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("ii", $newQuantity, $cartItem['id']);
            $stmt->execute();
        } else {
            // Insert new item
            $stmt = $this->db->prepare("INSERT INTO cart (user_id, session_id, product_id, quantity) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isii", $userId, $sessionId, $productId, $quantity);
            $stmt->execute();
        }
        
        return ['success' => true, 'message' => 'محصول به سبد خرید اضافه شد'];
    }
    
    /**
     * Update cart item quantity
     */
    public function updateQuantity($productId, $quantity) {
        $sessionId = getCartSessionId();
        
        if ($quantity <= 0) {
            return $this->removeItem($productId);
        }
        
        // Check stock
        $stmt = $this->db->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'محصول یافت نشد'];
        }
        
        $product = $result->fetch_assoc();
        
        if ($product['stock'] < $quantity) {
            return ['success' => false, 'message' => 'موجودی کافی نیست'];
        }
        
        $stmt = $this->db->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE session_id = ? AND product_id = ?");
        $stmt->bind_param("isi", $quantity, $sessionId, $productId);
        
        if ($stmt->execute()) {
            // Get updated item details
            $stmt = $this->db->prepare("
                SELECT 
                    c.quantity,
                    COALESCE(p.discount_price, p.price) as final_price,
                    (c.quantity * COALESCE(p.discount_price, p.price)) as subtotal
                FROM cart c
                INNER JOIN products p ON c.product_id = p.id
                WHERE c.session_id = ? AND c.product_id = ?
            ");
            $stmt->bind_param("si", $sessionId, $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            $item = $result->fetch_assoc();
            
            // Get total cart amount
            $total = $this->getTotal();
            
            return [
                'success' => true, 
                'message' => 'سبد خرید بروزرسانی شد',
                'subtotal' => $item['subtotal'] ?? 0,
                'total' => $total
            ];
        }
        
        return ['success' => false, 'message' => 'خطا در بروزرسانی'];
    }
    
    /**
     * Remove item from cart
     */
    public function removeItem($productId) {
        $sessionId = getCartSessionId();
        
        $stmt = $this->db->prepare("DELETE FROM cart WHERE session_id = ? AND product_id = ?");
        $stmt->bind_param("si", $sessionId, $productId);
        
        if ($stmt->execute()) {
            // Get updated total cart amount
            $total = $this->getTotal();
            $count = $this->getCount();
            
            return [
                'success' => true, 
                'message' => 'محصول از سبد خرید حذف شد',
                'total' => $total,
                'count' => $count
            ];
        }
        
        return ['success' => false, 'message' => 'خطا در حذف محصول'];
    }
    
    /**
     * Get cart items
     */
    public function getItems() {
        $sessionId = getCartSessionId();
        
        $stmt = $this->db->prepare("
            SELECT c.*, p.name, p.price, p.discount_price, p.image, p.stock,
                   (CASE WHEN p.discount_price IS NOT NULL THEN p.discount_price ELSE p.price END) as final_price,
                   (CASE WHEN p.discount_price IS NOT NULL THEN p.discount_price ELSE p.price END) * c.quantity as subtotal
            FROM cart c
            INNER JOIN products p ON c.product_id = p.id
            WHERE c.session_id = ? AND p.status = 'active'
            ORDER BY c.created_at DESC
        ");
        $stmt->bind_param("s", $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        return $items;
    }
    
    /**
     * Get cart total
     */
    public function getTotal() {
        $items = $this->getItems();
        $total = 0;
        
        foreach ($items as $item) {
            $total += $item['subtotal'];
        }
        
        return $total;
    }
    
    /**
     * Get cart count
     */
    public function getCount() {
        $sessionId = getCartSessionId();
        
        $stmt = $this->db->prepare("SELECT SUM(quantity) as total FROM cart WHERE session_id = ?");
        $stmt->bind_param("s", $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'] ?? 0;
    }
    
    /**
     * Clear cart
     */
    public function clear() {
        $sessionId = getCartSessionId();
        
        $stmt = $this->db->prepare("DELETE FROM cart WHERE session_id = ?");
        $stmt->bind_param("s", $sessionId);
        
        return $stmt->execute();
    }
    
    /**
     * Transfer cart from session to user (after login)
     */
    public function transferToUser($userId) {
        $sessionId = getCartSessionId();
        
        $stmt = $this->db->prepare("UPDATE cart SET user_id = ? WHERE session_id = ? AND user_id IS NULL");
        $stmt->bind_param("is", $userId, $sessionId);
        
        return $stmt->execute();
    }
}
