<?php
require_once __DIR__ . '/../includes/include.php';
require_once __DIR__ . '/../includes/db_config.php';

// Thêm khuyến mãi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $discount_percent = $_POST['discount_percent'] ?? null;
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $target = $_POST['target'];

    // Xác định loại áp dụng
    $product_id = null;
    $category_id = null;
    $apply_all = 0;

    if ($target === 'product') {
        $product_id = $_POST['product_id'];
    } elseif ($target === 'category') {
        $category_id = $_POST['category_id'];
    } elseif ($target === 'all') {
        $apply_all = 1;
    }

    $stmt = $pdo->prepare("INSERT INTO promotions (product_id, category_id, apply_all, discount_percent, start_date, end_date)
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$product_id, $category_id, $apply_all, $discount_percent, $start_date, $end_date]);

    header("Location: promotions.php");
    exit;
}

// Xóa khuyến mãi
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare("DELETE FROM promotions WHERE id = ?")->execute([$id]);
    header("Location: promotions.php");
    exit;
}

// Truy vấn dữ liệu
$products = $pdo->query("SELECT p.*, c.name AS category_name 
                         FROM products p 
                         LEFT JOIN categories c ON p.category_id = c.id 
                         ORDER BY c.name, p.name")->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$grouped_products = [];
foreach ($products as $product) {
    $cat = $product['category_name'] ?? 'Không phân loại';
    $grouped_products[$cat][] = $product;
}

$promotions = $pdo->query("
    SELECT pr.*, 
           p.name AS product_name, 
           c.name AS category_name
    FROM promotions pr
    LEFT JOIN products p ON pr.product_id = p.id
    LEFT JOIN categories c ON pr.category_id = c.id
    ORDER BY pr.start_date DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý khuyến mãi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <h2>🎁 Quản lý Khuyến mãi</h2>
        <a href="dashboard.php" class="btn btn-secondary mb-3">🏠 Trở lại Trang Quản Trị</a>

    <!-- Form Thêm -->
    <form method="POST" class="row g-3 mb-4">
        <input type="hidden" name="add" value="1">

        <!-- Chọn phạm vi áp dụng -->
        <div class="col-md-4">
            <label class="form-label">Áp dụng cho</label>
            <select name="target" class="form-select" required onchange="handleTargetChange(this.value)">
                <option value="">-- Chọn --</option>
                <option value="all">Tất cả sản phẩm</option>
                <option value="category">Theo danh mục</option>
                <option value="product">Sản phẩm cụ thể</option>
            </select>
        </div>

        <div class="col-md-4" id="productSelect" style="display:none;">
            <label class="form-label">Chọn sản phẩm</label>
            <select name="product_id" class="form-select">
                <?php foreach ($grouped_products as $cat => $plist): ?>
                    <optgroup label="<?= htmlspecialchars($cat) ?>">
                        <?php foreach ($plist as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-4" id="categorySelect" style="display:none;">
            <label class="form-label">Chọn danh mục</label>
            <select name="category_id" class="form-select">
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Giảm (%)</label>
            <input type="number" name="discount_percent" class="form-control" min="0" max="100" required>
        </div>

        <div class="col-md-3">
            <label class="form-label">Ngày bắt đầu</label>
            <input type="date" name="start_date" class="form-control" required>
        </div>

        <div class="col-md-3">
            <label class="form-label">Ngày kết thúc</label>
            <input type="date" name="end_date" class="form-control" required>
        </div>

        <div class="col-12">
            <button class="btn btn-success">➕ Thêm Khuyến mãi</button>
        </div>
    </form>

    <!-- Danh sách khuyến mãi -->
    <table class="table table-bordered table-striped">
        <thead class="table-light">
        <tr>
            <th>Áp dụng</th>
            <th>Giảm (%)</th>
            <th>Bắt đầu</th>
            <th>Kết thúc</th>
            <th>Thao tác</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($promotions as $promo): ?>
            <tr>
                <td>
                    <?php
                        if ($promo['apply_all']) {
                            echo '<span class="text-danger">Tất cả sản phẩm</span>';
                        } elseif ($promo['category_id']) {
                            echo 'Danh mục: <strong>' . htmlspecialchars($promo['category_name']) . '</strong>';
                        } else {
                            echo 'Sản phẩm: <strong>' . htmlspecialchars($promo['product_name']) . '</strong>';
                        }
                    ?>
                </td>
                <td><?= $promo['discount_percent'] ?>%</td>
                <td><?= $promo['start_date'] ?></td>
                <td><?= $promo['end_date'] ?></td>
                <td>
                    <a href="?delete=<?= $promo['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa?')">🗑️ Xóa</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function handleTargetChange(value) {
    document.getElementById('productSelect').style.display = (value === 'product') ? 'block' : 'none';
    document.getElementById('categorySelect').style.display = (value === 'category') ? 'block' : 'none';
}
</script>
</body>
</html>
