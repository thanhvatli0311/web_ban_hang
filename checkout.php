<?php
require_once 'includes/include.php';
require_once 'includes/db_config.php';
include 'includes/header.php';

// Lấy thông tin người dùng nếu đã đăng nhập
$user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT name, phone, address FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="container py-5">
    <h3 class="mb-4">🧾 Thông tin giao hàng</h3>

    <?php if (!empty($_SESSION['cart'])): ?>
        <form action="submit_order.php" method="post">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Họ và tên</label>
                    <input type="text" name="name" id="name" class="form-control"
                        value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                </div>
            <div class="mb-3">
                <label for="shipping_phone" class="form-label">Số điện thoại</label>
                <input type="tel" name="shipping_phone" id="shipping_phone" class="form-control" required>
            </div>
            </div>
            <div class="mb-3">
                <label for="shipping_address" class="form-label">Địa chỉ giao hàng</label>
                <input type="text" name="shipping_address" id="shipping_address" class="form-control" required>
            </div>

            <div class="d-flex justify-content-between">
                <a href="cart.php" class="btn btn-secondary">⬅ Quay lại giỏ hàng</a>
                <button type="submit" class="btn btn-success">✅ Đặt hàng</button>
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-warning">❌ Giỏ hàng của bạn đang trống. <a href="index.php">Mua sắm ngay</a></div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
