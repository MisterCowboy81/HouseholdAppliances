<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/User.php';

// If already logged in, redirect to home
if (isLoggedIn()) {
    redirect(SITE_URL . '/public/index.php');
}

$error = '';
$userObj = new User();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    
    if ($password !== $confirmPassword) {
        $error = 'رمز عبور و تکرار آن یکسان نیستند';
    } else {
        $result = $userObj->register($username, $email, $password, $fullName, $phone);
        
        if ($result['success']) {
            setFlashMessage('success', 'ثبت نام با موفقیت انجام شد. لطفا وارد شوید.');
            redirect(SITE_URL . '/public/login.php');
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = 'ثبت نام';
require_once 'header.php';
?>

<section class="section">
    <div class="container">
        <div style="max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: var(--shadow);">
            <h1 class="text-center mb-20">ثبت نام</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">نام کاربری <span style="color: red;">*</span></label>
                    <input type="text" name="username" class="form-control" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">ایمیل <span style="color: red;">*</span></label>
                    <input type="email" name="email" class="form-control" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">نام و نام خانوادگی <span style="color: red;">*</span></label>
                    <input type="text" name="full_name" class="form-control" required 
                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">شماره تلفن</label>
                    <input type="text" name="phone" class="form-control" 
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">رمز عبور <span style="color: red;">*</span></label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                    <small style="color: var(--text-light);">حداقل 6 کاراکتر</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">تکرار رمز عبور <span style="color: red;">*</span></label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="6">
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">ثبت نام</button>
            </form>
            
            <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <p>قبلا ثبت نام کرده‌اید؟ <a href="<?php echo SITE_URL; ?>/public/login.php" style="color: var(--primary-color); font-weight: bold;">وارد شوید</a></p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>
