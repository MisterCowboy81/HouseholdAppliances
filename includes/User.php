<?php
/**
 * User Authentication and Management
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Register new user
     */
    public function register($username, $email, $password, $fullName, $phone = null, $role = 'customer') {
        // Validate input
        if (empty($username) || empty($email) || empty($password) || empty($fullName)) {
            return ['success' => false, 'message' => 'لطفا تمام فیلدهای الزامی را پر کنید'];
        }
        
        if (!validateEmail($email)) {
            return ['success' => false, 'message' => 'ایمیل نامعتبر است'];
        }
        
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'رمز عبور باید حداقل 6 کاراکتر باشد'];
        }
        
        // Check if username exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'نام کاربری قبلا استفاده شده است'];
        }
        
        // Check if email exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'ایمیل قبلا ثبت شده است'];
        }
        
        // Hash password
        $hashedPassword = hashPassword($password);
        
        // Insert user
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, full_name, phone, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $email, $hashedPassword, $fullName, $phone, $role);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'ثبت نام با موفقیت انجام شد', 'user_id' => $this->db->lastInsertId()];
        }
        
        return ['success' => false, 'message' => 'خطا در ثبت نام'];
    }
    
    /**
     * Login user
     */
    public function login($username, $password) {
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'لطفا نام کاربری و رمز عبور را وارد کنید'];
        }
        
        // Get user
        $stmt = $this->db->prepare("SELECT id, username, email, password, full_name, role, status FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'نام کاربری یا رمز عبور اشتباه است'];
        }
        
        $user = $result->fetch_assoc();
        
        // Check status
        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'حساب کاربری شما غیرفعال است'];
        }
        
        // Verify password
        if (!verifyPassword($password, $user['password'])) {
            return ['success' => false, 'message' => 'نام کاربری یا رمز عبور اشتباه است'];
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        
        return ['success' => true, 'message' => 'ورود موفقیت آمیز', 'user' => $user];
    }
    
    /**
     * Logout user
     */
    public function logout() {
        session_unset();
        session_destroy();
        return ['success' => true, 'message' => 'خروج موفقیت آمیز'];
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        $allowedFields = ['full_name', 'phone', 'address', 'city', 'postal_code'];
        $updates = [];
        $params = [];
        $types = '';
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
                $types .= 's';
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'message' => 'هیچ داده ای برای بروزرسانی وجود ندارد'];
        }
        
        $params[] = $userId;
        $types .= 'i';
        
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'پروفایل با موفقیت بروزرسانی شد'];
        }
        
        return ['success' => false, 'message' => 'خطا در بروزرسانی پروفایل'];
    }
    
    /**
     * Change password
     */
    public function changePassword($userId, $oldPassword, $newPassword) {
        if (strlen($newPassword) < 6) {
            return ['success' => false, 'message' => 'رمز عبور جدید باید حداقل 6 کاراکتر باشد'];
        }
        
        // Get current password
        $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'کاربر یافت نشد'];
        }
        
        $user = $result->fetch_assoc();
        
        // Verify old password
        if (!verifyPassword($oldPassword, $user['password'])) {
            return ['success' => false, 'message' => 'رمز عبور فعلی اشتباه است'];
        }
        
        // Update password
        $hashedPassword = hashPassword($newPassword);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'رمز عبور با موفقیت تغییر کرد'];
        }
        
        return ['success' => false, 'message' => 'خطا در تغییر رمز عبور'];
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        $stmt = $this->db->prepare("SELECT id, username, email, full_name, phone, address, city, postal_code, role, status, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Get all users (admin only)
     */
    public function getAllUsers($limit = 100, $offset = 0) {
        $stmt = $this->db->prepare("SELECT id, username, email, full_name, phone, role, status, created_at FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        return $users;
    }
    
    /**
     * Delete user (admin only)
     */
    public function deleteUser($userId) {
        // Prevent deleting admin users (optional safety check)
        $user = $this->getUserById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'کاربر یافت نشد'];
        }
        
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'کاربر با موفقیت حذف شد'];
        }
        
        return ['success' => false, 'message' => 'خطا در حذف کاربر'];
    }
}
