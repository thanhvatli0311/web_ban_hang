<?php
require_once __DIR__ . '/../includes/include.php';
require_once __DIR__ . '/../includes/db_config.php';

// Xử lý thêm danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
        $stmt->execute(['name' => $name]);
        header("Location: categories.php");
        exit;
    }
}

// Xử lý xóa danh mục
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Kiểm tra xem có sản phẩm nào thuộc danh mục này không
    $check = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = :id");
    $check->execute(['id' => $id]);
    if ($check->fetchColumn() == 0) {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
    header("Location: categories.php");
    exit;
}

// Xử lý sửa danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $pdo->prepare("UPDATE categories SET name = :name WHERE id = :id");
        $stmt->execute(['name' => $name, 'id' => $id]);
        header("Location: categories.php");
        exit;
    }
}

// Lấy danh sách danh mục
$categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý danh mục</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
</head>
<body class="bg-light">
<div class="container py-4">
    <h3 class="mb-4">📂 Quản lý danh mục</h3>
    <a href="dashboard.php" class="btn btn-secondary mb-3">⬅ Trở về trang quản trị</a>

    <!-- Form Thêm danh mục -->
    <form method="post" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="name" class="form-control" placeholder="Tên danh mục" required>
        </div>
        <div class="col-auto">
            <button type="submit" name="add_category" class="btn btn-primary">➕ Thêm danh mục</button>
        </div>
    </form>

    <!-- Bảng danh sách danh mục -->
    <table id="categoryTable" class="table table-bordered table-hover">
        <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Tên danh mục</th>
            <th>Thao tác</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?= $cat['id'] ?></td>
                <td><?= htmlspecialchars($cat['name']) ?></td>
                <td>
                    <form method="post" class="d-inline-block">
                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                        <input type="text" name="name" class="form-control d-inline-block w-auto" value="<?= htmlspecialchars($cat['name']) ?>" required>
                        <button type="submit" name="edit_category" class="btn btn-sm btn-warning">✏️ Sửa</button>
                    </form>
                    <a href="?delete=<?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa?')">🗑️ Xóa</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#categoryTable').DataTable({
            language: {
                search: "🔍 Tìm kiếm:",
                lengthMenu: "Hiển thị _MENU_ danh mục",
                info: "Hiển thị _START_ đến _END_ trong tổng _TOTAL_ danh mục",
                paginate: { previous: "Trước", next: "Sau" },
                zeroRecords: "Không tìm thấy danh mục phù hợp"
            }
        });
    });
</script>
</body>
</html>
