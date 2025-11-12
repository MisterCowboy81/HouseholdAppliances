<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/Cart.php';
require_once __DIR__ . '/../includes/Order.php';
require_once __DIR__ . '/../includes/Address.php';

requireLogin();

// Prevent admin from accessing checkout
if (isAdmin()) {
    setFlashMessage('error', 'مدیران نمی‌توانند از بخش خرید استفاده کنند');
    redirect(SITE_URL . '/admin/index.php');
}

$cart = new Cart();
$cartItems = $cart->getItems();
$cartTotal = $cart->getTotal();

if (empty($cartItems)) {
    setFlashMessage('error', 'سبد خرید شما خالی است');
    redirect(SITE_URL . '/public/cart.php');
}

$error = '';
$orderObj = new Order();
$addressObj = new Address();
$userId = getCurrentUserId();

// Get saved addresses
$savedAddresses = $addressObj->getUserAddresses($userId);
$defaultAddress = $addressObj->getDefaultAddress($userId);

if (empty($cartItems)) {
    setFlashMessage('error', 'سبد خرید شما خالی است');
    redirect(SITE_URL . '/public/cart.php');
}

$error = '';
$orderObj = new Order();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'خطای امنیتی. لطفا دوباره تلاش کنید.';
    } else {
        $shippingData = [
            'address' => sanitize($_POST['address'] ?? ''),
            'city' => sanitize($_POST['city'] ?? ''),
            'postal_code' => sanitize($_POST['postal_code'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? '')
        ];
        
        $paymentMethod = sanitize($_POST['payment_method'] ?? 'cod');
        
        $result = $orderObj->create(getCurrentUserId(), $cartItems, $shippingData, $paymentMethod);
        
        if ($result['success']) {
            // Clear cart
            $cart->clear();
            
            setFlashMessage('success', 'سفارش شما با موفقیت ثبت شد');
            redirect(SITE_URL . '/public/order-success.php?order=' . $result['order_number']);
        } else {
            $error = $result['message'];
        }
    }
}

$user = getCurrentUser();

