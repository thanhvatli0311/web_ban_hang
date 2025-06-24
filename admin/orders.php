<?php
require_once __DIR__ . '/../includes/include.php';
require_once __DIR__ . '/../includes/db_config.php';

// Lấy danh sách trạng thái để lọc
$allowed_statuses = ['đang chờ', 'đang xử lý', 'đã hoàn thành', 'đã hủy'];
$status_filter = $_GET['status'] ?? null;
if (!in_array($status_filter, $allowed_statuses)) {
    $status_filter = null;
}

// Truy vấn danh sách đơn hàng có JOIN với users để lấy tên khách hàng
$sql = "SELECT o.id, u.name AS customer_name, o.total_amount, o.status, o.created_at 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE (:status IS NULL OR o.status = :status)
        ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['status' => $status_filter]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4">📦 Danh sách đơn hàng</h2>
    
    <a href="dashboard.php" class="btn btn-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Trở về trang quản trị
    </a>

    <!-- Bộ lọc trạng thái -->
    <form method="get" class="mb-3">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <label for="status" class="form-label mb-0">Lọc theo trạng thái:</label>
            </div>
            <div class="col-auto">
                <select name="status" id="status" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($allowed_statuses as $status): ?>
                        <option value="<?= $status ?>" <?= $status_filter === $status ? 'selected' : '' ?>>
                            <?= ucfirst($status) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Lọc
                </button>
            </div>
        </div>
    </form>

    <!-- Bảng danh sách đơn -->
    <table id="ordersTable" class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Khách hàng</th>
                <th class="text-end">Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= $order['id'] ?></td>
                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                <td class="text-end"><?= number_format($order['total_amount'], 0, ',', '.') ?>₫</td>
                <td>
                    <form method="post" action="update_order_status.php" class="d-flex">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <select name="status" class="form-select form-select-sm me-1 text-capitalize 
                            <?= $order['status'] === 'đã hoàn thành' ? 'text-success' : 
                                ($order['status'] === 'đã hủy' ? 'text-danger' : 
                                ($order['status'] === 'đang xử lý' ? 'text-warning' : 'text-secondary')) ?>">
                            <?php foreach ($allowed_statuses as $status): ?>
                                <option value="<?= $status ?>" <?= $order['status'] === $status ? 'selected' : '' ?>>
                                    <?= ucfirst($status) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="bi bi-check-circle"></i>
                        </button>
                    </form>
                </td>
                <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                <td>
                    <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-info me-1">
                        <i class="bi bi-eye"></i> Chi tiết
                    </a>
                    <form method="post" action="delete_order.php" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đơn hàng này?');" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="bi bi-trash"></i> Xóa
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function () {
        $('#ordersTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json"
            }
        });
    });
</script>
</body>
</html>
