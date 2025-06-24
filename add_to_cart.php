<?php
require_once 'includes/include.php';
require_once 'includes/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

    // Lấy sản phẩm gốc
    $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if ($product) {
        // Kiểm tra khuyến mãi hiện tại nếu có
        $now = date('Y-m-d');
        $promoStmt = $pdo->prepare("
            SELECT discount_percent FROM promotions 
            WHERE (product_id = :pid OR apply_all = 1) 
              AND start_date <= :now AND end_date >= :now 
            ORDER BY product_id DESC, discount_percent DESC 
            LIMIT 1
        ");
        $promoStmt->execute([
            ':pid' => $product_id,
            ':now' => $now
        ]);
        $promotion = $promoStmt->fetch();

        $finalPrice = $product['price'];
        if ($promotion) {
            $discount = (float) $promotion['discount_percent'];
            $finalPrice = $finalPrice * (1 - $discount / 100);
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => round($finalPrice),
                'quantity' => $quantity,
                'image' => $product['image'] ?? 'no-image.png'
            ];
        }
    }
}

header("Location: cart.php");
exit;
