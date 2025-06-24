<?php
require_once '../includes/include.php'; // session_start()
require_once '../includes/db_config.php';

// Kiểm tra có id truyền vào không
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID sản phẩm không hợp lệ.";
    header("Location: products.php");
    exit;
}

$product_id = (int)$_GET['id'];

// Lấy thông tin sản phẩm để xóa ảnh nếu cần
$stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['error'] = "Không tìm thấy sản phẩm.";
    header("Location: products.php");
    exit;
}

// Xóa ảnh nếu tồn tại
if (!empty($product['image'])) {
    $image_path = '../uploads/' . $product['image'];
    if (file_exists($image_path)) {
        unlink($image_path); // Xóa file ảnh
    }
}

// Thực hiện xóa sản phẩm
$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
$stmt->execute([$product_id]);

$_SESSION['success'] = "Đã xóa sản phẩm thành công.";
header("Location: products.php");
exit;
