<?php
$pageTitle = 'ویرایش محصول';
require_once 'header.php';
require_once __DIR__ . '/../includes/Product.php';
require_once __DIR__ . '/../includes/Category.php';

$productObj = new Product();
$categoryObj = new Category();

$isEdit = isset($_GET['id']);
$product = null;
$error = '';

if ($isEdit) {
    $product = $productObj->getProductById(intval($_GET['id']));
    if (!$product) {
        setFlashMessage('error', 'محصول یافت نشد');
        redirect(ADMIN_URL . '/products.php');
    }
    $pageTitle = 'ویرایش محصول';
} else {
    $pageTitle = 'افزودن محصول جدید';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'category_id' => intval($_POST['category_id']),
        'name' => sanitize($_POST['name']),
        'description' => sanitize($_POST['description']),
        'price' => floatval($_POST['price']),
        'discount_price' => !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null,
        'stock' => intval($_POST['stock']),
        'brand' => sanitize($_POST['brand']),
        'model' => sanitize($_POST['model']),
        'warranty' => sanitize($_POST['warranty']),
        'status' => sanitize($_POST['status']),
        'featured' => isset($_POST['featured']) ? 1 : 0
    ];
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = UPLOADS_PATH . '/products';
        $imageName = uploadImage($_FILES['image'], $uploadDir);
        
        if ($imageName) {
            // Delete old image if editing
            if ($isEdit && $product['image']) {
                deleteFile($uploadDir . '/' . $product['image']);
            }
            $data['image'] = $imageName;
        }
    } elseif ($isEdit && $product['image']) {
        $data['image'] = $product['image'];
    } else {
        $data['image'] = null;
    }
    
    if ($isEdit) {
        $result = $productObj->updateProduct($product['id'], $data);
    } else {
        $result = $productObj->createProduct($data);
    }
    
    if ($result['success']) {
        setFlashMessage('success', $result['message']);
        redirect(ADMIN_URL . '/products.php');
    } else {
        $error = $result['message'];
    }
}

$categories = $categoryObj->getAll('all');
?>

<div class="page-header">
    <h1 class="page-title"><?php echo $pageTitle; ?></h1>
    <a href="<?php echo ADMIN_URL; ?>/products.php" class="btn btn-outline">
        <i class="fas fa-arrow-right"></i> بازگشت
    </a>
</div>

<div class="admin-card">
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">نام محصول <span style="color: red;">*</span></label>
                <input type="text" name="name" class="form-control" required 
                       value="<?php echo $product ? htmlspecialchars($product['name']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">دسته‌بندی <span style="color: red;">*</span></label>
                <select name="category_id" class="form-control" required>
                    <option value="">انتخاب کنید</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                                <?php echo ($product && $product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">برند</label>
                <input type="text" name="brand" class="form-control" 
                       value="<?php echo $product ? htmlspecialchars($product['brand']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">مدل</label>
                <input type="text" name="model" class="form-control" 
                       value="<?php echo $product ? htmlspecialchars($product['model']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">قیمت (ریال) <span style="color: red;">*</span></label>
                <input type="number" name="price" class="form-control" required min="0" step="1000"
                       value="<?php echo $product ? $product['price'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">قیمت با تخفیف (ریال)</label>
                <input type="number" name="discount_price" class="form-control" min="0" step="1000"
                       value="<?php echo $product && $product['discount_price'] ? $product['discount_price'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">موجودی <span style="color: red;">*</span></label>
                <input type="number" name="stock" class="form-control" required min="0"
                       value="<?php echo $product ? $product['stock'] : '0'; ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">گارانتی</label>
                <input type="text" name="warranty" class="form-control" 
                       value="<?php echo $product ? htmlspecialchars($product['warranty']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">وضعیت <span style="color: red;">*</span></label>
                <select name="status" class="form-control" required>
                    <option value="active" <?php echo ($product && $product['status'] == 'active') ? 'selected' : ''; ?>>فعال</option>
                    <option value="inactive" <?php echo ($product && $product['status'] == 'inactive') ? 'selected' : ''; ?>>غیرفعال</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <input type="checkbox" name="featured" value="1" <?php echo ($product && $product['featured']) ? 'checked' : ''; ?>>
                    محصول ویژه
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">توضیحات</label>
            <textarea name="description" class="form-control" rows="5"><?php echo $product ? htmlspecialchars($product['description']) : ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">تصویر محصول</label>
            <input type="file" name="image" class="form-control image-upload" accept="image/*" data-preview="#image-preview">
            
            <div class="image-preview" id="image-preview">
                <?php if ($product && $product['image']): ?>
                    <img src="<?php echo SITE_URL; ?>/uploads/products/<?php echo htmlspecialchars($product['image']); ?>" alt="Preview">
                <?php else: ?>
                    <i class="fas fa-image" style="font-size: 3rem; color: var(--text-light);"></i>
                <?php endif; ?>
            </div>
        </div>
        
        <div style="display: flex; gap: 15px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> ذخیره
            </button>
            <a href="<?php echo ADMIN_URL; ?>/products.php" class="btn btn-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>
</div>

<?php require_once 'footer.php'; ?>
