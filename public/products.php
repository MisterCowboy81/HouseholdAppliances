<?php
$pageTitle = 'محصولات';
require_once 'header.php';
require_once __DIR__ . '/../includes/Product.php';

$productObj = new Product();

// Get filters
$filters = [];
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $filters['category_id'] = intval($_GET['category']);
}
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $filters['search'] = sanitize($_GET['q']);
}
if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $filters['min_price'] = floatval($_GET['min_price']);
}
if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $filters['max_price'] = floatval($_GET['max_price']);
}

// Sorting
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'price_asc':
            $filters['order_by'] = 'p.price ASC';
            break;
        case 'price_desc':
            $filters['order_by'] = 'p.price DESC';
            break;
        case 'newest':
            $filters['order_by'] = 'p.created_at DESC';
            break;
        default:
            $filters['order_by'] = 'p.created_at DESC';
    }
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

$products = $productObj->getProducts($filters, $perPage, $offset);
$totalProducts = $productObj->getTotalCount($filters);
$totalPages = ceil($totalProducts / $perPage);

// Get category name if filtered
$categoryName = '';
if (isset($filters['category_id'])) {
    $cat = $categoryObj->getById($filters['category_id']);
    $categoryName = $cat ? $cat['name'] : '';
}
?>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">
                <?php echo $categoryName ? htmlspecialchars($categoryName) : 'محصولات'; ?>
            </h1>
            <?php if (isset($filters['search'])): ?>
                <p class="section-subtitle">نتایج جستجو برای: "<?php echo htmlspecialchars($filters['search']); ?>"</p>
            <?php endif; ?>
        </div>
        
        <!-- Filters and Sorting -->
        <div style="background: white; padding: 20px; border-radius: 12px; margin-bottom: 30px; box-shadow: var(--shadow);">
            <form method="GET" id="filter-form" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
                <?php if (isset($_GET['q'])): ?>
                    <input type="hidden" name="q" value="<?php echo htmlspecialchars($_GET['q']); ?>">
                <?php endif; ?>
                
                <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                    <select name="category" class="form-control">
                        <option value="">همه دسته‌بندی‌ها</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                    <select name="sort" class="form-control">
                        <option value="">مرتب سازی</option>
                        <option value="newest" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'newest') ? 'selected' : ''; ?>>جدیدترین</option>
                        <option value="price_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : ''; ?>>ارزان‌ترین</option>
                        <option value="price_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : ''; ?>>گران‌ترین</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">اعمال فیلتر</button>
                <a href="<?php echo SITE_URL; ?>/public/products.php" class="btn btn-outline">حذف فیلتر</a>
            </form>
        </div>
        
        <!-- Products Grid -->
        <?php if (!empty($products)): ?>
            <div class="grid grid-4">
                <?php foreach ($products as $product): ?>
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
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div style="display: flex; justify-content: center; gap: 10px; margin-top: 40px;">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="btn btn-outline">قبلی</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                           class="btn <?php echo $i == $page ? 'btn-primary' : 'btn-outline'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="btn btn-outline">بعدی</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px;">
                <i class="fas fa-search" style="font-size: 4rem; color: var(--text-light); margin-bottom: 20px;"></i>
                <h3>محصولی یافت نشد</h3>
                <p>متاسفانه محصولی با این مشخصات پیدا نشد.</p>
                <a href="<?php echo SITE_URL; ?>/public/products.php" class="btn btn-primary" style="margin-top: 20px;">مشاهده همه محصولات</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'footer.php'; ?>
