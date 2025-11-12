<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/Product.php';

$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productId <= 0) {
    redirect(SITE_URL . '/public/products.php');
}

$productObj = new Product();
$product = $productObj->getProductById($productId);

if (!$product) {
    redirect(SITE_URL . '/public/products.php');
}

$pageTitle = $product['name'];
require_once 'header.php';

// Calculate final price
$finalPrice = $product['discount_price'] ?? $product['price'];
$hasDiscount = !empty($product['discount_price']);
?>

<section class="section">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; background: white; padding: 40px; border-radius: 12px; box-shadow: var(--shadow);">
            <!-- Product Image -->
            <div>
                <div style="border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; margin-bottom: 20px;">
                    <?php if ($product['image']): ?>
                        <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             style="width: 100%; height: auto;">
                    <?php else: ?>
                        <img src="<?php echo SITE_URL; ?>/assets/images/no-image.png" alt="بدون تصویر" style="width: 100%; height: auto;">
                    <?php endif; ?>
                </div>
                
                <?php if ($hasDiscount): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-tag"></i> 
                        <?php echo calculateDiscountPercentage($product['price'], $product['discount_price']); ?>% تخفیف ویژه!
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Product Details -->
            <div>
                <div style="margin-bottom: 10px;">
                    <a href="<?php echo SITE_URL; ?>/public/products.php?category=<?php echo $product['category_id']; ?>" 
                       style="color: var(--primary-color); font-size: 0.9rem;">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </a>
                </div>
                
                <h1 style="font-size: 2rem; margin-bottom: 20px; color: var(--dark-color);">
                    <?php echo htmlspecialchars($product['name']); ?>
                </h1>
                
                <!-- Price -->
                <div style="margin-bottom: 30px; padding: 20px; background: var(--light-color); border-radius: 12px;">
                    <?php if ($hasDiscount): ?>
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                            <span style="font-size: 2rem; font-weight: bold; color: var(--primary-color);">
                                <?php echo formatPrice($finalPrice); ?>
                            </span>
                            <span style="font-size: 1.2rem; color: var(--text-light); text-decoration: line-through;">
                                <?php echo formatPrice($product['price']); ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <div style="font-size: 2rem; font-weight: bold; color: var(--primary-color);">
                            <?php echo formatPrice($finalPrice); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Product Info -->
                <div style="margin-bottom: 30px;">
                    <?php if ($product['brand']): ?>
                        <div style="display: flex; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                            <strong style="width: 120px;">برند:</strong>
                            <span><?php echo htmlspecialchars($product['brand']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($product['model']): ?>
                        <div style="display: flex; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                            <strong style="width: 120px;">مدل:</strong>
                            <span><?php echo htmlspecialchars($product['model']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($product['warranty']): ?>
                        <div style="display: flex; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                            <strong style="width: 120px;">گارانتی:</strong>
                            <span><?php echo htmlspecialchars($product['warranty']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div style="display: flex; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                        <strong style="width: 120px;">موجودی:</strong>
                        <?php if ($product['stock'] > 0): ?>
                            <span style="color: var(--secondary-color);">
                                <i class="fas fa-check-circle"></i> موجود (<?php echo $product['stock']; ?> عدد)
                            </span>
                        <?php else: ?>
                            <span style="color: var(--danger-color);">
                                <i class="fas fa-times-circle"></i> ناموجود
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Add to Cart -->
                <?php if ($product['stock'] > 0): ?>
                    <div style="display: flex; gap: 15px; margin-bottom: 30px;">
                        <div style="flex: 0 0 120px;">
                            <label class="form-label">تعداد:</label>
                            <input type="number" class="form-control qty-input" value="1" min="1" max="<?php echo $product['stock']; ?>" id="product-quantity">
                        </div>
                        <div style="flex: 1;">
                            <label class="form-label" style="visibility: hidden;">Action</label>
                            <button class="btn btn-primary add-to-cart" 
                                    data-product-id="<?php echo $product['id']; ?>" 
                                    style="width: 100%; font-size: 1.1rem; padding: 14px;"
                                    onclick="this.setAttribute('data-quantity', document.getElementById('product-quantity').value)">
                                <i class="fas fa-shopping-cart"></i> افزودن به سبد خرید
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> این محصول در حال حاضر موجود نیست
                    </div>
                <?php endif; ?>
                
                <!-- Features -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div style="display: flex; align-items: center; gap: 10px; padding: 15px; background: var(--light-color); border-radius: 8px;">
                        <i class="fas fa-truck" style="font-size: 1.5rem; color: var(--primary-color);"></i>
                        <span style="font-size: 0.9rem;">ارسال سریع</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; padding: 15px; background: var(--light-color); border-radius: 8px;">
                        <i class="fas fa-shield-alt" style="font-size: 1.5rem; color: var(--primary-color);"></i>
                        <span style="font-size: 0.9rem;">ضمانت اصالت</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; padding: 15px; background: var(--light-color); border-radius: 8px;">
                        <i class="fas fa-undo" style="font-size: 1.5rem; color: var(--primary-color);"></i>
                        <span style="font-size: 0.9rem;">۷ روز ضمانت بازگشت</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; padding: 15px; background: var(--light-color); border-radius: 8px;">
                        <i class="fas fa-headset" style="font-size: 1.5rem; color: var(--primary-color);"></i>
                        <span style="font-size: 0.9rem;">پشتیبانی ۲۴/۷</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Description -->
        <?php if ($product['description']): ?>
            <div style="margin-top: 40px; background: white; padding: 40px; border-radius: 12px; box-shadow: var(--shadow);">
                <h2 style="margin-bottom: 20px; color: var(--dark-color);">توضیحات محصول</h2>
                <div style="line-height: 1.8; color: var(--text-color);">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'footer.php'; ?>
