<?php
$pageTitle = 'مدیریت محصولات';
require_once 'header.php';
require_once __DIR__ . '/../includes/Product.php';
require_once __DIR__ . '/../includes/Category.php';

$productObj = new Product();
$categoryObj = new Category();

// Handle delete
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $result = $productObj->deleteProduct(intval($_GET['id']));
    if ($result['success']) {
        setFlashMessage('success', $result['message']);
    } else {
        setFlashMessage('error', $result['message']);
    }
    redirect(ADMIN_URL . '/products.php');
}

// Get all products
$products = $productObj->getProducts([], 100, 0);
$categories = $categoryObj->getAll('all');
?>

<div class="page-header">
    <h1 class="page-title">مدیریت محصولات</h1>
    <a href="<?php echo ADMIN_URL; ?>/product-edit.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> افزودن محصول جدید
    </a>
</div>

<div class="admin-card">
    <?php if (!empty($products)): ?>
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th style="width: 80px;">تصویر</th>
                        <th>نام محصول</th>
                        <th>دسته‌بندی</th>
                        <th>قیمت</th>
                        <th>موجودی</th>
                        <th>وضعیت</th>
                        <th style="width: 150px;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <?php if ($product['image']): ?>
                                    <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: var(--light-color); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image" style="color: var(--text-light);"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                <?php if ($product['brand']): ?>
                                    <div style="color: var(--text-light); font-size: 0.85rem;">
                                        <?php echo htmlspecialchars($product['brand']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td>
                                <?php if ($product['discount_price']): ?>
                                    <div><?php echo formatPrice($product['discount_price']); ?></div>
                                    <div style="color: var(--text-light); font-size: 0.85rem; text-decoration: line-through;">
                                        <?php echo formatPrice($product['price']); ?>
                                    </div>
                                <?php else: ?>
                                    <?php echo formatPrice($product['price']); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($product['stock'] > 0): ?>
                                    <span style="color: var(--secondary-color);"><?php echo $product['stock']; ?></span>
                                <?php else: ?>
                                    <span style="color: var(--danger-color);">ناموجود</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusMap = [
                                    'active' => ['فعال', 'status-active'],
                                    'inactive' => ['غیرفعال', 'status-inactive'],
                                    'out_of_stock' => ['ناموجود', 'status-inactive']
                                ];
                                $statusInfo = $statusMap[$product['status']] ?? ['نامشخص', 'status-inactive'];
                                ?>
                                <span class="status-badge <?php echo $statusInfo[1]; ?>">
                                    <?php echo $statusInfo[0]; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?php echo ADMIN_URL; ?>/product-edit.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-sm btn-primary" title="ویرایش">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/public/product-detail.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-sm btn-outline" title="مشاهده" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?delete=1&id=<?php echo $product['id']; ?>" 
                                       class="btn btn-sm btn-danger confirm-delete" title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 60px 20px;">
            <i class="fas fa-box" style="font-size: 4rem; color: var(--text-light); margin-bottom: 20px;"></i>
            <h3>محصولی وجود ندارد</h3>
            <p>برای افزودن محصول جدید روی دکمه زیر کلیک کنید</p>
            <a href="<?php echo ADMIN_URL; ?>/product-edit.php" class="btn btn-primary" style="margin-top: 20px;">
                <i class="fas fa-plus"></i> افزودن محصول جدید
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
