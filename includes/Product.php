<?php
/**
 * Product Management
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

class Product {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Get all products with optional filters
     */
    public function getProducts($filters = [], $limit = 20, $offset = 0) {
        $where = [];
        $params = [];
        $types = '';
        
        // Only filter by active status if not showing all
        if (!isset($filters['show_all']) || !$filters['show_all']) {
            $where[] = "p.status = 'active'";
        }
        
        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = ?";
            $params[] = $filters['category_id'];
            $types .= 'i';
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(p.name LIKE ? OR p.description LIKE ? OR p.brand LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'sss';
        }
        
        if (!empty($filters['min_price'])) {
            $where[] = "p.price >= ?";
            $params[] = $filters['min_price'];
            $types .= 'd';
        }
        
        if (!empty($filters['max_price'])) {
            $where[] = "p.price <= ?";
            $params[] = $filters['max_price'];
            $types .= 'd';
        }
        
        if (isset($filters['featured']) && $filters['featured']) {
            $where[] = "p.featured = 1";
        }
        
        $whereClause = !empty($where) ? implode(' AND ', $where) : '1=1';
        $orderBy = $filters['order_by'] ?? 'p.created_at DESC';
        
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE $whereClause 
                ORDER BY $orderBy 
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
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        return $products;
    }
    
    /**
     * Get product by ID
     */
    public function getProductById($id) {
        $stmt = $this->db->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Get featured products
     */
    public function getFeaturedProducts($limit = 6) {
        return $this->getProducts(['featured' => true], $limit);
    }
    
    /**
     * Get latest products
     */
    public function getLatestProducts($limit = 8) {
        return $this->getProducts([], $limit);
    }
    
    /**
     * Create product (admin)
     */
    public function createProduct($data) {
        $stmt = $this->db->prepare("INSERT INTO products (category_id, name, description, price, discount_price, stock, image, brand, model, warranty, status, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param(
            "issddisisssi",
            $data['category_id'],
            $data['name'],
            $data['description'],
            $data['price'],
            $data['discount_price'],
            $data['stock'],
            $data['image'],
            $data['brand'],
            $data['model'],
            $data['warranty'],
            $data['status'],
            $data['featured']
        );
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'محصول با موفقیت ایجاد شد', 'product_id' => $this->db->lastInsertId()];
        }
        
        return ['success' => false, 'message' => 'خطا در ایجاد محصول'];
    }
    
    /**
     * Update product (admin)
     */
    public function updateProduct($id, $data) {
        $fields = [];
        $params = [];
        $types = '';
        
        $allowedFields = ['category_id' => 'i', 'name' => 's', 'description' => 's', 'price' => 'd', 
                          'discount_price' => 'd', 'stock' => 'i', 'image' => 's', 'brand' => 's', 
                          'model' => 's', 'warranty' => 's', 'status' => 's', 'featured' => 'i'];
        
        foreach ($allowedFields as $field => $type) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
                $types .= $type;
            }
        }
        
        if (empty($fields)) {
            return ['success' => false, 'message' => 'هیچ داده ای برای بروزرسانی وجود ندارد'];
        }
        
        $params[] = $id;
        $types .= 'i';
        
        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'محصول با موفقیت بروزرسانی شد'];
        }
        
        return ['success' => false, 'message' => 'خطا در بروزرسانی محصول'];
    }
    
    /**
     * Delete product (admin)
     */
    public function deleteProduct($id) {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'محصول با موفقیت حذف شد'];
        }
        
        return ['success' => false, 'message' => 'خطا در حذف محصول'];
    }
    
    /**
     * Update stock
     */
    public function updateStock($id, $quantity) {
        $stmt = $this->db->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $id);
        return $stmt->execute();
    }
    
    /**
     * Check stock availability
     */
    public function checkStock($id, $quantity) {
        $stmt = $this->db->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }
        
        $product = $result->fetch_assoc();
        return $product['stock'] >= $quantity;
    }
    
    /**
     * Get total product count
     */
    public function getTotalCount($filters = []) {
        $where = ["status = 'active'"];
        $params = [];
        $types = '';
        
        if (!empty($filters['category_id'])) {
            $where[] = "category_id = ?";
            $params[] = $filters['category_id'];
            $types .= 'i';
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(name LIKE ? OR description LIKE ? OR brand LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'sss';
        }
        
        $whereClause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) as total FROM products WHERE $whereClause";
        
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'];
    }
}
