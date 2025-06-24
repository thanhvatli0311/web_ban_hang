<?php
require_once __DIR__ . '/../includes/include.php';
require_once __DIR__ . '/../includes/db_config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int) $_POST['order_id'];
    $new_status = $_POST['status'];

    $allowed = ['đang chờ', 'đang xử lý', 'đã hoàn thành', 'đã hủy'];
    if (!in_array($new_status, $allowed)) {
        die('Trạng thái không hợp lệ.');
    }

    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
    header("Location: orders.php");
    exit;
}