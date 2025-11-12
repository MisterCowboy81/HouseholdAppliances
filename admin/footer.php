        </main>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script>
        // Admin-specific JS
        $(document).ready(function() {
            // Highlight active menu item
            var currentPath = window.location.pathname;
            $('aside nav a').each(function() {
                if ($(this).attr('href') === currentPath || currentPath.includes($(this).attr('href'))) {
                    $(this).css({
                        'background': 'var(--primary-color)',
                        'color': 'white',
                        'border-radius': '8px',
                        'margin': '0 10px'
                    });
                }
            });
            
            // Hover effect for menu items
            $('aside nav a').hover(
                function() {
                    if (!$(this).css('background-color').includes('rgb(37, 99, 235)')) {
                        $(this).css('background', '#f3f4f6');
                    }
                },
                function() {
                    if (!$(this).css('background-color').includes('rgb(37, 99, 235)')) {
                        $(this).css('background', 'transparent');
                    }
                }
            );
        });
    </script>
</body>
</html>
