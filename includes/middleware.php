<?php
require_once __DIR__ . '/include.php';

// Kiểm tra đăng nhập
function require_login() {
    if (empty($_SESSION['user'])) {
        header("Location: /auth/login.php");
        exit;
    }
}

// Chỉ cho admin
function require_admin() {
    require_login();
    if ($_SESSION['user']['role'] !== 'admin') {
        die('⛔ Bạn không có quyền truy cập khu vực này.');
    }
}

// Chỉ cho khách hàng
function require_customer() {
    require_login();
    if ($_SESSION['user']['role'] !== 'customer') {
        die('⛔ Chỉ khách hàng mới được truy cập.');
    }
}
