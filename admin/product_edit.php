<?php
require_once __DIR__ . '/../includes/include.php';
require_once __DIR__ . '/../includes/db_config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Thiếu ID sản phẩm.");
}
$product_id = (int) $_GET['id'];

$errors = [];
$success = false;

// Lấy thông tin sản phẩm
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    die("Không tìm thấy sản phẩm.");
}

// Lấy danh sách danh mục
$categories = $pdo->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Lấy ảnh phụ
$stmt_imgs = $pdo->prepare("SELECT id, image_path FROM product_images WHERE product_id = ?");
$stmt_imgs->execute([$product_id]);
$extra_images = $stmt_imgs->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);
    $category_id = $_POST['category_id'];
    $stock = isset($_POST['stock']) ? (int) $_POST['stock'] : 0;

    if (empty($name) || empty($price) || empty($description)) {
        $errors[] = "Vui lòng điền đầy đủ thông tin.";
    } elseif (!is_numeric($price) || $price <= 0) {
        $errors[] = "Giá phải là số dương.";
    }

    if (!is_numeric($stock) || $stock < 0) {
        $errors[] = "Số lượng tồn kho phải là số nguyên không âm.";
    }

    // Xử lý ảnh chính nếu có thay đổi
    $mainImagePath = $product['image'];
    if ($_FILES['main_image']['error'] === 0) {
        $uploadsDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0777, true);
        $filename = uniqid() . '_' . basename($_FILES['main_image']['name']);
        $mainImagePath = 'uploads/' . $filename;
        move_uploaded_file($_FILES['main_image']['tmp_name'], $uploadsDir . $filename);
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, description = ?, category_id = ?, image = ?, stock = ? WHERE id = ?");
        $stmt->execute([$name, $price, $description, $category_id, $mainImagePath, $stock, $product_id]);

        // Xử lý thêm ảnh phụ mới
        if (!empty($_FILES['extra_images']['name'][0])) {
            foreach ($_FILES['extra_images']['tmp_name'] as $index => $tmpName) {
                if ($_FILES['extra_images']['error'][$index] === 0) {
                    $filename = uniqid() . '_' . basename($_FILES['extra_images']['name'][$index]);
                    $imagePath = 'uploads/' . $filename;
                    move_uploaded_file($tmpName, __DIR__ . '/../' . $imagePath);

                    $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
                    $stmt->execute([$product_id, $imagePath]);
                }
            }
        }

        $success = true;

        // Refresh lại dữ liệu mới
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        $stmt_imgs = $pdo->prepare("SELECT id, image_path FROM product_images WHERE product_id = ?");
        $stmt_imgs->execute([$product_id]);
        $extra_images = $stmt_imgs->fetchAll();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cập Nhật Sản Phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2>✏️ Cập Nhật Sản Phẩm</h2>
    <a href="products.php" class="btn btn-secondary btn-sm mb-3">⬅ Quay lại danh sách</a>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php elseif ($success): ?>
        <div class="alert alert-success">✅ Cập nhật thành công!</div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Tên sản phẩm</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? $product['name']) ?>">
        </div>
        <div class="mb-3">
            <label>Giá</label>
            <input type="number" name="price" class="form-control" value="<?= htmlspecialchars($_POST['price'] ?? $product['price']) ?>">
        </div>
        <div class="mb-3">
            <label>Số lượng tồn kho</label>
            <input type="number" name="stock" class="form-control" min="0" value="<?= htmlspecialchars($_POST['stock'] ?? $product['stock']) ?>">
        </div>
        <div class="mb-3">
            <label>Mô tả</label>
            <textarea name="description" class="form-control"><?= htmlspecialchars($_POST['description'] ?? $product['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label>Danh mục</label>
            <select name="category_id" class="form-control">
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Ảnh chính -->
        <div class="mb-3">
            <label>Ảnh chính hiện tại:</label><br>
            <img src="../<?= htmlspecialchars($product['image']) ?>" style="max-height: 120px;" class="mb-2 border rounded">
            <input type="file" name="main_image" class="form-control mt-2">
        </div>

        <!-- Ảnh phụ -->
        <div class="mb-3">
            <label>Ảnh phụ hiện tại:</label><br>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($extra_images as $img): ?>
                    <img src="../<?= htmlspecialchars($img['image_path']) ?>" style="height: 80px;" class="rounded border">
                <?php endforeach; ?>
            </div>
            <label class="mt-2">Thêm ảnh phụ mới (có thể chọn nhiều):</label>
            <input type="file" name="extra_images[]" class="form-control" multiple>
        </div>

        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
    </form>
</div>
</body>
</html>
