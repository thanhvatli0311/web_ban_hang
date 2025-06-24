<?php
require_once '../includes/include.php';
require_once '../includes/db_config.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $start_date = $_POST['start_date'] ?? date('Y-m-d');
    $end_date = $_POST['end_date'] ?? date('Y-m-d', strtotime('+30 days'));
    $is_active = 1; // mặc định là đang kích hoạt

    // Xử lý upload ảnh
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $img_name = basename($_FILES['image']['name']);
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($img_ext, $allowed_ext)) {
            $errors[] = 'Chỉ chấp nhận các định dạng ảnh: jpg, jpeg, png, gif, webp.';
        } else {
            $new_name = uniqid('banner_', true) . '.' . $img_ext;
            $target_path = "../assets/banners/$new_name";

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $image_path = $new_name;
            } else {
                $errors[] = 'Không thể tải lên ảnh.';
            }
        }
    } else {
        $errors[] = 'Vui lòng chọn hình ảnh banner.';
    }

    // Thêm vào CSDL
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO banners 
            (title, image, link, position, is_active, start_date, end_date) 
            VALUES (:title, :image, :link, :position, :is_active, :start_date, :end_date)");
        $stmt->execute([
            ':title' => $title,
            ':image' => $image_path,
            ':link' => $link,
            ':position' => $position,
            ':is_active' => $is_active,
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ]);

        $success = "✅ Thêm banner thành công!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Banner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/flatly/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-plus-circle-fill"></i> Thêm Banner Mới</h3>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php elseif (!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="bg-light p-4 rounded shadow-sm">
        <div class="mb-3">
            <label for="title" class="form-label">Tiêu đề <span class="text-danger">*</span></label>
            <input type="text" name="title" id="title" required class="form-control"
                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="image" class="form-label">Hình ảnh <span class="text-danger">*</span></label>
            <input type="file" name="image" id="image" accept="image/*" class="form-control">
        </div>

        <div class="mb-3">
            <label for="link" class="form-label">Liên kết (tuỳ chọn)</label>
            <input type="url" name="link" id="link" class="form-control"
                   value="<?= htmlspecialchars($_POST['link'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="position" class="form-label">Vị trí hiển thị</label>
            <select name="position" id="position" class="form-select">
                <option value="">-- Chọn vị trí hiển thị --</option>
                <option value="homepage-top" <?= ($_POST['position'] ?? '') == 'homepage-top' ? 'selected' : '' ?>>Trang chủ - Trên cùng</option>
                <option value="homepage-middle" <?= ($_POST['position'] ?? '') == 'homepage-middle' ? 'selected' : '' ?>>Trang chủ - Giữa</option>
                <option value="homepage-bottom" <?= ($_POST['position'] ?? '') == 'homepage-bottom' ? 'selected' : '' ?>>Trang chủ - Cuối</option>
                <option value="sidebar-left" <?= ($_POST['position'] ?? '') == 'sidebar-left' ? 'selected' : '' ?>>Sidebar - Bên trái</option>
                <option value="sidebar-right" <?= ($_POST['position'] ?? '') == 'sidebar-right' ? 'selected' : '' ?>>Sidebar - Bên phải</option>
                <option value="product-top" <?= ($_POST['position'] ?? '') == 'product-top' ? 'selected' : '' ?>>Chi tiết sản phẩm - Trên cùng</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="start_date" class="form-label">Ngày bắt đầu</label>
            <input type="date" name="start_date" id="start_date" class="form-control"
                   value="<?= htmlspecialchars($_POST['start_date'] ?? date('Y-m-d')) ?>">
        </div>

        <div class="mb-3">
            <label for="end_date" class="form-label">Ngày kết thúc</label>
            <input type="date" name="end_date" id="end_date" class="form-control"
                   value="<?= htmlspecialchars($_POST['end_date'] ?? date('Y-m-d', strtotime('+30 days'))) ?>">
        </div>

        <div class="d-flex justify-content-between">
            <a href="banners.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left-circle"></i> Quay lại
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Lưu Banner
            </button>
        </div>
    </form>
</div>
</body>
</html>
