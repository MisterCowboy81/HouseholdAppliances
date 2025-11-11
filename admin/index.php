<?php
$pageTitle = 'داشبورد';
require_once 'header.php';
require_once __DIR__ . '/../includes/Product.php';
require_once __DIR__ . '/../includes/Order.php';
require_once __DIR__ . '/../includes/User.php';

$productObj = new Product();
$orderObj = new Order();
$userObj = new User();

// Get statistics
$stats = $orderObj->getStatistics();

// Get total products
$totalProducts = $productObj->getTotalCount();

// Get total users
$db = getDB();
$result = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
$totalCustomers = $result->fetch_assoc()['total'];

// Get recent orders
$recentOrders = $orderObj->getAllOrders([], 10, 0);
?>

<div class="page-header">
    <h1 class="page-title">داشبورد</h1>
    <div style="color: var(--text-light);">
        <i class="fas fa-calendar"></i> <?php echo date('Y/m/d'); ?>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo number_format($stats['total_orders']); ?></h3>
            <p>کل سفارش‌ها</p>
        </div>
        <div class="stat-icon primary">
            <i class="fas fa-shopping-bag"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo number_format($stats['pending_orders']); ?></h3>
            <p>سفارش‌های در انتظار</p>
        </div>
        <div class="stat-icon warning">
            <i class="fas fa-clock"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo formatPrice($stats['total_revenue']); ?></h3>
            <p>درآمد کل</p>
        </div>
        <div class="stat-icon success">
            <i class="fas fa-money-bill-wave"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo number_format($totalProducts); ?></h3>
            <p>محصولات</p>
        </div>
        <div class="stat-icon primary">
            <i class="fas fa-box"></i>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
    <!-- Recent Orders -->
    <div class="admin-card">
        <h2><i class="fas fa-shopping-bag"></i> آخرین سفارش‌ها</h2>
        
        <?php if (!empty($recentOrders)): ?>
            <div class="admin-table">
                <table>
                    <thead>
                        <tr>
                            <th>شماره سفارش</th>
                            <th>مشتری</th>
                            <th>مبلغ</th>
                            <th>وضعیت</th>
                            <th>تاریخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($recentOrders, 0, 5) as $order): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo ADMIN_URL; ?>/order-detail.php?id=<?php echo $order['id']; ?>" 
                                       style="color: var(--primary-color); font-weight: bold;">
                                        <?php echo htmlspecialchars($order['order_number']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($order['full_name'] ?? $order['username']); ?></td>
                                <td><?php echo formatPrice($order['total_amount']); ?></td>
                                <td>
                                    <?php
                                    $statusMap = [
                                        'pending' => 'در انتظار',
                                        'processing' => 'در حال پردازش',
                                        'shipped' => 'ارسال شده',
                                        'delivered' => 'تحویل داده شده',
                                        'cancelled' => 'لغو شده'
                                    ];
                                    ?>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo $statusMap[$order['status']]; ?>
                                    </span>
                                </td>
                                <td><?php echo timeAgo($order['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="<?php echo ADMIN_URL; ?>/orders.php" class="btn btn-outline">مشاهده همه سفارش‌ها</a>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: var(--text-light); padding: 40px;">سفارشی وجود ندارد</p>
        <?php endif; ?>
    </div>
    
    <!-- Quick Stats -->
    <div>
        <div class="admin-card">
            <h2><i class="fas fa-chart-pie"></i> آمار امروز</h2>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="padding: 15px; background: var(--light-color); border-radius: 8px;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary-color);">
                        <?php echo number_format($stats['today_orders']); ?>
                    </div>
                    <div style="color: var(--text-light); font-size: 0.9rem;">سفارش امروز</div>
                </div>
                
                <div style="padding: 15px; background: var(--light-color); border-radius: 8px;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: var(--secondary-color);">
                        <?php echo number_format($totalCustomers); ?>
                    </div>
                    <div style="color: var(--text-light); font-size: 0.9rem;">مشتریان</div>
                </div>
            </div>
        </div>
        
        <div class="admin-card">
            <h2><i class="fas fa-link"></i> لینک‌های سریع</h2>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <a href="<?php echo ADMIN_URL; ?>/products.php?action=add" class="btn btn-primary" style="text-align: center;">
                    <i class="fas fa-plus"></i> افزودن محصول جدید
                </a>
                <a href="<?php echo ADMIN_URL; ?>/orders.php?status=pending" class="btn btn-outline" style="text-align: center;">
                    <i class="fas fa-clock"></i> سفارش‌های در انتظار
                </a>
                <a href="<?php echo ADMIN_URL; ?>/categories.php" class="btn btn-outline" style="text-align: center;">
                    <i class="fas fa-tags"></i> مدیریت دسته‌بندی‌ها
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
