<?php
require_once __DIR__ . '/../includes/include.php';
require_once __DIR__ . '/../includes/db_config.php';



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];

    // Xóa các item trong order_items trước
    $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);

    // Sau đó xóa đơn hàng
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);

    $_SESSION['success'] = "Đã xóa đơn hàng #$order_id thành công.";
}

header("Location: orders.php");
exit;
