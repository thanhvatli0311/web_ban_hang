<?php
require_once 'includes/include.php';
require_once 'includes/db_config.php';
include 'includes/header.php';

if (empty($_SESSION['cart'])) {
    echo '<div class="container mt-5 alert alert-warning">❗ Giỏ hàng của bạn đang trống. <a href="index.php">Quay lại mua sắm</a></div>';
    include 'includes/footer.php';
    exit;
}

if (!isset($_SESSION['user'])) {
    echo '<div class="container mt-5 alert alert-warning">⚠ Vui lòng đăng nhập để đặt hàng. <a href="admin/login.php">Đăng nhập</a></div>';
    include 'includes/footer.php';
    exit;
}
// Lấy dữ liệu từ form
$user = $_SESSION['user'];
$user_id = $user['id'];
$total_amount = 0;

$shipping_address = $_POST['shipping_address'] ?? '';
$shipping_phone = $_POST['shipping_phone'] ?? '';


foreach ($_SESSION['cart'] as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

try {
    $pdo->beginTransaction();

    // Lưu vào bảng orders
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, shipping_phone, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$user_id, $total_amount, $shipping_address, $shipping_phone]);


    $order_id = $pdo->lastInsertId();

    // Lưu chi tiết đơn hàng vào bảng order_items + giảm tồn kho
    $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt_stock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

    foreach ($_SESSION['cart'] as $product_id => $item) {
        $stmt_item->execute([$order_id, $product_id, $item['quantity'], $item['price']]);
        $stmt_stock->execute([$item['quantity'], $product_id]);
    }

    $pdo->commit();

    // Gửi email xác nhận đơn hàng
    $to = $user['email'];
    $subject = "Xác nhận đơn hàng #" . $order_id;
    $headers = "Content-Type: text/html; charset=UTF-8";

    $message = "<h2>✅ Cảm ơn bạn đã đặt hàng tại shop 14/6</h2>";
    $message .= "<p>Mã đơn hàng: <strong>$order_id</strong></p>";
    $message .= "<p>Ngày đặt: " . date('d/m/Y H:i') . "</p>";
    $message .= "<table border='1' cellpadding='8' cellspacing='0'>";
    $message .= "<thead><tr><th>Ảnh</th><th>Sản phẩm</th><th>Giá</th><th>Số lượng</th><th>Tổng</th></tr></thead><tbody>";

    foreach ($_SESSION['cart'] as $item) {
        $subtotal = $item['price'] * $item['quantity'];
        $img = $item['image'] ?? 'no-image.png';
        $message .= "<tr>
            <td><img src='https://yourdomain.com/$img' width='60'></td>
            <td>{$item['name']}</td>
            <td>" . number_format($item['price'], 0, ',', '.') . "₫</td>
            <td>{$item['quantity']}</td>
            <td>" . number_format($subtotal, 0, ',', '.') . "₫</td>
        </tr>";
    }



    unset($_SESSION['cart']);
    echo '<div class="container mt-5 alert alert-success">✅ Đặt hàng thành công! Mã đơn hàng của bạn là #' . $order_id . '</div>';
} catch (Exception $e) {
    $pdo->rollBack();
    echo '<div class="container mt-5 alert alert-danger">❌ Đã xảy ra lỗi khi xử lý đơn hàng: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

include 'includes/footer.php';
?>
