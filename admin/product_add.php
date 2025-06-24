<?php
require_once __DIR__ . '/../includes/include.php';
require_once __DIR__ . '/../includes/db_config.php';

$errors = [];
$success = false;

// Lấy danh sách danh mục
$categories = $pdo->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $stock = isset($_POST['stock']) ? (int) $_POST['stock'] : 0;

    $description = trim($_POST['description']);
    $category_id = $_POST['category_id'];

    // Kiểm tra dữ liệu
    if (empty($name) || empty($price) || empty($description)) {
        $errors[] = "Vui lòng điền đầy đủ thông tin.";
    } elseif (!is_numeric($price) || $price <= 0) {
        $errors[] = "Giá phải là số dương.";
    }
    if (!is_numeric($stock) || $stock < 0) {
    $errors[] = "Số lượng tồn kho phải là số nguyên không âm.";
}


    // Xử lý ảnh chính
    $mainImagePath = '';
    if ($_FILES['main_image']['error'] === 0) {
        $uploadsDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }
        $filename = uniqid() . '_' . basename($_FILES['main_image']['name']);
        $mainImagePath = 'uploads/' . $filename;
        move_uploaded_file($_FILES['main_image']['tmp_name'], $uploadsDir . $filename);
    } else {
        $errors[] = "Vui lòng chọn ảnh sản phẩm.";
    }

    // Thêm sản phẩm nếu không có lỗi
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, description, category_id, image, stock) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $price, $description, $category_id, $mainImagePath, $stock]);

        $productId = $pdo->lastInsertId();

        // Xử lý ảnh phụ
        if (!empty($_FILES['extra_images']['name'][0])) {
            foreach ($_FILES['extra_images']['tmp_name'] as $index => $tmpName) {
                if ($_FILES['extra_images']['error'][$index] === 0) {
                    $filename = uniqid() . '_' . basename($_FILES['extra_images']['name'][$index]);
                    $imagePath = 'uploads/' . $filename;
                    move_uploaded_file($tmpName, $uploadsDir . $filename);

                    $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
                    $stmt->execute([$productId, $imagePath]);
                }
            }
        }

        $success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Sản Phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2>➕ Thêm Sản Phẩm</h2>
    <a href="products.php" class="btn btn-secondary btn-sm mb-3">⬅ Quay lại quản lý sản phẩm</a>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php elseif ($success): ?>
        <div class="alert alert-success">✅ Thêm sản phẩm thành công!</div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Tên sản phẩm</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label>Giá</label>
            <input type="number" name="price" class="form-control" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label>Số lượng tồn kho</label>
            <input type="number" name="stock" class="form-control" min="0" value="<?= htmlspecialchars($_POST['stock'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label>Mô tả</label>
            <textarea name="description" class="form-control"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label>Danh mục</label>
            <select name="category_id" class="form-control">
                <option value="">-- Chọn danh mục --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Hình ảnh chính</label>
            <input type="file" name="main_image" class="form-control">
        </div>
        <div class="mb-3">
            <label>Hình ảnh phụ (có thể chọn nhiều)</label>
            <input type="file" name="extra_images[]" class="form-control" multiple>
        </div>
        <button type="submit" class="btn btn-primary">Thêm sản phẩm</button>
    </form>
</div>
</body>
</html>
