<?php
/**
 * Order Management
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

class Order {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Create new order
     */
    public function create($userId, $cartItems, $shippingData, $paymentMethod = 'cod') {
        if (empty($cartItems)) {
            return ['success' => false, 'message' => 'سبد خرید خالی است'];
        }
        
        // Validate shipping data
        $requiredFields = ['address', 'city', 'postal_code', 'phone'];
        foreach ($requiredFields as $field) {
            if (empty($shippingData[$field])) {
                return ['success' => false, 'message' => 'لطفا تمام فیلدهای آدرس را پر کنید'];
            }
        }
        
        // Calculate total
        $totalAmount = 0;
        foreach ($cartItems as $item) {
            $totalAmount += $item['subtotal'];
        }
        
        // Generate order number
        $orderNumber = generateOrderNumber();
        
        // Begin transaction
        $this->db->getConnection()->begin_transaction();
        
        try {
            // Insert order
            $stmt = $this->db->prepare("
                INSERT INTO orders (user_id, order_number, total_amount, shipping_address, shipping_city, 
                                   shipping_postal_code, phone, payment_method, status, payment_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')
            ");
            
            $stmt->bind_param(
                "isdsssss",
                $userId,
                $orderNumber,
                $totalAmount,
                $shippingData['address'],
                $shippingData['city'],
                $shippingData['postal_code'],
                $shippingData['phone'],
                $paymentMethod
            );
            
            $stmt->execute();
            $orderId = $this->db->lastInsertId();
            
            // Insert order items and update stock
            $stmt = $this->db->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($cartItems as $item) {
                $stmt->bind_param(
                    "iisdid",
                    $orderId,
                    $item['product_id'],
                    $item['name'],
                    $item['final_price'],
                    $item['quantity'],
                    $item['subtotal']
                );
                $stmt->execute();
                
                // Update product stock
                $updateStmt = $this->db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $updateStmt->bind_param("ii", $item['quantity'], $item['product_id']);
                $updateStmt->execute();
            }
            
            // Commit transaction
            $this->db->getConnection()->commit();
            
            return [
                'success' => true,
                'message' => 'سفارش با موفقیت ثبت شد',
                'order_id' => $orderId,
                'order_number' => $orderNumber
            ];
            
        } catch (Exception $e) {
            // Rollback on error
            $this->db->getConnection()->rollback();
            return ['success' => false, 'message' => 'خطا در ثبت سفارش: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get order by ID
     */
    public function getById($orderId, $userId = null) {
        if ($userId !== null) {
            $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $orderId, $userId);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
            $stmt->bind_param("i", $orderId);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Get order items
     */
    public function getItems($orderId) {
        $stmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        return $items;
    }
    
    /**
     * Get user orders
     */
    public function getUserOrders($userId, $limit = 20, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT * FROM orders 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iii", $userId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
        return $orders;
    }
    
    /**
     * Get all orders (admin)
     */
    public function getAllOrders($filters = [], $limit = 50, $offset = 0) {
        $where = [];
        $params = [];
        $types = '';
        
        if (!empty($filters['status'])) {
            $where[] = "o.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }
        
        if (!empty($filters['payment_status'])) {
            $where[] = "o.payment_status = ?";
            $params[] = $filters['payment_status'];
            $types .= 's';
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(order_number LIKE ? OR phone LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'ss';
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT o.*, u.username, u.full_name 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                $whereClause 
                ORDER BY o.created_at DESC 
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
        
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
        return $orders;
    }
    
    /**
     * Update order status
     */
    public function updateStatus($orderId, $status) {
        $allowedStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $allowedStatuses)) {
            return ['success' => false, 'message' => 'وضعیت نامعتبر است'];
        }
        
        $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $orderId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'وضعیت سفارش بروزرسانی شد'];
        }
        
        return ['success' => false, 'message' => 'خطا در بروزرسانی وضعیت'];
    }
    
    /**
     * Update payment status
     */
    public function updatePaymentStatus($orderId, $paymentStatus) {
        $allowedStatuses = ['pending', 'paid', 'failed'];
        
        if (!in_array($paymentStatus, $allowedStatuses)) {
            return ['success' => false, 'message' => 'وضعیت پرداخت نامعتبر است'];
        }
        
        $stmt = $this->db->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
        $stmt->bind_param("si", $paymentStatus, $orderId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'وضعیت پرداخت بروزرسانی شد'];
        }
        
        return ['success' => false, 'message' => 'خطا در بروزرسانی وضعیت پرداخت'];
    }
    
    /**
     * Get order statistics (admin)
     */
    public function getStatistics() {
        $stats = [];
        
        // Total orders
        $result = $this->db->query("SELECT COUNT(*) as total FROM orders");
        $stats['total_orders'] = $result->fetch_assoc()['total'];
        
        // Pending orders
        $result = $this->db->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
        $stats['pending_orders'] = $result->fetch_assoc()['total'];
        
        // Total revenue
        $result = $this->db->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'");
        $stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;
        
        // Today's orders
        $result = $this->db->query("SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()");
        $stats['today_orders'] = $result->fetch_assoc()['total'];
        
        return $stats;
    }
}
