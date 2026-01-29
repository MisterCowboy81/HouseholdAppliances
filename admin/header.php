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
    <header class="admin-header" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); box-shadow: 0 4px 20px rgba(0,0,0,0.15); position: sticky; top: 0; z-index: 1000;">
        <div style="padding: 18px 35px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 20px;">
                <button class="admin-menu-toggle" id="adminMenuToggle" style="display: none; background: rgba(255,255,255,0.2); border: none; color: white; padding: 10px 12px; border-radius: 8px; cursor: pointer; font-size: 1.2rem; transition: all 0.3s ease;">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 style="color: white; font-size: 1.6rem; margin: 0; font-weight: 800; display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-user-shield" style="font-size: 1.8rem;"></i> 
                    <span>پنل مدیریت</span>
                </h1>
                <a href="<?php echo SITE_URL; ?>/public/index.php" target="_blank" style="color: white; opacity: 0.95; padding: 8px 16px; border-radius: 8px; background: rgba(255,255,255,0.1); transition: all 0.3s ease; text-decoration: none; font-weight: 500;">
                    <i class="fas fa-external-link-alt"></i> مشاهده سایت
                </a>
            </div>
            <div style="color: white; display: flex; align-items: center; gap: 20px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user"></i>
                    </div>
                    <span style="font-weight: 500;"><?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                </div>
                <a href="<?php echo SITE_URL; ?>/public/logout.php" style="color: white; margin-right: 0; padding: 8px 16px; border-radius: 8px; background: rgba(255,255,255,0.1); transition: all 0.3s ease; text-decoration: none; font-weight: 500;">
                    <i class="fas fa-sign-out-alt"></i> خروج
                </a>
            </div>
        </div>
    </header>
    
    <!-- Admin Layout -->
    <div class="admin-layout" style="display: flex; min-height: calc(100vh - 60px);">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <nav class="admin-sidebar-nav">
                <ul class="admin-menu-list">
                    <li>
                        <a href="<?php echo ADMIN_URL; ?>/index.php" class="admin-nav-link">
                            <i class="fas fa-chart-line"></i>
                            <span>داشبورد</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo ADMIN_URL; ?>/products.php" class="admin-nav-link">
                            <i class="fas fa-box"></i>
                            <span>محصولات</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo ADMIN_URL; ?>/categories.php" class="admin-nav-link">
                            <i class="fas fa-tags"></i>
                            <span>دسته‌بندی‌ها</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo ADMIN_URL; ?>/orders.php" class="admin-nav-link">
                            <i class="fas fa-shopping-bag"></i>
                            <span>سفارش‌ها</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo ADMIN_URL; ?>/users.php" class="admin-nav-link">
                            <i class="fas fa-users"></i>
                            <span>کاربران</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main-content" style="flex: 1; padding: 30px; min-width: 0;">
            <?php
            $flash = getFlashMessage();
            if ($flash):
            ?>
                <div class="alert alert-<?php echo $flash['type']; ?>" style="margin-bottom: 20px;">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>
