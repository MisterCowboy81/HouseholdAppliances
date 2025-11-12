<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();
requireAdmin();

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>پنل مدیریت</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: #f3f4f6;">
    
    <!-- Admin Header -->
    <header style="background: linear-gradient(135deg, #1e3a8a, #3b82f6); box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="padding: 15px 30px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 30px;">
                <h1 style="color: white; font-size: 1.5rem; margin: 0;">
                    <i class="fas fa-user-shield"></i> پنل مدیریت
                </h1>
                <a href="<?php echo SITE_URL; ?>/public/index.php" target="_blank" style="color: white; opacity: 0.9;">
                    <i class="fas fa-external-link-alt"></i> مشاهده سایت
                </a>
            </div>
            <div style="color: white;">
                <span><?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                <a href="<?php echo SITE_URL; ?>/public/logout.php" style="color: white; margin-right: 20px;">
                    <i class="fas fa-sign-out-alt"></i> خروج
                </a>
            </div>
        </div>
    </header>
    
    <!-- Admin Layout -->
    <div style="display: flex; min-height: calc(100vh - 60px);">
        <!-- Sidebar -->
        <aside style="width: 260px; background: white; box-shadow: 2px 0 4px rgba(0,0,0,0.05);">
            <nav style="padding: 20px 0;">
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li>
                        <a href="<?php echo ADMIN_URL; ?>/index.php" style="display: flex; align-items: center; padding: 15px 25px; color: var(--text-color); transition: all 0.3s;">
                            <i class="fas fa-chart-line" style="width: 25px;"></i>
                            <span>داشبورد</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo ADMIN_URL; ?>/products.php" style="display: flex; align-items: center; padding: 15px 25px; color: var(--text-color); transition: all 0.3s;">
                            <i class="fas fa-box" style="width: 25px;"></i>
                            <span>محصولات</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo ADMIN_URL; ?>/categories.php" style="display: flex; align-items: center; padding: 15px 25px; color: var(--text-color); transition: all 0.3s;">
                            <i class="fas fa-tags" style="width: 25px;"></i>
                            <span>دسته‌بندی‌ها</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo ADMIN_URL; ?>/orders.php" style="display: flex; align-items: center; padding: 15px 25px; color: var(--text-color); transition: all 0.3s;">
                            <i class="fas fa-shopping-bag" style="width: 25px;"></i>
                            <span>سفارش‌ها</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo ADMIN_URL; ?>/users.php" style="display: flex; align-items: center; padding: 15px 25px; color: var(--text-color); transition: all 0.3s;">
                            <i class="fas fa-users" style="width: 25px;"></i>
                            <span>کاربران</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main style="flex: 1; padding: 30px;">
            <?php
            $flash = getFlashMessage();
            if ($flash):
            ?>
                <div class="alert alert-<?php echo $flash['type']; ?>" style="margin-bottom: 20px;">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>
