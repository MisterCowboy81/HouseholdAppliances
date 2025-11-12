    </main>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-store"></i> <?php echo SITE_NAME; ?></h3>
                    <p>فروشگاه آنلاین لوازم خانگی با بهترین قیمت، کیفیت عالی و ضمانت اصالت کالا</p>
                    <div style="margin-top: 15px;">
                        <a href="#" style="margin-left: 10px; font-size: 1.5rem;"><i class="fab fa-instagram"></i></a>
                        <a href="#" style="margin-left: 10px; font-size: 1.5rem;"><i class="fab fa-telegram"></i></a>
                        <a href="#" style="margin-left: 10px; font-size: 1.5rem;"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>دسترسی سریع</h3>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/public/index.php">صفحه اصلی</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/public/products.php">محصولات</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/public/about.php">درباره ما</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/public/contact.php">تماس با ما</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="<?php echo SITE_URL; ?>/public/profile.php">حساب کاربری</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>خدمات مشتریان</h3>
                    <ul>
                        <li><a href="#">پیگیری سفارش</a></li>
                        <li><a href="#">شیوه‌های پرداخت</a></li>
                        <li><a href="#">رویه‌های بازگشت کالا</a></li>
                        <li><a href="#">ضمانت اصالت کالا</a></li>
                        <li><a href="#">سوالات متداول</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>تماس با ما</h3>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> تهران، خیابان ولیعصر</li>
                        <li><i class="fas fa-phone"></i> 021-12345678</li>
                        <li><i class="fas fa-envelope"></i> info@example.com</li>
                        <li><i class="fas fa-clock"></i> شنبه تا پنجشنبه، 9 صبح تا 6 عصر</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. تمامی حقوق محفوظ است.</p>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" style="display: none; position: fixed; bottom: 30px; left: 30px; background: var(--primary-color); color: white; width: 50px; height: 50px; text-align: center; line-height: 50px; border-radius: 50%; box-shadow: var(--shadow-lg); z-index: 999;">
        <i class="fas fa-arrow-up"></i>
    </a>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Main JS -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
