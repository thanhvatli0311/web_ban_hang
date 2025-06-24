<?php
require_once __DIR__ . '/../includes/include.php';
require_once __DIR__ . '/../includes/db_config.php';

// Xử lý tìm kiếm
$search = $_GET['search'] ?? '';
$searchParam = "%$search%";

// Phân trang
$limit = 10;
$page = isset($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Đếm tổng số sản phẩm
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE name LIKE ?");
$countStmt->execute([$searchParam]);
$totalRows = $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Lấy danh sách sản phẩm có phân trang và tìm kiếm
$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.name LIKE ? 
    ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute([$searchParam]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Sản Phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2>📦 Danh Sách Sản Phẩm</h2>
    <a href="product_add.php" class="btn btn-success mb-3">➕ Thêm Sản Phẩm</a>
    <a href="dashboard.php" class="btn btn-secondary mb-3">🏠 Trở lại Trang Quản Trị</a>

    <form class="mb-3" method="get">
        <input type="text" name="search" class="form-control" placeholder="Tìm theo tên sản phẩm" value="<?= htmlspecialchars($search) ?>">
    </form>

    <table id="productsTable" class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Ảnh</th>
                <th>Tên</th>
                <th>Giá</th>
                <th>Danh mục</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $i => $p): ?>
                <tr>
                    <td><?= $i + 1 + $offset ?></td>
                    <td><img src="../<?= $p['image'] ?>" height="50"></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= number_format($p['price'], 0, ',', '.') ?>₫</td>
                    <td><?= htmlspecialchars($p['category_name']) ?></td>
                    <td><?= $p['created_at'] ?></td>
                    <td>
                        <a href="product_edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">✏️ Sửa</a>
                        <a href="product_delete.php?id=<?= $p['id'] ?>" onclick="return confirm('Xác nhận xoá sản phẩm?')" class="btn btn-sm btn-danger">🗑️ Xoá</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Phân trang -->
    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
    const table = new DataTable('#productsTable', {
        paging: false,
        searching: false,
        info: false
    });
</script>
</body>
</html>