<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - صفحه یافت نشد</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        .error-container {
            text-align: center;
            color: white;
            max-width: 600px;
        }
        .error-code {
            font-size: 10rem;
            font-weight: bold;
            line-height: 1;
            margin-bottom: 20px;
            text-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        .error-message {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .error-description {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        .error-icon {
            font-size: 8rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        .back-btn {
            display: inline-block;
            padding: 15px 40px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        .back-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-container">
            <div class="error-icon">
                <i class="fas fa-search"></i>
            </div>
            <div class="error-code">404</div>
            <h1 class="error-message">صفحه یافت نشد!</h1>
            <p class="error-description">
                متأسفیم، صفحه‌ای که به دنبال آن هستید وجود ندارد یا منتقل شده است.
            </p>
            <a href="/HouseholdAppliances/public/index.php" class="back-btn">
                <i class="fas fa-home"></i> بازگشت به صفحه اصلی
            </a>
        </div>
    </div>
</body>
</html>
