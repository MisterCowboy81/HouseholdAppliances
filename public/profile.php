<?php
$pageTitle = 'پروفایل کاربر';
require_once 'header.php';
require_once __DIR__ . '/../includes/User.php';
require_once __DIR__ . '/../includes/Order.php';

requireLogin();

$userObj = new User();
$orderObj = new Order();

$user = getCurrentUser();
$orders = $orderObj->getUserOrders($user['id'], 10, 0);

$message = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $data = [
        'full_name' => sanitize($_POST['full_name']),
        'phone' => sanitize($_POST['phone']),
        'address' => sanitize($_POST['address']),
        'city' => sanitize($_POST['city']),
        'postal_code' => sanitize($_POST['postal_code'])
    ];
    
    $result = $userObj->updateProfile($user['id'], $data);
    
    if ($result['success']) {
        setFlashMessage('success', $result['message']);
        redirect(SITE_URL . '/public/profile.php');
    } else {
        $error = $result['message'];
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if ($newPassword !== $confirmPassword) {
        $error = 'رمز عبور جدید و تکرار آن یکسان نیستند';
    } else {
        $result = $userObj->changePassword($user['id'], $oldPassword, $newPassword);
        
        if ($result['success']) {
            setFlashMessage('success', $result['message']);
            redirect(SITE_URL . '/public/profile.php');
        } else {
            $error = $result['message'];
        }
    }
}

// Refresh user data
$user = getCurrentUser();
?>

<section class="section">
    <div class="container">
        <h1 class="section-title text-center mb-20">پروفایل کاربری</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="profile-layout" style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
            <!-- Sidebar -->
            <div>
                <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: var(--shadow); margin-bottom: 20px;">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <div style="width: 100px; height: 100px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; color: white; font-size: 3rem;">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                        <p style="color: var(--text-light);"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    
                    <div style="border-top: 1px solid var(--border-color); padding-top: 20px;">
                        <a href="<?php echo SITE_URL; ?>/public/addresses.php" class="btn btn-primary" style="width: 100%; text-align: center; margin-bottom: 10px;">
                            <i class="fas fa-map-marked-alt"></i> دفترچه آدرس
                        </a>
                        <a href="<?php echo SITE_URL; ?>/public/logout.php" class="btn btn-danger" style="width: 100%; text-align: center;">
                            <i class="fas fa-sign-out-alt"></i> خروج
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div>
                <!-- Profile Info -->
                <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: var(--shadow); margin-bottom: 30px;">
                    <h2 style="margin-bottom: 20px; color: var(--dark-color);">اطلاعات کاربری</h2>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="update_profile" value="1">
                        
                        <div class="form-group">
                            <label class="form-label">نام و نام خانوادگی</label>
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
                            <label class="form-label">آدرس</label>
                            <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">کد پستی</label>
                            <input type="text" name="postal_code" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">بروزرسانی اطلاعات</button>
                    </form>
                </div>
                
                <!-- Change Password -->
                <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: var(--shadow); margin-bottom: 30px;">
                    <h2 style="margin-bottom: 20px; color: var(--dark-color);">تغییر رمز عبور</h2>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="change_password" value="1">
                        
                        <div class="form-group">
                            <label class="form-label">رمز عبور فعلی</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">رمز عبور جدید</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">تکرار رمز عبور جدید</label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="6">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">تغییر رمز عبور</button>
                    </form>
                </div>
                
                <!-- Orders History -->
                <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: var(--shadow);">
                    <h2 style="margin-bottom: 20px; color: var(--dark-color);">سفارش‌های من</h2>
                    
                    <?php if (!empty($orders)): ?>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: var(--light-color);">
                                        <th style="padding: 12px; text-align: right; border-bottom: 2px solid var(--border-color);">شماره سفارش</th>
                                        <th style="padding: 12px; text-align: right; border-bottom: 2px solid var(--border-color);">تاریخ</th>
                                        <th style="padding: 12px; text-align: right; border-bottom: 2px solid var(--border-color);">مبلغ</th>
                                        <th style="padding: 12px; text-align: right; border-bottom: 2px solid var(--border-color);">وضعیت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td style="padding: 12px; border-bottom: 1px solid var(--border-color);">
                                                <?php echo htmlspecialchars($order['order_number']); ?>
                                            </td>
                                            <td style="padding: 12px; border-bottom: 1px solid var(--border-color);">
                                                <?php echo date('Y/m/d', strtotime($order['created_at'])); ?>
                                            </td>
                                            <td style="padding: 12px; border-bottom: 1px solid var(--border-color);">
                                                <?php echo formatPrice($order['total_amount']); ?>
                                            </td>
                                            <td style="padding: 12px; border-bottom: 1px solid var(--border-color);">
                                                <?php
                                                $statusMap = [
                                                    'pending' => ['در انتظار', 'badge-warning'],
                                                    'processing' => ['در حال پردازش', 'badge-info'],
                                                    'shipped' => ['ارسال شده', 'badge-info'],
                                                    'delivered' => ['تحویل داده شده', 'badge-success'],
                                                    'cancelled' => ['لغو شده', 'badge-danger']
                                                ];
                                                $status = $statusMap[$order['status']] ?? ['نامشخص', 'badge-secondary'];
                                                ?>
                                                <span class="badge <?php echo $status[1]; ?>"><?php echo $status[0]; ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p style="text-align: center; color: var(--text-light); padding: 40px;">هنوز سفارشی ثبت نکرده‌اید</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>
