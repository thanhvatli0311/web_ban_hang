<?php
require_once __DIR__ . '/../includes/include.php';
require_once __DIR__ . '/../includes/db_config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Thiếu ID đơn hàng.');
}

$order_id = (int) $_GET['id'];

// Lấy thông tin đơn hàng + khách hàng
$stmt = $pdo->prepare("SELECT o.*, u.name AS customer_name 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die('Không tìm thấy đơn hàng.');
}

// Lấy danh sách sản phẩm trong đơn hàng
$stmt = $pdo->prepare("SELECT oi.*, p.name AS product_name, p.image 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng #<?= $order_id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .btn, a, nav, footer {
                display: none !important;
            }
        }
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">🧾 Chi tiết đơn hàng #<?= $order_id ?></h2>
        <div>
            <a href="orders.php" class="btn btn-outline-secondary me-2">⬅ Quay lại</a>
            <button onclick="window.print()" class="btn btn-outline-success">
                🖨 In hóa đơn
            </button>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white fw-bold">📦 Thông tin giao hàng</div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-6"><strong>👤 Khách hàng:</strong> <?= htmlspecialchars($order['customer_name']) ?></div>
                <div class="col-md-6"><strong>📞 SĐT:</strong> <?= htmlspecialchars($order['shipping_phone'] ?? '[Chưa có dữ liệu]') ?></div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12"><strong>📍 Địa chỉ:</strong> <?= htmlspecialchars($order['shipping_address'] ?? '[Chưa có dữ liệu]') ?></div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6"><strong>📅 Ngày tạo:</strong> <?= $order['created_at'] ?></div>
                <div class="col-md-6"><strong>🚚 Trạng thái:</strong> <?= ucfirst($order['status']) ?></div>
            </div>
            <div class="row mt-3">
                <div class="col text-end">
                    <strong class="fs-5">💰 Tổng tiền:</strong>
                    <span class="text-danger fw-bold fs-5"><?= number_format($order['total_amount'], 0, ',', '.') ?>₫</span>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mb-3">🛒 Sản phẩm đã đặt:</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle shadow-sm">
            <thead class="table-light text-center">
            <tr>
                <th>Hình</th>
                <th>Tên sản phẩm</th>
                <th>Đơn giá</th>
                <th>Số lượng</th>
                <th>Tạm tính</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td class="text-center">
                        <img src="../<?= htmlspecialchars($item['image'] ?? 'assets/images/no-image.png') ?>" class="product-img" alt="">
                    </td>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td class="text-end"><?= number_format($item['price'], 0, ',', '.') ?>₫</td>
                    <td class="text-center"><?= $item['quantity'] ?></td>
                    <td class="text-end"><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>₫</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
