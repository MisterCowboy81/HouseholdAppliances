<?php
$pageTitle = 'صفحه اصلی';
require_once 'header.php';
require_once __DIR__ . '/../includes/Product.php';

$productObj = new Product();
$featuredProducts = $productObj->getFeaturedProducts(6);
$latestProducts = $productObj->getLatestProducts(8);
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>به فروشگاه لوازم خانگی خوش آمدید</h1>
        <p>خرید آنلاین لوازم خانگی با بهترین قیمت و کیفیت</p>
        <a href="<?php echo SITE_URL; ?>/public/products.php" class="btn btn-primary">مشاهده محصولات</a>
    </div>
</section>

<!-- Featured Products -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">محصولات ویژه</h2>
            <p class="section-subtitle">بهترین محصولات ما با قیمت ویژه</p>
        </div>
        
        <div class="grid grid-3">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($product['image']): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <img src="<?php echo SITE_URL; ?>/assets/images/no-image.png" alt="بدون تصویر">
                        <?php endif; ?>
                        
                        <?php if ($product['discount_price']): ?>
                            <div class="product-badge">
                                <?php echo calculateDiscountPercentage($product['price'], $product['discount_price']); ?>% تخفیف
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-content">
                        <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                        <h3 class="product-title">
                            <a href="<?php echo SITE_URL; ?>/public/product-detail.php?id=<?php echo $product['id']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </h3>
                        
                        <div class="product-price">
                            <?php if ($product['discount_price']): ?>
                                <span class="price-current"><?php echo formatPrice($product['discount_price']); ?></span>
                                <span class="price-original"><?php echo formatPrice($product['price']); ?></span>
                            <?php else: ?>
                                <span class="price-current"><?php echo formatPrice($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-actions">
                            <button class="btn btn-primary btn-cart add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-shopping-cart"></i> افزودن به سبد
                            </button>
                            <a href="<?php echo SITE_URL; ?>/public/product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($featuredProducts)): ?>
            <p class="text-center">محصولی یافت نشد</p>
        <?php endif; ?>
    </div>
</section>

<!-- Categories -->
<section class="section" style="background: white;">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">دسته‌بندی محصولات</h2>
            <p class="section-subtitle">انتخاب بر اساس دسته‌بندی</p>
        </div>
        
        <div class="grid grid-4">
            <?php foreach ($categories as $category): ?>
                <a href="<?php echo SITE_URL; ?>/public/products.php?category=<?php echo $category['id']; ?>" class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-blender"></i>
                    </div>
                    <h3 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h3>
                    <?php if ($category['description']): ?>
                        <p class="category-count"><?php echo htmlspecialchars(substr($category['description'], 0, 50)); ?>...</p>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Latest Products -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">جدیدترین محصولات</h2>
            <p class="section-subtitle">آخرین محصولات اضافه شده</p>
        </div>
        
        <div class="grid grid-4">
            <?php foreach ($latestProducts as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($product['image']): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <img src="<?php echo SITE_URL; ?>/assets/images/no-image.png" alt="بدون تصویر">
                        <?php endif; ?>
                        
                        <?php if ($product['discount_price']): ?>
                            <div class="product-badge">
                                <?php echo calculateDiscountPercentage($product['price'], $product['discount_price']); ?>% تخفیف
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-content">
                        <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                        <h3 class="product-title">
                            <a href="<?php echo SITE_URL; ?>/public/product-detail.php?id=<?php echo $product['id']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </h3>
                        
                        <div class="product-price">
                            <?php if ($product['discount_price']): ?>
                                <span class="price-current"><?php echo formatPrice($product['discount_price']); ?></span>
                                <span class="price-original"><?php echo formatPrice($product['price']); ?></span>
                            <?php else: ?>
                                <span class="price-current"><?php echo formatPrice($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-actions">
                            <button class="btn btn-primary btn-cart add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-shopping-cart"></i> افزودن به سبد
                            </button>
                            <a href="<?php echo SITE_URL; ?>/public/product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features -->
<section class="section" style="background: white;">
    <div class="container">
        <div class="grid grid-3">
            <div class="category-card">
                <div class="category-icon"><i class="fas fa-shipping-fast"></i></div>
                <h3 class="category-name">ارسال سریع</h3>
                <p>ارسال رایگان برای خریدهای بالای 5 میلیون تومان</p>
            </div>
            
            <div class="category-card">
                <div class="category-icon"><i class="fas fa-shield-alt"></i></div>
                <h3 class="category-name">ضمانت اصالت</h3>
                <p>تضمین اصالت و کیفیت تمامی محصولات</p>
            </div>
            
            <div class="category-card">
                <div class="category-icon"><i class="fas fa-headset"></i></div>
                <h3 class="category-name">پشتیبانی 24/7</h3>
                <p>پاسخگویی به سوالات شما در تمام ساعات شبانه روز</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>
