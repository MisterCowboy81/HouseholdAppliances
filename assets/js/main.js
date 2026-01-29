/**
 * Main JavaScript for Household Appliances E-Commerce
 * Requires jQuery
 */

$(document).ready(function() {
    
    // Get base URL from the page
    var baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.indexOf('/public/'));
    
    // Mobile menu toggle
    $('#mobileMenuToggle').on('click', function() {
        $('#mainNav').toggleClass('active');
        var icon = $(this).find('i');
        if (icon.hasClass('fa-bars')) {
            icon.removeClass('fa-bars').addClass('fa-times');
        } else {
            icon.removeClass('fa-times').addClass('fa-bars');
        }
    });
    
    // Mobile dropdown toggle
    $('.dropdown-toggle').on('click', function(e) {
        if ($(window).width() <= 768) {
            e.preventDefault();
            $(this).parent('.dropdown').toggleClass('active');
        }
    });
    
    // Close mobile menu when clicking outside
    $(document).on('click', function(e) {
        if ($(window).width() <= 768) {
            if (!$(e.target).closest('#mainNav, #mobileMenuToggle').length) {
                $('#mainNav').removeClass('active');
                $('#mobileMenuToggle i').removeClass('fa-times').addClass('fa-bars');
            }
        }
    });
    
    // Add to cart functionality
    $(document).on('click', '.add-to-cart', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var productId = $button.data('product-id');
        var quantity = $button.data('quantity') || 1;
        
        // Disable button during request
        $button.prop('disabled', true);
        var originalText = $button.html();
        $button.html('<i class="fas fa-spinner fa-spin"></i> در حال افزودن...');
        
        $.ajax({
            url: baseUrl + '/public/ajax/cart.php',
            method: 'POST',
            data: {
                action: 'add',
                product_id: productId,
                quantity: quantity
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                    // Update cart count badge immediately with a small delay to ensure server processed
                    setTimeout(function() {
                        updateCartCount();
                    }, 100);
                } else {
                    showNotification('error', response.message);
                }
            },
            error: function() {
                showNotification('error', 'خطا در برقراری ارتباط');
            },
            complete: function() {
                // Re-enable button
                $button.prop('disabled', false);
                $button.html(originalText);
            }
        });
    });
    
    // Update cart quantity
    $(document).on('change', '.update-cart-qty', function() {
        var $input = $(this);
        var productId = $input.data('product-id');
        var quantity = parseInt($input.val()) || 1;
        var $row = $input.closest('tr');
        var $subtotalCell = $row.find('.item-subtotal');
        var $totalAmount = $('.cart-total-amount');
        
        // Validate quantity
        var min = parseInt($input.attr('min')) || 1;
        var max = parseInt($input.attr('max')) || 999;
        
        if (quantity < min) {
            quantity = min;
            $input.val(min);
        }
        if (quantity > max) {
            quantity = max;
            $input.val(max);
            showNotification('warning', 'تعداد انتخابی بیشتر از موجودی است');
        }
        
        // Disable input during update
        $input.prop('disabled', true);
        
        $.ajax({
            url: baseUrl + '/public/ajax/cart.php',
            method: 'POST',
            data: {
                action: 'update',
                product_id: productId,
                quantity: quantity
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update subtotal in the row with animation
                    if (response.subtotal !== undefined) {
                        $subtotalCell.fadeOut(100, function() {
                            $(this).text(formatPrice(response.subtotal)).fadeIn(200);
                        });
                    }
                    
                    // Update total cart amount with animation
                    if (response.total !== undefined) {
                        $totalAmount.fadeOut(100, function() {
                            $(this).text(formatPrice(response.total)).fadeIn(200);
                        });
                    }
                    
                    // Update cart count badge
                    updateCartCount();
                    
                    // Show success notification
                    showNotification('success', response.message || 'تعداد محصول بروزرسانی شد');
                } else {
                    showNotification('error', response.message);
                    // Revert to previous value on error
                    var prevValue = $input.data('previous-value') || 1;
                    $input.val(prevValue);
                }
            },
            error: function() {
                showNotification('error', 'خطا در برقراری ارتباط');
                var prevValue = $input.data('previous-value') || 1;
                $input.val(prevValue);
            },
            complete: function() {
                $input.prop('disabled', false);
            }
        });
    });
    
    // Store previous value before change
    $(document).on('focus', '.update-cart-qty', function() {
        $(this).data('previous-value', $(this).val());
    });
    
    // Remove from cart
    $(document).on('click', '.remove-from-cart', function(e) {
        e.preventDefault();
        
        if (!confirm('آیا مطمئن هستید که می‌خواهید این محصول را حذف کنید؟')) {
            return;
        }
        
        var $button = $(this);
        var $row = $button.closest('tr');
        var productId = $button.data('product-id');
        var $totalAmount = $('.cart-total-amount');
        
        // Disable button during request
        $button.prop('disabled', true);
        
        $.ajax({
            url: baseUrl + '/public/ajax/cart.php',
            method: 'POST',
            data: {
                action: 'remove',
                product_id: productId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Remove row with animation
                    $row.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Check if cart is empty
                        if ($('.cart-table tbody tr').length === 0) {
                            location.reload();
                        }
                    });
                    
                    // Update total cart amount
                    if (response.total !== undefined) {
                        $totalAmount.fadeOut(100, function() {
                            $(this).text(formatPrice(response.total)).fadeIn(200);
                        });
                    }
                    
                    // Update cart count badge
                    updateCartCount();
                    
                    // Show success notification
                    showNotification('success', response.message);
                } else {
                    showNotification('error', response.message);
                    $button.prop('disabled', false);
                }
            },
            error: function() {
                showNotification('error', 'خطا در برقراری ارتباط');
                $button.prop('disabled', false);
            }
        });
    });
    
    // Update cart count on page load (with delay to ensure DOM is ready)
    setTimeout(function() {
        updateCartCount();
    }, 500);
    
    // Search form submit
    $('.search-form').on('submit', function(e) {
        var searchInput = $(this).find('input[name="q"]');
        if (searchInput.val().trim() === '') {
            e.preventDefault();
            showNotification('warning', 'لطفا عبارت جستجو را وارد کنید');
        }
    });
    
    // Image preview for file uploads
    $('input[type="file"].image-upload').on('change', function(e) {
        var preview = $(this).data('preview');
        if (preview && this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $(preview).attr('src', e.target.result).show();
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Confirm delete actions
    $('.confirm-delete').on('click', function(e) {
        if (!confirm('آیا مطمئن هستید که می‌خواهید این مورد را حذف کنید؟')) {
            e.preventDefault();
        }
    });
    
    // Auto-hide alerts
    $('.alert').each(function() {
        var alert = $(this);
        setTimeout(function() {
            alert.fadeOut();
        }, 5000);
    });
    
    // Smooth scroll to top
    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn(300).css('display', 'block');
        } else {
            $('.back-to-top').fadeOut(300);
        }
    });
    
    $('.back-to-top').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({ scrollTop: 0 }, 600);
        $(this).css('transform', 'scale(0.9)');
        setTimeout(function() {
            $('.back-to-top').css('transform', 'scale(1)');
        }, 200);
    });
    
    // Back to top hover effect
    $('.back-to-top').hover(
        function() {
            $(this).css({
                'transform': 'scale(1.1) translateY(-5px)',
                'box-shadow': '0 12px 35px rgba(37, 99, 235, 0.5)'
            });
        },
        function() {
            $(this).css({
                'transform': 'scale(1) translateY(0)',
                'box-shadow': '0 8px 25px rgba(37, 99, 235, 0.4)'
            });
        }
    );
    
    // Product quantity controls
    $('.qty-btn').on('click', function() {
        var input = $(this).siblings('.qty-input');
        var currentVal = parseInt(input.val()) || 1;
        
        if ($(this).hasClass('qty-plus')) {
            input.val(currentVal + 1);
        } else if ($(this).hasClass('qty-minus') && currentVal > 1) {
            input.val(currentVal - 1);
        }
    });
    
    // Filter products
    $('#filter-form select').on('change', function() {
        $('#filter-form').submit();
    });
    
    // Price range slider (if using range inputs)
    $('#price-min, #price-max').on('change', function() {
        var minPrice = $('#price-min').val();
        var maxPrice = $('#price-max').val();
        $('#price-range-display').text(formatPrice(minPrice) + ' - ' + formatPrice(maxPrice));
    });
});

