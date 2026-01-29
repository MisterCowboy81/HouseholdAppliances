        </main>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script>
        // Admin-specific JS
        $(document).ready(function() {
            // Set sidebar top position based on header height
            function updateSidebarPosition() {
                if ($(window).width() > 768) {
                    var headerHeight = $('.admin-header').outerHeight() || 70;
                    $('.admin-sidebar').css({
                        'top': headerHeight + 'px',
                        'max-height': 'calc(100vh - ' + headerHeight + 'px)'
                    });
                }
            }
            
            // Update on load and resize
            updateSidebarPosition();
            $(window).on('resize', function() {
                updateSidebarPosition();
            });
            
            // Mobile menu toggle
            $('#adminMenuToggle').on('click', function() {
                $('.admin-sidebar').toggleClass('mobile-open');
                $('body').toggleClass('admin-sidebar-open');
                var icon = $(this).find('i');
                if (icon.hasClass('fa-bars')) {
                    icon.removeClass('fa-bars').addClass('fa-times');
                } else {
                    icon.removeClass('fa-times').addClass('fa-bars');
                }
            });
            
            // Close sidebar when clicking outside on mobile
            $(document).on('click', function(e) {
                if ($(window).width() <= 768) {
                    if (!$(e.target).closest('.admin-sidebar, #adminMenuToggle').length) {
                        $('.admin-sidebar').removeClass('mobile-open');
                        $('body').removeClass('admin-sidebar-open');
                        $('#adminMenuToggle i').removeClass('fa-times').addClass('fa-bars');
                    }
                }
            });
            
            // Highlight active menu item
            var currentPath = window.location.pathname;
            $('aside nav a.admin-nav-link').each(function() {
                var href = $(this).attr('href');
                var pathMatch = currentPath.includes(href) || currentPath === href || 
                               (href.includes('index.php') && currentPath.endsWith('/admin/')) ||
                               (href.includes('index.php') && currentPath.endsWith('/admin/index.php'));
                
                if (pathMatch) {
                    $(this).addClass('active');
                }
            });
            
            // Close mobile menu when clicking a link
            $('.admin-nav-link').on('click', function() {
                if ($(window).width() <= 768) {
                    $('.admin-sidebar').removeClass('mobile-open');
                    $('body').removeClass('admin-sidebar-open');
                    $('#adminMenuToggle i').removeClass('fa-times').addClass('fa-bars');
                }
            });
        });
    </script>
</body>
</html>
