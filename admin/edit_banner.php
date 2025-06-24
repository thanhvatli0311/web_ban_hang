<?php
require_once '../includes/include.php';
require_once '../includes/db_config.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM banners WHERE id = ?");
$stmt->execute([$id]);
$banner = $stmt->fetch();

if (!$banner) {
    die("Banner không tồn tại.");
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $start_date = $_POST['start_date'] ?? date('Y-m-d');
    $end_date = $_POST['end_date'] ?? date('Y-m-d', strtotime('+30 days'));

    $image = $banner['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $img_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($img_ext, $allowed)) {
            $errors[] = "Chỉ chấp nhận định dạng ảnh jpg, jpeg, png, webp, gif.";
        } else {
            $new_name = uniqid('banner_', true) . '.' . $img_ext;
            $target = "../assets/banners/$new_name";
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $image = $new_name;
            } else {
                $errors[] = "Không thể tải ảnh lên.";
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE banners SET title = :title, link = :link, image = :image, position = :position, is_active = :is_active, start_date = :start_date, end_date = :end_date WHERE id = :id");
        $stmt->execute([
            ':title' => $title,
            ':link' => $link,
            ':image' => $image,
            ':position' => $position,
            ':is_active' => $is_active,
            ':start_date' => $start_date,
            ':end_date' => $end_date,
            ':id' => $id
        ]);
        $success = "Cập nhật banner thành công!";
    }
}
?>

<!-- Giao diện giống add_banner.php -->
<link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/flatly/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<div class="container py-4">
    <h3><i class="bi bi-pencil-square"></i> Sửa Banner</h3>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul>
        </div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="bg-light p-4 rounded shadow-sm">
        <div class="mb-3">
            <label class="form-label">Tiêu đề</label>
            <input name="title" class="form-control" required value="<?= htmlspecialchars($banner['title']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Hình ảnh hiện tại</label><br>
            <img src="../assets/banners/<?= htmlspecialchars($banner['image']) ?>" height="60"><br>
            <input type="file" name="image" class="form-control mt-2">
        </div>
        <div class="mb-3">
            <label class="form-label">Liên kết</label>
            <input name="link" class="form-control" value="<?= htmlspecialchars($banner['link']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Vị trí hiển thị</label>
            <select name="position" class="form-select">
                <?php
                $positions = [
                    "homepage-top" => "Trang chủ - Trên cùng",
                    "homepage-middle" => "Trang chủ - Giữa",
                    "homepage-bottom" => "Trang chủ - Cuối",
                    "sidebar-left" => "Sidebar - Trái",
                    "sidebar-right" => "Sidebar - Phải",
                    "product-top" => "Chi tiết sản phẩm - Trên cùng"
                ];
                foreach ($positions as $key => $label) {
                    $selected = $banner['position'] == $key ? 'selected' : '';
                    echo "<option value='$key' $selected>$label</option>";
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Ngày bắt đầu</label>
            <input type="date" name="start_date" class="form-control" value="<?= $banner['start_date'] ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Ngày kết thúc</label>
            <input type="date" name="end_date" class="form-control" value="<?= $banner['end_date'] ?>">
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= $banner['is_active'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_active">Kích hoạt</label>
        </div>

        <div class="d-flex justify-content-between">
            <a href="banners.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Quay lại</a>
            <button class="btn btn-success"><i class="bi bi-save"></i> Cập nhật</button>
        </div>
    </form>
</div>
