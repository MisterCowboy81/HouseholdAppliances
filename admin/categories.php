<?php
$pageTitle = 'مدیریت دسته‌بندی‌ها';
require_once 'header.php';
require_once __DIR__ . '/../includes/Category.php';

$categoryObj = new Category();

// Handle delete
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $result = $categoryObj->delete(intval($_GET['id']));
    if ($result['success']) {
        setFlashMessage('success', $result['message']);
    } else {
        setFlashMessage('error', $result['message']);
    }
    redirect(ADMIN_URL . '/categories.php');
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    
    if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {
        // Update
        $result = $categoryObj->update(intval($_POST['category_id']), [
            'name' => $name,
            'description' => $description,
            'status' => sanitize($_POST['status'])
        ]);
    } else {
        // Create
        $result = $categoryObj->create($name, $description);
    }
    
    if ($result['success']) {
        setFlashMessage('success', $result['message']);
        redirect(ADMIN_URL . '/categories.php');
    }
}

$categories = $categoryObj->getCategoriesWithCount();
$editCategory = null;

if (isset($_GET['edit'])) {
    $editCategory = $categoryObj->getById(intval($_GET['edit']));
}
?>

<div class="page-header">
    <h1 class="page-title">مدیریت دسته‌بندی‌ها</h1>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
    <!-- Add/Edit Form -->
    <div class="admin-card">
        <h2><?php echo $editCategory ? 'ویرایش دسته‌بندی' : 'افزودن دسته‌بندی جدید'; ?></h2>
        
        <form method="POST" action="">
            <?php if ($editCategory): ?>
                <input type="hidden" name="category_id" value="<?php echo $editCategory['id']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label class="form-label">نام دسته‌بندی <span style="color: red;">*</span></label>
                <input type="text" name="name" class="form-control" required 
                       value="<?php echo $editCategory ? htmlspecialchars($editCategory['name']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">توضیحات</label>
                <textarea name="description" class="form-control" rows="3"><?php echo $editCategory ? htmlspecialchars($editCategory['description']) : ''; ?></textarea>
            </div>
            
            <?php if ($editCategory): ?>
                <div class="form-group">
                    <label class="form-label">وضعیت</label>
                    <select name="status" class="form-control">
                        <option value="active" <?php echo $editCategory['status'] == 'active' ? 'selected' : ''; ?>>فعال</option>
                        <option value="inactive" <?php echo $editCategory['status'] == 'inactive' ? 'selected' : ''; ?>>غیرفعال</option>
                    </select>
                </div>
            <?php endif; ?>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo $editCategory ? 'بروزرسانی' : 'افزودن'; ?>
                </button>
                <?php if ($editCategory): ?>
                    <a href="<?php echo ADMIN_URL; ?>/categories.php" class="btn btn-outline">انصراف</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Categories List -->
    <div class="admin-card">
        <h2>لیست دسته‌بندی‌ها</h2>
        
        <?php if (!empty($categories)): ?>
            <div class="admin-table">
                <table>
                    <thead>
                        <tr>
                            <th>نام</th>
                            <th>توضیحات</th>
                            <th>تعداد محصولات</th>
                            <th>وضعیت</th>
                            <th style="width: 150px;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars(substr($cat['description'], 0, 50)); ?><?php echo strlen($cat['description']) > 50 ? '...' : ''; ?></td>
                                <td><?php echo $cat['product_count']; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $cat['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $cat['status'] == 'active' ? 'فعال' : 'غیرفعال'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?edit=<?php echo $cat['id']; ?>" class="btn btn-sm btn-primary" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=1&id=<?php echo $cat['id']; ?>" 
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
            <p style="text-align: center; color: var(--text-light); padding: 40px;">دسته‌بندی وجود ندارد</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>
