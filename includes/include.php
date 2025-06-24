<?php
// Kiểm tra nếu session chưa được khởi tạo thì mới gọi session_start()
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Thêm các cấu hình chung khác nếu cần
?>