$pageTitle = 'تسویه حساب';
require_once 'header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title text-center mb-20">تسویه حساب</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                <!-- Shipping Information -->
                <div>
                    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: var(--shadow); margin-bottom: 30px;">
                        <h2 style="margin-bottom: 20px; color: var(--dark-color);">
                            <i class="fas fa-map-marker-alt"></i> اطلاعات ارسال
                        </h2>
                        
                        <?php if (!empty($savedAddresses)): ?>
                            <div class="form-group">
                                <label class="form-label">انتخاب از آدرس‌های ذخیره شده</label>
                                <select id="saved-address-select" class="form-control" onchange="loadSavedAddress(this.value)">
                                    <option value="">انتخاب آدرس...</option>
                                    <?php foreach ($savedAddresses as $addr): ?>
                                        <option value="<?php echo $addr['id']; ?>" 
                                                data-phone="<?php echo htmlspecialchars($addr['phone']); ?>"
                                                data-city="<?php echo htmlspecialchars($addr['city']); ?>"
                                                data-address="<?php echo htmlspecialchars($addr['address']); ?>"
                                                data-postal="<?php echo htmlspecialchars($addr['postal_code']); ?>"
                                                <?php echo ($defaultAddress && $addr['id'] == $defaultAddress['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($addr['title']); ?> - <?php echo htmlspecialchars($addr['city']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="margin-bottom: 20px; padding: 10px; background: var(--light-color); border-radius: 8px;">
                                <small>
                                    <i class="fas fa-info-circle"></i> 
                                    می‌توانید از آدرس‌های ذخیره شده استفاده کنید یا آدرس جدید وارد کنید.
                                    <a href="<?php echo SITE_URL; ?>/public/addresses.php" style="color: var(--primary-color);">مدیریت آدرس‌ها</a>
                                </small>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label class="form-label">نام گیرنده <span style="color: red;">*</span></label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">شماره تماس <span style="color: red;">*</span></label>
                            <input type="text" name="phone" id="phone" class="form-control" required 
                                   value="<?php echo htmlspecialchars($defaultAddress['phone'] ?? $user['phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">شهر <span style="color: red;">*</span></label>
                            <input type="text" name="city" id="city" class="form-control" required 
                                   value="<?php echo htmlspecialchars($defaultAddress['city'] ?? $user['city'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">آدرس کامل <span style="color: red;">*</span></label>
                            <textarea name="address" id="address" class="form-control" required rows="3"><?php echo htmlspecialchars($defaultAddress['address'] ?? $user['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">کد پستی <span style="color: red;">*</span></label>
                            <input type="text" name="postal_code" id="postal_code" class="form-control" required 
                                   value="<?php echo htmlspecialchars($defaultAddress['postal_code'] ?? $user['postal_code'] ?? ''); ?>"
                                   pattern="[0-9]{10}" 
                                   placeholder="1234567890">
                            <small style="color: var(--text-light);">کد پستی 10 رقمی بدون خط تیره</small>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: var(--shadow);">
                        <h2 style="margin-bottom: 20px; color: var(--dark-color);">
                            <i class="fas fa-credit-card"></i> روش پرداخت
                        </h2>
                        
                        <div style="display: flex; flex-direction: column; gap: 15px;">
                            <label style="padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 10px;">
                                <input type="radio" name="payment_method" value="cod" checked>
                                <div>
                                    <strong>پرداخت در محل (COD)</strong>
                                    <div style="color: var(--text-light); font-size: 0.9rem;">پرداخت نقدی هنگام دریافت کالا</div>
                                </div>
                            </label>
                            
                            <label style="padding: 15px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 10px; opacity: 0.6;">
                                <input type="radio" name="payment_method" value="online" disabled>
                                <div>
                                    <strong>پرداخت آنلاین</strong>
                                    <div style="color: var(--text-light); font-size: 0.9rem;">به زودی...</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div>
                    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: var(--shadow); position: sticky; top: 100px;">
                        <h2 style="margin-bottom: 20px; color: var(--dark-color);">خلاصه سفارش</h2>
                        
                        <div style="max-height: 300px; overflow-y: auto; margin-bottom: 20px;">
                            <?php foreach ($cartItems as $item): ?>
                                <div style="display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                                    <?php if ($item['image']): ?>
                                        <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo htmlspecialchars($item['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>"
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                    <?php endif; ?>
                                    <div style="flex: 1;">
                                        <div style="font-size: 0.9rem; margin-bottom: 5px;">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </div>
                                        <div style="color: var(--text-light); font-size: 0.85rem;">
                                            <?php echo $item['quantity']; ?> × <?php echo formatPrice($item['final_price']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div style="border-top: 2px solid var(--border-color); padding-top: 20px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                                <span>جمع کل:</span>
                                <strong><?php echo formatPrice($cartTotal); ?></strong>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                                <span>هزینه ارسال:</span>
                                <strong style="color: var(--secondary-color);">رایگان</strong>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; padding-top: 15px; border-top: 2px solid var(--border-color);">
                                <strong style="font-size: 1.1rem;">مبلغ قابل پرداخت:</strong>
                                <strong style="font-size: 1.5rem; color: var(--primary-color);"><?php echo formatPrice($cartTotal); ?></strong>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px; font-size: 1.1rem; padding: 15px;">
                            <i class="fas fa-check"></i> ثبت نهایی سفارش
                        </button>
                        
                        <a href="<?php echo SITE_URL; ?>/public/cart.php" class="btn btn-outline" style="width: 100%; margin-top: 10px; text-align: center;">
                            <i class="fas fa-arrow-right"></i> بازگشت به سبد خرید
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
function loadSavedAddress(addressId) {
    if (!addressId) return;
    
    const select = document.getElementById('saved-address-select');
    const option = select.options[select.selectedIndex];
    
    document.getElementById('phone').value = option.dataset.phone || '';
    document.getElementById('city').value = option.dataset.city || '';
    document.getElementById('address').value = option.dataset.address || '';
    document.getElementById('postal_code').value = option.dataset.postal || '';
}

// Load default address on page load if available
window.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('saved-address-select');
    if (select && select.value) {
        loadSavedAddress(select.value);
    }
});
</script>

<?php require_once 'footer.php'; ?>
