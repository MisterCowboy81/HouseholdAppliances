<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/User.php';

requireLogin();
requireAdmin();

$userObj = new User();
$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($userId <= 0) {
    redirect(ADMIN_URL . '/users.php');
}

$user = $userObj->getUserById($userId);
if (!$user) {
    redirect(ADMIN_URL . '/users.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name' => sanitize($_POST['full_name']),
        'phone' => sanitize($_POST['phone'] ?? ''),
        'address' => sanitize($_POST['address'] ?? ''),
        'city' => sanitize($_POST['city'] ?? ''),
        'postal_code' => sanitize($_POST['postal_code'] ?? '')
    ];
    
    $result = $userObj->updateProfile($userId, $data);
    
    if ($result['success']) {
        setFlashMessage('success', 'اطلاعات کاربر با موفقیت بروزرسانی شد');
        redirect(ADMIN_URL . '/users.php');
    } else {
        $error = $result['message'];
    }
}

$pageTitle = 'ویرایش کاربر';
require_once 'header.php';
?>

<div class="page-header">
    <h1 class="page-title">ویرایش کاربر: <?php echo htmlspecialchars($user['full_name']); ?></h1>
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
                <label class="form-label">نام کاربری</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
            </div>
            
            <div class="form-group">
                <label class="form-label">ایمیل</label>
                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
            </div>
            
            <div class="form-group">
                <label class="form-label">نام و نام خانوادگی <span style="color: red;">*</span></label>
                <input type="text" name="full_name" class="form-control" required 
                       value="<?php echo htmlspecialchars($user['full_name']); ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">شماره تلفن</label>
                <input type="text" name="phone" class="form-control" 
                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">شهر</label>
                <input type="text" name="city" class="form-control" 
                       value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">کد پستی</label>
                <input type="text" name="postal_code" class="form-control" 
                       value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">آدرس</label>
            <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">نقش</label>
            <input type="text" class="form-control" value="<?php echo $user['role'] == 'admin' ? 'مدیر' : 'مشتری'; ?>" readonly>
            <small style="color: var(--text-light);">برای تغییر نقش، از پایگاه داده استفاده کنید</small>
        </div>
        
        <div class="form-group">
            <label class="form-label">وضعیت</label>
            <input type="text" class="form-control" value="<?php 
                $statusMap = ['active' => 'فعال', 'inactive' => 'غیرفعال', 'banned' => 'مسدود'];
                echo $statusMap[$user['status']] ?? 'نامشخص';
            ?>" readonly>
            <small style="color: var(--text-light);">برای تغییر وضعیت، از پایگاه داده استفاده کنید</small>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> ذخیره تغییرات
            </button>
            <a href="<?php echo ADMIN_URL; ?>/users.php" class="btn btn-outline">
                لغو
            </a>
        </div>
    </form>
</div>

<?php require_once 'footer.php'; ?>
