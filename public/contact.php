<?php
$pageTitle = 'تماس با ما';
require_once 'header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real application, you would send an email or save to database
    $message = 'پیام شما با موفقیت ارسال شد. به زودی با شما تماس خواهیم گرفت.';
}
?>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">تماس با ما</h1>
            <p class="section-subtitle">ما همیشه آماده پاسخگویی به سوالات شما هستیم</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
            <!-- Contact Form -->
            <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: var(--shadow);">
                <h2 style="margin-bottom: 20px;">فرم تماس</h2>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">نام و نام خانوادگی</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">ایمیل</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">موضوع</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">پیام</label>
                        <textarea name="message" class="form-control" rows="5" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">ارسال پیام</button>
                </form>
            </div>

            <!-- Contact Info -->
            <div>
                <div
                    style="background: white; padding: 40px; border-radius: 12px; box-shadow: var(--shadow); margin-bottom: 30px;">
                    <h2 style="margin-bottom: 20px;">اطلاعات تماس</h2>

                    <div style="display: flex; flex-direction: column; gap: 20px;">
                        <div style="display: flex; align-items: start; gap: 15px;">
                            <i class="fas fa-map-marker-alt"
                                style="font-size: 1.5rem; color: var(--primary-color); margin-top: 5px;"></i>
                            <div>
                                <strong>آدرس:</strong>
                                <p style="margin-top: 5px; color: var(--text-light);">شیراز شهر صدرا فاز یک</p>
                            </div>
                        </div>

                        <div style="display: flex; align-items: start; gap: 15px;">
                            <i class="fas fa-phone"
                                style="font-size: 1.5rem; color: var(--primary-color); margin-top: 5px;"></i>
                            <div>
                                <strong>تلفن:</strong>
                                <p style="margin-top: 5px; color: var(--text-light);">071-32345678</p>
                            </div>
                        </div>

                        <div style="display: flex; align-items: start; gap: 15px;">
                            <i class="fas fa-envelope"
                                style="font-size: 1.5rem; color: var(--primary-color); margin-top: 5px;"></i>
                            <div>
                                <strong>ایمیل:</strong>
                                <p style="margin-top: 5px; color: var(--text-light);">info@example.com</p>
                            </div>
                        </div>

                        <div style="display: flex; align-items: start; gap: 15px;">
                            <i class="fas fa-clock"
                                style="font-size: 1.5rem; color: var(--primary-color); margin-top: 5px;"></i>
                            <div>
                                <strong>ساعات کاری:</strong>
                                <p style="margin-top: 5px; color: var(--text-light);">شنبه تا پنجشنبه، 9 صبح تا 6 عصر
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Media -->
                <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: var(--shadow);">
                    <h2 style="margin-bottom: 20px;">شبکه‌های اجتماعی</h2>
                    <div style="display: flex; gap: 15px;">
                        <a href="#"
                            style="width: 50px; height: 50px; background: #E1306C; color: white; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 1.5rem;">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#"
                            style="width: 50px; height: 50px; background: #0088cc; color: white; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 1.5rem;">
                            <i class="fab fa-telegram"></i>
                        </a>
                        <a href="#"
                            style="width: 50px; height: 50px; background: #25D366; color: white; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 1.5rem;">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>