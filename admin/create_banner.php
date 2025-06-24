<?php
require_once __DIR__ . '/../includes/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $link = $_POST['link'] ?? '';
    $position = $_POST['position'] ?? 'homepage';
    $image = $_FILES['image']['name'] ?? '';

    if ($image) {
        $uploadPath = '../assets/banners/' . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);

        $stmt = $pdo->prepare("INSERT INTO banners (title, image, link, position) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $image, $link, $position]);
        header("Location: banners.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Banner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <h2>Thêm Banner</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Tiêu đề:</label>
            <input type="text" name="title" class="form-control">
        </div>
        <div class="mb-3">
            <label>Hình ảnh:</label>
            <input type="file" name="image" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Liên kết:</label>
            <input type="text" name="link" class="form-control">
        </div>
        <div class="mb-3">
            <label>Vị trí:</label>
            <select name="position" class="form-control">
                <option value="homepage">Trang chủ</option>
                <option value="sidebar">Thanh bên</option>
                <option value="footer">Chân trang</option>
            </select>
        </div>
        <button class="btn btn-primary">Lưu</button>
        <a href="banners.php" class="btn btn-secondary">Quay lại</a>
    </form>
</body>
</html>
