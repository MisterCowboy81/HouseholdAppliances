<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/User.php';

requireLogin();
requireAdmin();

$userObj = new User();

// Handle delete
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $userId = intval($_GET['id']);
    $currentUserId = getCurrentUserId();
    
    // Prevent deleting yourself
    if ($userId == $currentUserId) {
        setFlashMessage('error', 'نمی‌توانید حساب کاربری خود را حذف کنید');
    } else {
        $result = $userObj->deleteUser($userId);
        if ($result['success']) {
            setFlashMessage('success', $result['message']);
        } else {
            setFlashMessage('error', $result['message']);
        }
    }
    redirect(ADMIN_URL . '/users.php');
}

$users = $userObj->getAllUsers(100, 0);

$pageTitle = 'مدیریت کاربران';
require_once 'header.php';
?>

<div class="page-header">
    <h1 class="page-title">مدیریت کاربران</h1>
    <a href="<?php echo ADMIN_URL; ?>/user-add.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> افزودن کاربر جدید
    </a>
</div>

<div class="admin-card">
    <?php if (!empty($users)): ?>
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>نام کاربری</th>
                        <th>نام و نام خانوادگی</th>
                        <th>ایمیل</th>
                        <th>شماره تماس</th>
                        <th>نقش</th>
                        <th>وضعیت</th>
                        <th>تاریخ ثبت‌نام</th>
                        <th style="width: 120px;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                            <td>
                                <?php if ($user['role'] == 'admin'): ?>
                                    <span class="badge badge-danger">مدیر</span>
                                <?php else: ?>
                                    <span class="badge badge-info">مشتری</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusMap = [
                                    'active' => ['فعال', 'status-active'],
                                    'inactive' => ['غیرفعال', 'status-inactive'],
                                    'banned' => ['مسدود', 'status-cancelled']
                                ];
                                $statusInfo = $statusMap[$user['status']] ?? ['نامشخص', 'status-inactive'];
                                ?>
                                <span class="status-badge <?php echo $statusInfo[1]; ?>">
                                    <?php echo $statusInfo[0]; ?>
                                </span>
                            </td>
                            <td><?php echo date('Y/m/d', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="<?php echo ADMIN_URL; ?>/user-edit.php?id=<?php echo $user['id']; ?>" 
                                       class="btn btn-sm btn-outline" title="ویرایش">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['id'] != getCurrentUserId()): ?>
                                        <a href="?delete=1&id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('آیا مطمئن هستید که می‌خواهید این کاربر را حذف کنید؟')"
                                           title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 60px 20px;">
            <i class="fas fa-users" style="font-size: 4rem; color: var(--text-light); margin-bottom: 20px;"></i>
            <h3>کاربری وجود ندارد</h3>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
