<?php
/**
 * Category Management
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

class Category {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Get all categories
     */
    public function getAll($status = 'active') {
        if ($status === 'all') {
            $stmt = $this->db->prepare("SELECT * FROM categories ORDER BY name ASC");
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare("SELECT * FROM categories WHERE status = ? ORDER BY name ASC");
            $stmt->bind_param("s", $status);
            $stmt->execute();
        }
        
        $result = $stmt->get_result();
        $categories = [];
        
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        
        return $categories;
    }
    
    /**
     * Get category by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Create category
     */
    public function create($name, $description = null, $image = null, $parentId = null) {
        $status = 'active';
        $stmt = $this->db->prepare("INSERT INTO categories (name, description, image, parent_id, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssis", $name, $description, $image, $parentId, $status);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'دسته‌بندی با موفقیت ایجاد شد', 'category_id' => $this->db->lastInsertId()];
        }
        
        return ['success' => false, 'message' => 'خطا در ایجاد دسته‌بندی'];
    }
    
    /**
     * Update category
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        $types = '';
        
        $allowedFields = ['name' => 's', 'description' => 's', 'image' => 's', 'parent_id' => 'i', 'status' => 's'];
        
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
        
        $sql = "UPDATE categories SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'دسته‌بندی با موفقیت بروزرسانی شد'];
        }
        
        return ['success' => false, 'message' => 'خطا در بروزرسانی دسته‌بندی'];
    }
    
    /**
     * Delete category
     */
    public function delete($id) {
        // Check if category has products
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            return ['success' => false, 'message' => 'نمی‌توان دسته‌بندی دارای محصول را حذف کرد'];
        }
        
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'دسته‌بندی با موفقیت حذف شد'];
        }
        
        return ['success' => false, 'message' => 'خطا در حذف دسته‌بندی'];
    }
    
    /**
     * Get categories with product count
     */
    public function getCategoriesWithCount() {
        $stmt = $this->db->prepare("
            SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
            WHERE c.status = 'active'
            GROUP BY c.id 
            ORDER BY c.name ASC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        
        return $categories;
    }
}
