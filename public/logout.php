<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/User.php';

if (isLoggedIn()) {
    $userObj = new User();
    $userObj->logout();
}

setFlashMessage('success', 'شما با موفقیت خارج شدید');
redirect(SITE_URL . '/public/index.php');
