<?php
/**
 * Address Management Class
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

class Address {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Add new address
     */
    public function add($userId, $data) {
        // Validate required fields
        $requiredFields = ['title', 'full_name', 'phone', 'city', 'address', 'postal_code'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => 'لطفا تمام فیلدهای الزامی را پر کنید'];
            }
        }
        
        // If this is set as default, unset other defaults first
        if (!empty($data['is_default'])) {
            $this->db->query("UPDATE user_addresses SET is_default = 0 WHERE user_id = $userId");
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO user_addresses (user_id, title, full_name, phone, province, city, address, postal_code, is_default) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $isDefault = !empty($data['is_default']) ? 1 : 0;
        
        $stmt->bind_param(
            "isssssssi",
            $userId,
            $data['title'],
            $data['full_name'],
            $data['phone'],
            $data['province'] ?? '',
            $data['city'],
            $data['address'],
            $data['postal_code'],
            $isDefault
        );
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'آدرس با موفقیت اضافه شد', 'address_id' => $this->db->lastInsertId()];
        }
        
        return ['success' => false, 'message' => 'خطا در افزودن آدرس'];
    }
    
    /**
     * Update address
     */
    public function update($addressId, $userId, $data) {
        // Verify ownership
        if (!$this->verifyOwnership($addressId, $userId)) {
            return ['success' => false, 'message' => 'دسترسی غیرمجاز'];
        }
        
        // If this is set as default, unset other defaults first
        if (!empty($data['is_default'])) {
            $this->db->query("UPDATE user_addresses SET is_default = 0 WHERE user_id = $userId");
        }
        
        $stmt = $this->db->prepare("
            UPDATE user_addresses 
            SET title = ?, full_name = ?, phone = ?, province = ?, city = ?, address = ?, postal_code = ?, is_default = ?
            WHERE id = ? AND user_id = ?
        ");
        
        $isDefault = !empty($data['is_default']) ? 1 : 0;
        
        $stmt->bind_param(
            "ssssssssii",
            $data['title'],
            $data['full_name'],
            $data['phone'],
            $data['province'] ?? '',
            $data['city'],
            $data['address'],
            $data['postal_code'],
            $isDefault,
            $addressId,
            $userId
        );
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'آدرس با موفقیت بروزرسانی شد'];
        }
        
        return ['success' => false, 'message' => 'خطا در بروزرسانی آدرس'];
    }
    
    /**
     * Delete address
     */
    public function delete($addressId, $userId) {
        // Verify ownership
        if (!$this->verifyOwnership($addressId, $userId)) {
            return ['success' => false, 'message' => 'دسترسی غیرمجاز'];
        }
        
        $stmt = $this->db->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $addressId, $userId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'آدرس با موفقیت حذف شد'];
        }
        
        return ['success' => false, 'message' => 'خطا در حذف آدرس'];
    }
    
    /**
     * Get all addresses for a user
     */
    public function getUserAddresses($userId) {
        $stmt = $this->db->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $addresses = [];
        while ($row = $result->fetch_assoc()) {
            $addresses[] = $row;
        }
        
        return $addresses;
    }
    
    /**
     * Get address by ID
     */
    public function getById($addressId, $userId = null) {
        if ($userId !== null) {
            $stmt = $this->db->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $addressId, $userId);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM user_addresses WHERE id = ?");
            $stmt->bind_param("i", $addressId);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Get default address for user
     */
    public function getDefaultAddress($userId) {
        $stmt = $this->db->prepare("SELECT * FROM user_addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Set address as default
     */
    public function setAsDefault($addressId, $userId) {
        // Verify ownership
        if (!$this->verifyOwnership($addressId, $userId)) {
            return ['success' => false, 'message' => 'دسترسی غیرمجاز'];
        }
        
        // Unset all defaults for this user
        $this->db->query("UPDATE user_addresses SET is_default = 0 WHERE user_id = $userId");
        
        // Set this as default
        $stmt = $this->db->prepare("UPDATE user_addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $addressId, $userId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'آدرس به عنوان پیش‌فرض تنظیم شد'];
        }
        
        return ['success' => false, 'message' => 'خطا در تنظیم آدرس پیش‌فرض'];
    }
    
    /**
     * Verify address ownership
     */
    private function verifyOwnership($addressId, $userId) {
        $stmt = $this->db->prepare("SELECT id FROM user_addresses WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $addressId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
}
