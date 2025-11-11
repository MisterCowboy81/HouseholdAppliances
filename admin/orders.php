<?php
$pageTitle = 'مدیریت سفارش‌ها';
require_once 'header.php';
require_once __DIR__ . '/../includes/Order.php';

$orderObj = new Order();

// Handle status update
if (isset($_POST['update_status']) && isset($_POST['order_id'])) {
    $result = $orderObj->updateStatus(intval($_POST['order_id']), sanitize($_POST['status']));
    if ($result['success']) {
        setFlashMessage('success', $result['message']);
    } else {
        setFlashMessage('error', $result['message']);
    }
    // Preserve filter when redirecting
    $redirectUrl = ADMIN_URL . '/orders.php';
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $redirectUrl .= '?status=' . urlencode($_GET['status']);
    }
    redirect($redirectUrl);
}

// Get filters
$filters = [];
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $filters['status'] = sanitize($_GET['status']);
}
if (isset($_GET['payment_status']) && !empty($_GET['payment_status'])) {
    $filters['payment_status'] = sanitize($_GET['payment_status']);
}

$orders = $orderObj->getAllOrders($filters, 100, 0);
?>

<div class="page-header">
    <h1 class="page-title">مدیریت سفارش‌ها</h1>
</div>

<!-- Filter -->
<div class="admin-card" style="margin-bottom: 20px;">
    <form method="GET" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
        <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
            <label class="form-label">فیلتر بر اساس وضعیت</label>
            <select name="status" class="form-control">
                <option value="">همه</option>
                <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>در انتظار</option>
                <option value="processing" <?php echo (isset($_GET['status']) && $_GET['status'] == 'processing') ? 'selected' : ''; ?>>در حال پردازش</option>
                <option value="shipped" <?php echo (isset($_GET['status']) && $_GET['status'] == 'shipped') ? 'selected' : ''; ?>>ارسال شده</option>
                <option value="delivered" <?php echo (isset($_GET['status']) && $_GET['status'] == 'delivered') ? 'selected' : ''; ?>>تحویل داده شده</option>
                <option value="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] == 'cancelled') ? 'selected' : ''; ?>>لغو شده</option>
            </select>
        </div>
        <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
            <label class="form-label">وضعیت پرداخت</label>
            <select name="payment_status" class="form-control">
                <option value="">همه</option>
                <option value="pending" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] == 'pending') ? 'selected' : ''; ?>>در انتظار</option>
                <option value="paid" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] == 'paid') ? 'selected' : ''; ?>>پرداخت شده</option>
                <option value="failed" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] == 'failed') ? 'selected' : ''; ?>>ناموفق</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">اعمال فیلتر</button>
        <a href="<?php echo ADMIN_URL; ?>/orders.php" class="btn btn-outline">حذف فیلتر</a>
    </form>
</div>

<div class="admin-card">
    <?php if (!empty($orders)): ?>
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>شماره سفارش</th>
                        <th>مشتری</th>
                        <th>تلفن</th>
                        <th>مبلغ</th>
                        <th>وضعیت</th>
                        <th>تاریخ</th>
                        <th style="width: 200px;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($order['full_name'] ?? $order['username']); ?></td>
                            <td><?php echo htmlspecialchars($order['phone']); ?></td>
                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" class="form-control" style="padding: 5px 10px; font-size: 0.85rem;" 
                                            onchange="if(confirm('آیا مطمئن هستید؟')) this.form.submit();">
                                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>در انتظار</option>
                                        <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>در حال پردازش</option>
                                        <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>ارسال شده</option>
                                        <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>تحویل داده شده</option>
                                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>لغو شده</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td><?php echo timeAgo($order['created_at']); ?></td>
                            <td>
                                <a href="<?php echo ADMIN_URL; ?>/order-detail.php?id=<?php echo $order['id']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> جزئیات
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 60px 20px;">
            <i class="fas fa-shopping-bag" style="font-size: 4rem; color: var(--text-light); margin-bottom: 20px;"></i>
            <h3>سفارشی وجود ندارد</h3>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