/**
 * Update cart count badge
 */
function updateCartCount() {
    var baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.indexOf('/public/'));
    if (!baseUrl || baseUrl === window.location.origin) {
        // Fallback if path doesn't contain /public/
        baseUrl = window.location.origin;
    }
    
    $.ajax({
        url: baseUrl + '/public/ajax/cart.php',
        method: 'POST',
        data: { action: 'count' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var count = parseInt(response.count) || 0;
                var $badges = $('.cart-badge');
                
                // If badge doesn't exist, create it
                if ($badges.length === 0) {
                    $('.header-link[href*="cart.php"]').each(function() {
                        var $badge = $('<span class="cart-badge"></span>');
                        $(this).append($badge);
                    });
                    $badges = $('.cart-badge');
                }
                
                // Update all badges
                $badges.each(function() {
                    var $badge = $(this);
                    var currentCount = parseInt($badge.text()) || 0;
                    
                    if (count > 0) {
                        if (currentCount !== count) {
                            // Only animate if count changed
                            if ($badge.is(':visible') && currentCount > 0) {
                                // Animate number change
                                $badge.fadeOut(100, function() {
                                    $(this).text(count).fadeIn(200);
                                });
                            } else {
                                // Show badge if it was hidden
                                $badge.text(count).css('display', '').fadeIn(200);
                            }
                        }
                    } else {
                        // Hide badge if count is 0
                        if ($badge.is(':visible')) {
                            $badge.fadeOut(200, function() {
                                $(this).css('display', 'none');
                            });
                        }
                    }
                });
            }
        },
        error: function(xhr, status, error) {
            // Silently fail - don't show error for badge update
            console.log('Failed to update cart count:', error);
        }
    });
}

