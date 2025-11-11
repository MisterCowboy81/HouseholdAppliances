<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/User.php';
require_once __DIR__ . '/../includes/Cart.php';

// If already logged in, redirect to home
if (isLoggedIn()) {
    redirect(SITE_URL . '/public/index.php');
}

$error = '';
$userObj = new User();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $result = $userObj->login($username, $password);
    
    if ($result['success']) {
        // Transfer cart to user
        $cartObj = new Cart();
        $cartObj->transferToUser($result['user']['id']);
        
        setFlashMessage('success', $result['message']);
        redirect(SITE_URL . '/public/index.php');
    } else {
        $error = $result['message'];
    }
}

$pageTitle = 'ورود';
require_once 'header.php';
?>

<section class="section">
    <div class="container">
        <div style="max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: var(--shadow);">
            <h1 class="text-center mb-20">ورود به حساب کاربری</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">نام کاربری یا ایمیل</label>
                    <input type="text" name="username" class="form-control" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">رمز عبور</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">ورود</button>
            </form>
            
            <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <p>حساب کاربری ندارید؟ <a href="<?php echo SITE_URL; ?>/public/register.php" style="color: var(--primary-color); font-weight: bold;">ثبت نام کنید</a></p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>
