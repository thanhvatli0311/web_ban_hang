<?php
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/db_config.php';

require_admin(); // Chỉ admin được vào

// Lấy thống kê đơn hàng
$orderStats = [
    'đang chờ' => 0,
    'đang xử lý' => 0,
    'đã hoàn thành' => 0,
    'đã hủy' => 0
];

$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
foreach ($stmt as $row) {
    $orderStats[$row['status']] = $row['count'];
}

// Tổng số sản phẩm đang hoạt động
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1");
    $totalProducts = $stmt->fetchColumn();
} catch (PDOException $e) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $totalProducts = $stmt->fetchColumn();
}

// Doanh thu 12 tháng gần nhất
$stmt = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, SUM(total_amount) as revenue
                      FROM orders WHERE status = 'completed'
                      GROUP BY month ORDER BY month DESC LIMIT 12");
$revenueData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5 đơn hàng mới nhất
$stmt = $pdo->query("SELECT id, total_amount, status, created_at FROM orders ORDER BY created_at DESC LIMIT 5");
$latestOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang quản trị</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary"><i class="bi bi-speedometer2"></i> Bảng điều khiển quản trị</h2>
        <a href="../logout.php" class="btn btn-outline-danger"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
    </div>

    <!-- Menu quản lý -->
    <div class="mb-4 d-flex flex-wrap gap-2">
        <a href="products.php" class="btn btn-outline-primary"><i class="bi bi-box-seam"></i> Sản phẩm</a>
        <a href="categories.php" class="btn btn-outline-secondary"><i class="bi bi-tags"></i> Danh mục</a>
        <a href="orders.php" class="btn btn-outline-success"><i class="bi bi-truck"></i> Đơn hàng</a>
        <a href="promotions.php" class="btn btn-outline-info"><i class="bi bi-gift"></i> Khuyến mãi</a>
        <a href="banners.php" class="btn btn-outline-warning"><i class="bi bi-image"></i> Banner</a>
        <a href="users.php" class="btn btn-outline-dark"><i class="bi bi-people"></i> Khách hàng</a>
    </div>

    <!-- Thống kê đơn hàng -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card text-bg-warning shadow-sm">
                <div class="card-body text-center">
                    <h6 class="card-title">🕒 Chờ duyệt</h6>
                    <p class="display-6"><?= $orderStats['đang chờ'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-bg-info shadow-sm">
                <div class="card-body text-center">
                    <h6 class="card-title">🚚 Đang giao</h6>
                    <p class="display-6"><?= $orderStats['đang xử lý'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-bg-success shadow-sm">
                <div class="card-body text-center">
                    <h6 class="card-title">✅ Hoàn thành</h6>
                    <p class="display-6"><?= $orderStats['đã hoàn thành'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-bg-danger shadow-sm">
                <div class="card-body text-center">
                    <h6 class="card-title">❌ Bị hủy</h6>
                    <p class="display-6"><?= $orderStats['đã hủy'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tổng sản phẩm -->
    <div class="alert alert-secondary text-center fw-bold mb-5">
        🛍️ Tổng số sản phẩm đang hoạt động: <span class="text-primary"><?= $totalProducts ?></span>
    </div>

    <!-- Biểu đồ doanh thu -->
    <div class="mb-5">
        <h4 class="mb-3"><i class="bi bi-graph-up-arrow"></i> Doanh thu 12 tháng gần nhất</h4>
        <canvas id="revenueChart" height="100"></canvas>
    </div>

    <!-- Đơn hàng mới nhất -->
    <div class="mb-5">
        <h4 class="mb-3"><i class="bi bi-clock-history"></i> 5 đơn hàng mới nhất</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>ID</th>
                        <th>Số tiền</th>
                        <th>Trạng thái</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($latestOrders as $order): ?>
                    <tr>
                        <td><?= $order['id'] ?></td>
                        <td><?= number_format($order['total_amount'], 0, ',', '.') ?>₫</td>
                        <td><?= ucfirst($order['status']) ?></td>
                        <td><?= $order['created_at'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($revenueData, 'month')) ?>,
        datasets: [{
            label: 'Doanh thu (VND)',
            data: <?= json_encode(array_map('intval', array_column($revenueData, 'revenue'))) ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