/**
 * Show notification as snackbar
 */
function showNotification(type, message) {
    // Ensure snackbar container exists
    var $container = $('.snackbar-container');
    if ($container.length === 0) {
        $container = $('<div class="snackbar-container"></div>');
        $('body').append($container);
    }
    
    // Icon mapping
    var icons = {
        success: '<i class="fas fa-check-circle"></i>',
        error: '<i class="fas fa-exclamation-circle"></i>',
        warning: '<i class="fas fa-exclamation-triangle"></i>',
        info: '<i class="fas fa-info-circle"></i>'
    };
    
    // Check if RTL
    var isRTL = $('body').css('direction') === 'rtl' || $('html').attr('dir') === 'rtl' || $('body').attr('dir') === 'rtl';
    
    // Create snackbar
    var $snackbar = $('<div>')
        .addClass('snackbar snackbar-' + type)
        .html(
            '<div class="snackbar-icon">' + (icons[type] || icons.info) + '</div>' +
            '<div class="snackbar-message">' + message + '</div>' +
            '<button class="snackbar-close" aria-label="بستن"><i class="fas fa-times"></i></button>' +
            '<div class="snackbar-progress"></div>'
        );
    
    // Apply RTL animation if needed
    if (isRTL) {
        $snackbar.css('animation', 'slideInSnackbarRTL 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)');
    }
    
    // Add to container
    $container.append($snackbar);
    
    // Auto remove after 5 seconds
    var timeout = setTimeout(function() {
        removeSnackbar($snackbar);
    }, 5000);
    
    // Close button handler
    $snackbar.find('.snackbar-close').on('click', function() {
        clearTimeout(timeout);
        removeSnackbar($snackbar);
    });
    
    // Pause on hover
    $snackbar.on('mouseenter', function() {
        clearTimeout(timeout);
        $snackbar.find('.snackbar-progress').css('animation-play-state', 'paused');
    });
    
    $snackbar.on('mouseleave', function() {
        timeout = setTimeout(function() {
            removeSnackbar($snackbar);
        }, 5000);
        $snackbar.find('.snackbar-progress').css('animation-play-state', 'running');
    });
    
    // Trigger animation (already handled by CSS)
}

/**
 * Remove snackbar with animation
 */
function removeSnackbar($snackbar) {
    if ($snackbar.hasClass('hiding')) {
        return; // Already removing
    }
    
    // Check if RTL
    var isRTL = $('body').css('direction') === 'rtl' || $('html').attr('dir') === 'rtl' || $('body').attr('dir') === 'rtl';
    
    $snackbar.addClass('hiding');
    
    // Apply RTL animation if needed
    if (isRTL) {
        $snackbar.css('animation', 'slideOutSnackbarRTL 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards');
    }
    
    setTimeout(function() {
        $snackbar.remove();
        // Remove container if empty
        var $container = $('.snackbar-container');
        if ($container.children().length === 0) {
            $container.remove();
        }
    }, 300);
}

/**
 * Format price
 */
function formatPrice(price) {
    return new Intl.NumberFormat('fa-IR').format(price) + ' ریال';
}

/**
 * Validate email
 */
function validateEmail(email) {
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Form validation
 */
function validateForm(formId) {
    var isValid = true;
    var form = $(formId);
    
    form.find('[required]').each(function() {
        var field = $(this);
        var value = field.val().trim();
        
        if (value === '') {
            isValid = false;
            field.addClass('error');
            showNotification('error', 'لطفا تمام فیلدهای الزامی را پر کنید');
            return false;
        } else {
            field.removeClass('error');
        }
        
        // Email validation
        if (field.attr('type') === 'email' && !validateEmail(value)) {
            isValid = false;
            field.addClass('error');
            showNotification('error', 'ایمیل وارد شده معتبر نیست');
            return false;
        }
    });
    
    return isValid;
}
