<?php
require_once 'includes/include.php'; // Khởi tạo session

// Hủy session
session_unset();
session_destroy();

// Quay lại trang đăng nhập
header("Location: ../login.php");
exit;
