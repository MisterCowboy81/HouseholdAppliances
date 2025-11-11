/**
 * Main JavaScript for Household Appliances E-Commerce
 * Requires jQuery
 */

$(document).ready(function() {
    
    // Get base URL from the page
    var baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.indexOf('/public/'));
    
    // Add to cart functionality
    $('.add-to-cart').on('click', function(e) {
        e.preventDefault();
        
        var productId = $(this).data('product-id');
        var quantity = $(this).data('quantity') || 1;
        
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
                    updateCartCount();
                } else {
                    showNotification('error', response.message);
                }
            },
            error: function() {
                showNotification('error', 'خطا در برقراری ارتباط');
            }
        });
    });
    
    // Update cart quantity
    $('.update-cart-qty').on('change', function() {
        var productId = $(this).data('product-id');
        var quantity = $(this).val();
        
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
                    location.reload();
                } else {
                    showNotification('error', response.message);
                }
            }
        });
    });
    
    // Remove from cart
    $('.remove-from-cart').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('آیا مطمئن هستید که می‌خواهید این محصول را حذف کنید؟')) {
            return;
        }
        
        var productId = $(this).data('product-id');
        
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
                    location.reload();
                } else {
                    showNotification('error', response.message);
                }
            }
        });
    });
    
    // Update cart count on page load
    updateCartCount();
    
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
        if ($(this).scrollTop() > 200) {
            $('.back-to-top').fadeIn();
        } else {
            $('.back-to-top').fadeOut();
        }
    });
    
    $('.back-to-top').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({ scrollTop: 0 }, 500);
    });
    
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
    $.ajax({
        url: baseUrl + '/public/ajax/cart.php',
        method: 'POST',
        data: { action: 'count' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var count = response.count;
                if (count > 0) {
                    $('.cart-badge').text(count).show();
                } else {
                    $('.cart-badge').hide();
                }
            }
        }
    });
}

/**
 * Show notification
 */
function showNotification(type, message) {
    var alertClass = 'alert-' + type;
    var notification = $('<div>')
        .addClass('alert ' + alertClass)
        .text(message)
        .hide();
    
    $('body').prepend(notification);
    notification.slideDown();
    
    setTimeout(function() {
        notification.slideUp(function() {
            $(this).remove();
        });
    }, 5000);
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
