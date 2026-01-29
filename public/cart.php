<?php
$pageTitle = 'سبد خرید';
require_once 'header.php';

// Prevent admin from accessing cart
if (isAdmin()) {
    setFlashMessage('error', 'مدیران نمی‌توانند از بخش خرید استفاده کنند');
    redirect(SITE_URL . '/admin/index.php');
}

$cartItems = $cart->getItems();
$cartTotal = $cart->getTotal();
?>

<section class="section">
    <div class="container">
        <h1 class="section-title text-center mb-20">سبد خرید</h1>
        
        <?php if (!empty($cartItems)): ?>
            <div class="cart-table">
                <table>
                    <thead>
                        <tr>
                            <th>محصول</th>
                            <th>قیمت واحد</th>
                            <th>تعداد</th>
                            <th>جمع</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <?php if ($item['image']): ?>
                                            <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo htmlspecialchars($item['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                 class="cart-item-image">
                                        <?php endif; ?>
                                        <div>
                                            <a href="<?php echo SITE_URL; ?>/public/product-detail.php?id=<?php echo $item['product_id']; ?>">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </a>
                                            <?php if ($item['stock'] < 5): ?>
                                                <div style="color: var(--danger-color); font-size: 0.85rem; margin-top: 5px;">
                                                    تنها <?php echo $item['stock']; ?> عدد در انبار موجود است
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo formatPrice($item['final_price']); ?></td>
                                <td>
                                    <input type="number" 
                                           class="form-control quantity-input update-cart-qty" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           min="1" 
                                           max="<?php echo $item['stock']; ?>"
                                           data-product-id="<?php echo $item['product_id']; ?>">
                                </td>
                                <td><strong class="item-subtotal"><?php echo formatPrice($item['subtotal']); ?></strong></td>
                                <td>
                                    <button class="btn btn-danger remove-from-cart" data-product-id="<?php echo $item['product_id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <div>
                    <a href="<?php echo SITE_URL; ?>/public/products.php" class="btn btn-outline">
                        <i class="fas fa-arrow-right"></i> ادامه خرید
                    </a>
                </div>
                
                <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: var(--shadow);">
                    <h3 style="margin-bottom: 20px; color: var(--dark-color);">خلاصه سبد خرید</h3>
                    
                    <div style="border-top: 1px solid var(--border-color); padding-top: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                            <span>جمع کل:</span>
                            <strong class="cart-total-amount" style="font-size: 1.5rem; color: var(--primary-color);"><?php echo formatPrice($cartTotal); ?></strong>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <a href="<?php echo SITE_URL; ?>/public/checkout.php" class="btn btn-primary" style="width: 100%; text-align: center;">
                                ادامه فرآیند خرید <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px;">
                <i class="fas fa-shopping-cart" style="font-size: 4rem; color: var(--text-light); margin-bottom: 20px;"></i>
                <h3>سبد خرید شما خالی است</h3>
                <p>برای خرید محصولات به صفحه محصولات بروید</p>
                <a href="<?php echo SITE_URL; ?>/public/products.php" class="btn btn-primary" style="margin-top: 20px;">
                    مشاهده محصولات
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'footer.php'; ?>
