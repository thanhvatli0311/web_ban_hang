<?php
require_once 'includes/include.php';
require_once 'includes/db_config.php';
include 'includes/header.php';
?>

<div class="container py-5">
    <h3 class="mb-4 text-center text-primary fw-bold">🛒 Giỏ Hàng Của Bạn</h3>

    <?php if (!empty($_SESSION['cart'])): ?>
        <form action="checkout.php" method="post">
            <div class="table-responsive">
                <table class="table table-bordered align-middle shadow-sm">
                    <thead class="table-light text-center">
                        <tr>
                            <th><input type="checkbox" id="select_all"></th>
                            <th>Ảnh</th>
                            <th>Tên Sản Phẩm</th>
                            <th>Đơn Giá</th>
                            <th>Số Lượng</th>
                            <th>Tổng</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        foreach ($_SESSION['cart'] as $id => $item):
                            $subtotal = $item['price'] * $item['quantity'];
                            $total += $subtotal;

                            $img_path = $item['image'] ?? 'no-image.png';
                            if (!file_exists($img_path)) $img_path = 'no-image.png';
                        ?>
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" name="selected_products[]" value="<?= $id ?>" class="select_item">
                            </td>
                            <td class="text-center">
                                <img src="<?= htmlspecialchars($img_path) ?>" alt="Ảnh" class="img-thumbnail rounded"
                                     style="width: 70px; height: 70px; object-fit: cover;">
                            </td>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td class="text-end"><?= number_format($item['price'], 0, ',', '.') ?>₫</td>
                            <td class="text-center">
                                <input type="number" name="quantity[<?= $id ?>]" value="<?= $item['quantity'] ?>"
                                       min="1" class="form-control text-center mx-auto" style="max-width: 80px;">
                            </td>
                            <td class="text-end"><?= number_format($subtotal, 0, ',', '.') ?>₫</td>
                            <td class="text-center">
                                <a href="remove_item.php?id=<?= $id ?>" class="btn btn-outline-danger btn-sm" title="Xóa sản phẩm">✖</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="fw-bold">
                            <td colspan="5" class="text-end">Tổng cộng (tạm tính):</td>
                            <td class="text-end text-danger"><?= number_format($total, 0, ',', '.') ?>₫</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between mt-4 flex-wrap gap-2">
                <a href="index.php" class="btn btn-outline-secondary">⬅ Tiếp tục mua sắm</a>
                <div class="d-flex gap-2">
                    <button type="submit" formaction="update_cart.php" class="btn btn-outline-primary">🔄 Cập nhật</button>
                    <button type="submit" class="btn btn-success">💳 Thanh toán mục đã chọn</button>
                </div>
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-info text-center">
            Giỏ hàng của bạn đang trống.<br>
            <a href="index.php" class="btn btn-sm btn-outline-primary mt-2">⬅ Quay lại mua sắm</a>
        </div>
    <?php endif; ?>
</div>

<script>
    // Chọn / bỏ chọn tất cả
    document.getElementById('select_all').addEventListener('change', function () {
        const checkboxes = document.querySelectorAll('.select_item');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>

<?php include 'includes/footer.php'; ?>
