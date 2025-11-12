<?php
$pageTitle = 'دفترچه آدرس';
require_once 'header.php';
require_once __DIR__ . '/../includes/Address.php';

requireLogin();

// Prevent admin from accessing
if (isAdmin()) {
    setFlashMessage('error', 'مدیران نمی‌توانند از این بخش استفاده کنند');
    redirect(SITE_URL . '/admin/index.php');
}

$addressObj = new Address();
$userId = getCurrentUserId();
$addresses = $addressObj->getUserAddresses($userId);

$message = '';
$error = '';

// Handle Add Address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_address'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'خطای امنیتی';
    } else {
        $data = [
            'title' => sanitize($_POST['title']),
            'full_name' => sanitize($_POST['full_name']),
            'phone' => sanitize($_POST['phone']),
            'province' => sanitize($_POST['province'] ?? ''),
            'city' => sanitize($_POST['city']),
            'address' => sanitize($_POST['address']),
            'postal_code' => sanitize($_POST['postal_code']),
            'is_default' => isset($_POST['is_default']) ? 1 : 0
        ];
        
        $result = $addressObj->add($userId, $data);
        
        if ($result['success']) {
            setFlashMessage('success', $result['message']);
            redirect(SITE_URL . '/public/addresses.php');
        } else {
            $error = $result['message'];
        }
    }
}

// Handle Delete Address
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $addressId = intval($_GET['delete']);
    $result = $addressObj->delete($addressId, $userId);
    
    if ($result['success']) {
        setFlashMessage('success', $result['message']);
    } else {
        setFlashMessage('error', $result['message']);
    }
    redirect(SITE_URL . '/public/addresses.php');
}

// Handle Set Default
if (isset($_GET['set_default']) && !empty($_GET['set_default'])) {
    $addressId = intval($_GET['set_default']);
    $result = $addressObj->setAsDefault($addressId, $userId);
    
    if ($result['success']) {
        setFlashMessage('success', $result['message']);
    } else {
        setFlashMessage('error', $result['message']);
    }
    redirect(SITE_URL . '/public/addresses.php');
}
?>

<section class="section">
    <div class="container" style="max-width: 1000px;">
        <h1 class="section-title text-center mb-20">دفترچه آدرس</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div style="display: grid; gap: 30px;">
            <!-- Add New Address Form -->
            <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: var(--shadow);">
                <h2 style="margin-bottom: 20px; color: var(--dark-color);">
                    <i class="fas fa-plus-circle"></i> افزودن آدرس جدید
                </h2>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="add_address" value="1">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">عنوان آدرس <span style="color: red;">*</span></label>
                            <input type="text" name="title" class="form-control" required 
                                   placeholder="مثال: منزل، محل کار">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">نام گیرنده <span style="color: red;">*</span></label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">شماره تماس <span style="color: red;">*</span></label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">استان</label>
                            <input type="text" name="province" class="form-control" placeholder="مثال: تهران">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">شهر <span style="color: red;">*</span></label>
                            <input type="text" name="city" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">کد پستی <span style="color: red;">*</span></label>
                            <input type="text" name="postal_code" class="form-control" required 
                                   pattern="[0-9]{10}" placeholder="1234567890">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">آدرس کامل <span style="color: red;">*</span></label>
                        <textarea name="address" class="form-control" required rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="is_default" value="1">
                            <span>تنظیم به عنوان آدرس پیش‌فرض</span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> ذخیره آدرس
                    </button>
                </form>
            </div>
            
            <!-- Saved Addresses -->
            <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: var(--shadow);">
                <h2 style="margin-bottom: 20px; color: var(--dark-color);">
                    <i class="fas fa-map-marked-alt"></i> آدرس‌های ذخیره شده
                </h2>
                
                <?php if (!empty($addresses)): ?>
                    <div style="display: grid; gap: 20px;">
                        <?php foreach ($addresses as $address): ?>
                            <div style="border: 2px solid <?php echo $address['is_default'] ? 'var(--primary-color)' : 'var(--border-color)'; ?>; 
                                        border-radius: 12px; padding: 20px; position: relative;
                                        <?php echo $address['is_default'] ? 'background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);' : ''; ?>">
                                
                                <?php if ($address['is_default']): ?>
                                    <div style="position: absolute; top: 10px; left: 10px; 
                                                background: var(--primary-color); color: white; 
                                                padding: 5px 15px; border-radius: 20px; font-size: 0.85rem;">
                                        <i class="fas fa-star"></i> پیش‌فرض
                                    </div>
                                <?php endif; ?>
                                
                                <h3 style="margin-bottom: 15px; color: var(--dark-color);">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($address['title']); ?>
                                </h3>
                                
                                <div style="display: grid; gap: 10px; margin-bottom: 20px;">
                                    <div><strong>گیرنده:</strong> <?php echo htmlspecialchars($address['full_name']); ?></div>
                                    <div><strong>تلفن:</strong> <?php echo htmlspecialchars($address['phone']); ?></div>
                                    <?php if ($address['province']): ?>
                                        <div><strong>استان:</strong> <?php echo htmlspecialchars($address['province']); ?></div>
                                    <?php endif; ?>
                                    <div><strong>شهر:</strong> <?php echo htmlspecialchars($address['city']); ?></div>
                                    <div><strong>آدرس:</strong> <?php echo htmlspecialchars($address['address']); ?></div>
                                    <div><strong>کد پستی:</strong> <?php echo htmlspecialchars($address['postal_code']); ?></div>
                                </div>
                                
                                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                    <?php if (!$address['is_default']): ?>
                                        <a href="?set_default=<?php echo $address['id']; ?>" 
                                           class="btn btn-outline btn-sm"
                                           onclick="return confirm('آیا می‌خواهید این آدرس را به عنوان پیش‌فرض تنظیم کنید؟')">
                                            <i class="fas fa-star"></i> تنظیم به عنوان پیش‌فرض
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="?delete=<?php echo $address['id']; ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('آیا مطمئن هستید که می‌خواهید این آدرس را حذف کنید؟')">
                                        <i class="fas fa-trash"></i> حذف
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: var(--text-light);">
                        <i class="fas fa-map-marked-alt" style="font-size: 3rem; margin-bottom: 15px;"></i>
                        <p>هنوز آدرسی ذخیره نکرده‌اید</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="<?php echo SITE_URL; ?>/public/profile.php" class="btn btn-outline">
                <i class="fas fa-arrow-right"></i> بازگشت به پروفایل
            </a>
        </div>
    </div>
</section>

<style>
.btn-sm {
    padding: 8px 15px;
    font-size: 0.9rem;
}
</style>

<?php require_once 'footer.php'; ?>
