<?php
require_once '../includes/include.php';
require_once '../includes/db_config.php';

// Lấy danh sách banner
$stmt = $pdo->query("SELECT * FROM banners ORDER BY id DESC");
$banners = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Banner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🖼️ Quản Lý Banner</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-house-door-fill"></i> Trang Quản Trị
        </a>
    </div>

    <div class="mb-3">
        <a href="add_banner.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Thêm Banner Mới
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle" id="bannerTable">
            <thead class="table-light">
                <tr>
                    <th style="width: 160px;">Hình ảnh</th>
                    <th>Tiêu đề</th>
                    <th>Liên kết</th>
                    <th>Vị trí</th>
                    <th>Hiển thị</th>
                    <th>Bắt đầu</th>
                    <th>Kết thúc</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($banners as $banner): ?>
                <tr>
                    <td>
                        <img src="../assets/banners/<?= htmlspecialchars($banner['image']) ?>" 
                             alt="Banner" class="img-fluid rounded" style="height: 60px; object-fit: cover;">
                    </td>
                    <td><?= htmlspecialchars($banner['title']) ?></td>
                    <td>
                        <?php if ($banner['link']): ?>
                            <a href="<?= htmlspecialchars($banner['link']) ?>" target="_blank">
                                <?= htmlspecialchars($banner['link']) ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">Không có</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($banner['position']) ?></td>
                    <td>
                        <?= $banner['is_active'] 
                            ? '<span class="badge bg-success">Đang hiển thị</span>' 
                            : '<span class="badge bg-secondary">Ẩn</span>' ?>
                    </td>
                    <td><?= date('d/m/Y', strtotime($banner['start_date'])) ?></td>
                    <td><?= date('d/m/Y', strtotime($banner['end_date'])) ?></td>
                    <td>
                        <a href="edit_banner.php?id=<?= $banner['id'] ?>" class="btn btn-sm btn-warning me-1">
                            <i class="bi bi-pencil-square"></i> Sửa
                        </a>
                        <a href="delete_banner.php?id=<?= $banner['id'] ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('Xóa banner này?');">
                            <i class="bi bi-trash"></i> Xóa
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        new DataTable('#bannerTable', {
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/vi.json'
            }
        });
    });
</script>
</body>
</html>
