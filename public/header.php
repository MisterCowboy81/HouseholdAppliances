<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/Cart.php';
require_once __DIR__ . '/../includes/Category.php';

$cart = new Cart();
$categoryObj = new Category();
$categories = $categoryObj->getAll();
$cartCount = $cart->getCount();
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <meta name="description" content="فروشگاه آنلاین لوازم خانگی با بهترین قیمت و کیفیت">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-top">
            <div class="container">
                <div>
                    <i class="fas fa-phone"></i> 021-12345678
                    <span style="margin: 0 15px;">|</span>
                    <i class="fas fa-envelope"></i> info@example.com
                </div>
                <div>
                    <?php if (isLoggedIn()): ?>
                        <span>خوش آمدید، <?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                        <?php if (isAdmin()): ?>
                            <a href="<?php echo ADMIN_URL; ?>/index.php" style="margin-right: 15px; color: #fbbf24;">
                                <i class="fas fa-user-shield"></i> پنل مدیریت
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/public/login.php">ورود / ثبت نام</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="header-main">
            <div class="container">
                <div style="display: flex; align-items: center; gap: 20px; width: 100%;">
                    <button class="mobile-menu-toggle" id="mobileMenuToggle" type="button" aria-label="منوی موبایل">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <a href="<?php echo SITE_URL; ?>/public/index.php" class="logo">
                        <i class="fas fa-store"></i> <?php echo SITE_NAME; ?>
                    </a>
                    
                    <div class="search-box">
                        <form action="<?php echo SITE_URL; ?>/public/products.php" method="GET" class="search-form">
                            <input type="text" name="q" placeholder="جستجوی محصولات..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                    
                    <div class="header-actions">
                        <?php if (isLoggedIn()): ?>
                            <?php if (!isAdmin()): ?>
                                <a href="<?php echo SITE_URL; ?>/public/profile.php" class="header-link">
                                    <i class="fas fa-user"></i>
                                    <span>حساب کاربری</span>
                                </a>
                                
                                <a href="<?php echo SITE_URL; ?>/public/cart.php" class="header-link">
                                    <i class="fas fa-shopping-cart"></i>
                                    <span>سبد خرید</span>
                                    <span class="cart-badge" style="<?php echo $cartCount > 0 ? '' : 'display: none;'; ?>"><?php echo $cartCount; ?></span>
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?php echo SITE_URL; ?>/public/cart.php" class="header-link">
                                <i class="fas fa-shopping-cart"></i>
                                <span>سبد خرید</span>
                                <span class="cart-badge" style="<?php echo $cartCount > 0 ? '' : 'display: none;'; ?>"><?php echo $cartCount; ?></span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <nav id="mainNav">
            <div class="container">
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>/public/index.php"><i class="fas fa-home"></i> صفحه اصلی</a></li>
                    <li class="dropdown">
                        <a href="<?php echo SITE_URL; ?>/public/products.php" class="dropdown-toggle"><i class="fas fa-boxes"></i> محصولات <i class="fas fa-chevron-down"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo SITE_URL; ?>/public/products.php">همه محصولات</a></li>
                            <?php foreach ($categories as $category): ?>
                                <li><a href="<?php echo SITE_URL; ?>/public/products.php?category=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li><a href="<?php echo SITE_URL; ?>/public/contact.php"><i class="fas fa-envelope"></i> تماس با ما</a></li>
                </ul>
            </div>
        </nav>
    </header>
    
    <!-- Flash Messages -->
    <?php
    $flash = getFlashMessage();
    if ($flash):
    ?>
        <div class="container" style="margin-top: 20px;">
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main>
