<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/Order.php';

requireLogin();
requireAdmin();

$orderObj = new Order();
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($orderId <= 0) {
    redirect(ADMIN_URL . '/orders.php');
}

$order = $orderObj->getById($orderId);
if (!$order) {
    redirect(ADMIN_URL . '/orders.php');
}

// Handle payment status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment'])) {
    $result = $orderObj->updatePaymentStatus($orderId, sanitize($_POST['payment_status']));
    if ($result['success']) {
        setFlashMessage('success', $result['message']);
        redirect(ADMIN_URL . '/order-detail.php?id=' . $orderId);
    }
}

$orderItems = $orderObj->getItems($orderId);

$pageTitle = 'جزئیات سفارش';
require_once 'header.php';
?>

<div class="page-header">
    <h1 class="page-title">جزئیات سفارش: <?php echo htmlspecialchars($order['order_number']); ?></h1>
    <a href="<?php echo ADMIN_URL; ?>/orders.php" class="btn btn-outline">
        <i class="fas fa-arrow-right"></i> بازگشت
    </a>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
    <!-- Order Items -->
    <div>
        <div class="admin-card">
            <h2>اقلام سفارش</h2>
            <div class="admin-table">
                <table>
                    <thead>
                        <tr>
                            <th>محصول</th>
                            <th>قیمت واحد</th>
                            <th>تعداد</th>
                            <th>جمع</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo formatPrice($item['price']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo formatPrice($item['subtotal']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" style="text-align: left;">جمع کل:</th>
                            <th><?php echo formatPrice($order['total_amount']); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <!-- Shipping Address -->
        <div class="admin-card" style="margin-top: 20px;">
            <h2>اطلاعات ارسال</h2>
            <div style="line-height: 1.8;">
                <p><strong>شهر:</strong> <?php echo htmlspecialchars($order['shipping_city']); ?></p>
                <p><strong>آدرس:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                <p><strong>کد پستی:</strong> <?php echo htmlspecialchars($order['shipping_postal_code']); ?></p>
                <p><strong>شماره تماس:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Order Info -->
    <div>
        <div class="admin-card">
            <h2>اطلاعات سفارش</h2>
            <div style="margin-bottom: 20px;">
                <p style="margin: 10px 0;"><strong>تاریخ ثبت:</strong><br><?php echo formatDate($order['created_at']); ?></p>
                <p style="margin: 10px 0;"><strong>روش پرداخت:</strong><br>
                    <?php 
                    $paymentMethods = [
                        'cod' => 'پرداخت در محل',
                        'online' => 'پرداخت آنلاین',
                        'card' => 'کارت به کارت'
                    ];
                    echo $paymentMethods[$order['payment_method']] ?? 'نامشخص';
                    ?>
                </p>
            </div>
            
            <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--border-color);">
            
            <!-- Payment Status Update -->
            <div>
                <h3 style="margin-bottom: 15px; font-size: 1.1rem;">وضعیت پرداخت</h3>
                <form method="POST">
                    <div class="form-group">
                        <select name="payment_status" class="form-control">
                            <option value="pending" <?php echo $order['payment_status'] == 'pending' ? 'selected' : ''; ?>>در انتظار پرداخت</option>
                            <option value="paid" <?php echo $order['payment_status'] == 'paid' ? 'selected' : ''; ?>>پرداخت شده</option>
                            <option value="failed" <?php echo $order['payment_status'] == 'failed' ? 'selected' : ''; ?>>پرداخت ناموفق</option>
                        </select>
                    </div>
                    <button type="submit" name="update_payment" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-check"></i> بروزرسانی وضعیت پرداخت
                    </button>
                </form>
                
                <?php if ($order['payment_method'] == 'cod'): ?>
                    <div style="margin-top: 15px; padding: 10px; background: #fef3c7; border-radius: 8px; font-size: 0.9rem;">
                        <i class="fas fa-info-circle"></i> 
                        پس از تحویل و دریافت وجه، وضعیت پرداخت را به "پرداخت شده" تغییر دهید
                    </div>
                <?php endif; ?>
            </div>
            
            <hr style="margin: 20px 0; border: none; border-top: 1px solid var(--border-color);">
            
            <!-- Order Status -->
            <div>
                <h3 style="margin-bottom: 10px; font-size: 1.1rem;">وضعیت سفارش</h3>
                <div style="padding: 15px; background: var(--light-color); border-radius: 8px; text-align: center;">
                    <?php
                    $statusMap = [
                        'pending' => ['در انتظار', 'badge-warning'],
                        'processing' => ['در حال پردازش', 'badge-info'],
                        'shipped' => ['ارسال شده', 'badge-info'],
                        'delivered' => ['تحویل داده شده', 'badge-success'],
                        'cancelled' => ['لغو شده', 'badge-danger']
                    ];
                    $statusInfo = $statusMap[$order['status']] ?? ['نامشخص', 'badge-secondary'];
                    ?>
                    <span class="badge <?php echo $statusInfo[1]; ?>" style="font-size: 1rem; padding: 8px 16px;">
                        <?php echo $statusInfo[0]; ?>
                    </span>
                </div>
                <p style="margin-top: 10px; text-align: center; font-size: 0.9rem; color: var(--text-light);">
                    برای تغییر، از لیست سفارش‌ها استفاده کنید
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
