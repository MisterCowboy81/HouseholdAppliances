<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/Order.php';

requireLogin();

$orderNumber = sanitize($_GET['order'] ?? '');

if (empty($orderNumber)) {
    redirect(SITE_URL . '/public/index.php');
}

$orderObj = new Order();
$order = $orderObj->getByOrderNumber($orderNumber);

if (!$order || $order['user_id'] != getCurrentUserId()) {
    redirect(SITE_URL . '/public/index.php');
}

$pageTitle = 'سفارش ثبت شد';
require_once 'header.php';
?>

<section class="section">
    <div class="container">
        <div style="max-width: 700px; margin: 0 auto; text-align: center;">
            <!-- Success Icon -->
            <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
                        width: 120px; height: 120px; border-radius: 50%; 
                        display: flex; align-items: center; justify-content: center; 
                        margin: 0 auto 30px; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);">
                <i class="fas fa-check" style="font-size: 60px; color: white;"></i>
            </div>
            
            <h1 style="color: var(--dark-color); margin-bottom: 15px; font-size: 2rem;">
                سفارش شما با موفقیت ثبت شد!
            </h1>
            
            <p style="color: var(--text-light); font-size: 1.1rem; margin-bottom: 30px;">
                از خرید شما متشکریم. سفارش شما در حال پردازش است.
            </p>
            
            <!-- Order Details Box -->
            <div style="background: white; padding: 30px; border-radius: 12px; 
                        box-shadow: var(--shadow-lg); margin-bottom: 30px; text-align: right;">
                <h2 style="margin-bottom: 20px; color: var(--dark-color); border-bottom: 2px solid var(--border-color); padding-bottom: 15px;">
                    <i class="fas fa-receipt"></i> جزئیات سفارش
                </h2>
                
                <div style="display: grid; gap: 15px;">
                    <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                        <span style="color: var(--text-light);">شماره سفارش:</span>
                        <strong style="color: var(--primary-color); font-size: 1.1rem;"><?php echo htmlspecialchars($order['order_number']); ?></strong>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                        <span style="color: var(--text-light);">تاریخ ثبت:</span>
                        <strong><?php echo formatDate($order['created_at']); ?></strong>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                        <span style="color: var(--text-light);">مبلغ کل:</span>
                        <strong style="color: var(--secondary-color); font-size: 1.2rem;"><?php echo formatPrice($order['total_amount']); ?></strong>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                        <span style="color: var(--text-light);">روش پرداخت:</span>
                        <strong>
                            <?php 
                            $paymentMethods = [
                                'cod' => 'پرداخت در محل',
                                'online' => 'پرداخت آنلاین',
                                'card' => 'کارت به کارت'
                            ];
                            echo $paymentMethods[$order['payment_method']] ?? 'نامشخص';
                            ?>
                        </strong>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                        <span style="color: var(--text-light);">وضعیت سفارش:</span>
                        <strong style="color: var(--warning-color);">
                            <?php 
                            $statusLabels = [
                                'pending' => 'در انتظار پردازش',
                                'processing' => 'در حال پردازش',
                                'shipped' => 'ارسال شده',
                                'delivered' => 'تحویل داده شده',
                                'cancelled' => 'لغو شده'
                            ];
                            echo $statusLabels[$order['status']] ?? 'نامشخص';
                            ?>
                        </strong>
                    </div>
                </div>
                
                <!-- Shipping Address -->
                <div style="margin-top: 25px; padding: 20px; background: #fef3c7; border-radius: 8px; border-right: 4px solid var(--warning-color);">
                    <h3 style="margin-bottom: 12px; color: var(--dark-color); font-size: 1rem;">
                        <i class="fas fa-map-marker-alt"></i> آدرس ارسال
                    </h3>
                    <p style="margin: 0; line-height: 1.8; color: var(--text-color);">
                        <strong>شهر:</strong> <?php echo htmlspecialchars($order['shipping_city']); ?><br>
                        <strong>آدرس:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?><br>
                        <strong>کد پستی:</strong> <?php echo htmlspecialchars($order['shipping_postal_code']); ?><br>
                        <strong>شماره تماس:</strong> <?php echo htmlspecialchars($order['phone']); ?>
                    </p>
                </div>
            </div>
            
            <!-- What's Next Section -->
            <div style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); 
                        padding: 25px; border-radius: 12px; margin-bottom: 30px; text-align: right;">
                <h3 style="margin-bottom: 15px; color: var(--primary-color);">
                    <i class="fas fa-info-circle"></i> مراحل بعدی
                </h3>
                <ul style="list-style: none; padding: 0; margin: 0; text-align: right;">
                    <li style="padding: 10px 0; border-bottom: 1px solid rgba(37, 99, 235, 0.2);">
                        <i class="fas fa-check-circle" style="color: var(--secondary-color); margin-left: 10px;"></i>
                        سفارش شما در صف پردازش قرار گرفت
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid rgba(37, 99, 235, 0.2);">
                        <i class="fas fa-box" style="color: var(--warning-color); margin-left: 10px;"></i>
                        بسته‌بندی و آماده‌سازی سفارش (1-2 روز کاری)
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid rgba(37, 99, 235, 0.2);">
                        <i class="fas fa-truck" style="color: var(--primary-color); margin-left: 10px;"></i>
                        ارسال به آدرس شما (2-5 روز کاری)
                    </li>
                    <li style="padding: 10px 0;">
                        <i class="fas fa-home" style="color: var(--secondary-color); margin-left: 10px;"></i>
                        تحویل در محل و دریافت مبلغ
                    </li>
                </ul>
            </div>
            
            <!-- Action Buttons -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 30px;">
                <a href="<?php echo SITE_URL; ?>/public/profile.php" class="btn btn-primary" style="text-align: center; padding: 15px;">
                    <i class="fas fa-history"></i> مشاهده سفارش‌های من
                </a>
                <a href="<?php echo SITE_URL; ?>/public/products.php" class="btn btn-outline" style="text-align: center; padding: 15px;">
                    <i class="fas fa-shopping-bag"></i> ادامه خرید
                </a>
            </div>
            
            <!-- Contact Support -->
            <div style="margin-top: 30px; padding: 20px; background: white; border-radius: 8px; box-shadow: var(--shadow);">
                <p style="color: var(--text-light); margin: 0;">
                    <i class="fas fa-headset"></i> در صورت هرگونه سوال با پشتیبانی تماس بگیرید:
                    <strong style="color: var(--primary-color); margin-right: 10px;">021-12345678</strong>
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>
