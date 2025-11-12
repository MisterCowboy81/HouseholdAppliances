<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/User.php';

requireLogin();
requireAdmin();

$userObj = new User();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $fullName = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone'] ?? '');
    $role = sanitize($_POST['role'] ?? 'customer');
    
    $result = $userObj->register($username, $email, $password, $fullName, $phone, $role);
    
    if ($result['success']) {
        setFlashMessage('success', 'کاربر جدید با موفقیت ایجاد شد');
        redirect(ADMIN_URL . '/users.php');
    } else {
        $error = $result['message'];
    }
}

$pageTitle = 'افزودن کاربر جدید';
require_once 'header.php';
?>

<div class="page-header">
    <h1 class="page-title">افزودن کاربر جدید</h1>
    <a href="<?php echo ADMIN_URL; ?>/users.php" class="btn btn-outline">
        <i class="fas fa-arrow-right"></i> بازگشت
    </a>
</div>

<div class="admin-card">
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">نام کاربری <span style="color: red;">*</span></label>
                <input type="text" name="username" class="form-control" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">ایمیل <span style="color: red;">*</span></label>
                <input type="email" name="email" class="form-control" required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">نام و نام خانوادگی <span style="color: red;">*</span></label>
                <input type="text" name="full_name" class="form-control" required 
                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">شماره تلفن</label>
                <input type="text" name="phone" class="form-control" 
                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">رمز عبور <span style="color: red;">*</span></label>
                <input type="password" name="password" class="form-control" required minlength="6">
                <small style="color: var(--text-light);">حداقل 6 کاراکتر</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">نقش کاربر <span style="color: red;">*</span></label>
                <select name="role" class="form-control" required>
                    <option value="customer">مشتری</option>
                    <option value="admin">مدیر</option>
                </select>
            </div>
        </div>
        
        <div style="display: flex; gap: 15px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> ایجاد کاربر
            </button>
            <a href="<?php echo ADMIN_URL; ?>/users.php" class="btn btn-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>
</div>

<?php require_once 'footer.php'; ?>
