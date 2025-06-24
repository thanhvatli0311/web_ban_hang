<?php
require_once 'includes/include.php';
require_once 'includes/db_config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Sản phẩm không tồn tại.");
}
$product_id = (int) $_GET['id'];

// Lấy sản phẩm
$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();
if (!$product) die("Không tìm thấy sản phẩm.");

// Hình ảnh phụ
$stmt_img = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
$stmt_img->execute([$product_id]);
$product_images = $stmt_img->fetchAll();

// Sản phẩm liên quan
$stmt_related = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4");
$stmt_related->execute([$product['category_id'], $product_id]);
$related_products = $stmt_related->fetchAll();

// Tính giá khuyến mãi nếu có
$today = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT discount_percent
    FROM promotions
    WHERE (
        product_id = :pid OR 
        category_id = :cid OR 
        apply_all = 1
    )
    AND :today BETWEEN start_date AND end_date
    ORDER BY 
        CASE 
            WHEN product_id = :pid THEN 1
            WHEN category_id = :cid THEN 2
            ELSE 3
        END
    LIMIT 1
");
$stmt->execute([
    ':pid' => $product_id,
    ':cid' => $product['category_id'],
    ':today' => $today
]);
$discount_percent = $stmt->fetchColumn();
$original_price = $product['price'];
if ($discount_percent) {
    $discounted_price = $original_price * (1 - $discount_percent / 100);
}

include 'includes/header.php';
?>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    .product-title {
        font-size: 1.8rem;
        font-weight: bold;
    }
    .product-card {
        border-radius: 0.75rem;
        border: 1px solid #eee;
        transition: 0.3s ease;
    }
    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.05);
    }
    .product-card img {
        height: 180px;
        object-fit: cover;
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
    }
    .thumbnail {
        width: 80px;
        height: 80px;
        object-fit: cover;
        cursor: pointer;
        border: 2px solid transparent;
    }
    .thumbnail:hover {
        border-color: #0d6efd;
    }
</style>

<div class="container py-5">
    <div class="row mb-5">
        <!-- Ảnh sản phẩm -->
        <div class="col-md-6 mb-4">
            <?php $main_img = $product_images[0]['image_path'] ?? 'uploads/no-image.png'; ?>
            <img id="mainImage" src="<?= htmlspecialchars($main_img) ?>" class="img-fluid rounded border mb-3 w-100" style="max-height: 450px; object-fit: cover;" alt="<?= htmlspecialchars($product['name']) ?>">
            <div class="d-flex gap-2 flex-wrap">
                <?php foreach ($product_images as $img): ?>
                    <img src="<?= htmlspecialchars($img['image_path']) ?>" class="thumbnail rounded" onclick="document.getElementById('mainImage').src=this.src;" alt="ảnh phụ">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Thông tin -->
        <div class="col-md-6">
            <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
            <p class="text-muted mb-1"><i class="bi bi-folder-fill"></i> <?= htmlspecialchars($product['category_name']) ?></p>

            <?php if (!empty($discount_percent)): ?>
                <h4 class="text-danger mb-3">
                    <del class="text-muted"><?= number_format($original_price, 0, ',', '.') ?>₫</del>
                    <span class="ms-2"><?= number_format($discounted_price, 0, ',', '.') ?>₫</span>
                    <span class="badge bg-success ms-2">-<?= $discount_percent ?>%</span>
                </h4>
            <?php else: ?>
                <h4 class="text-danger mb-3"><?= number_format($original_price, 0, ',', '.') ?>₫</h4>
            <?php endif; ?>

            <p class="mb-4"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            <p><strong>Tồn kho:</strong> <?= (int)$product['stock'] ?></p>

            <!-- Form thêm vào giỏ -->
            <form method="post" action="add_to_cart.php" class="mt-4">
                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                <div class="input-group" style="max-width: 200px;">
                    <input type="number" name="quantity" value="1" min="1" class="form-control" required>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sản phẩm liên quan -->
    <hr>
    <h4 class="mb-4"><i class="bi bi-grid-3x3-gap-fill"></i> Sản phẩm liên quan</h4>
    <div class="row">
        <?php foreach ($related_products as $item): ?>
            <?php
                $stmt_img = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ? LIMIT 1");
                $stmt_img->execute([$item['id']]);
                $img = $stmt_img->fetchColumn();
                $img_src = $img ?: 'uploads/no-image.png';
            ?>
            <div class="col-md-3 mb-4">
                <div class="card product-card h-100">
                    <img src="<?= htmlspecialchars($img_src) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?>">
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title"><?= htmlspecialchars($item['name']) ?></h6>
                        <p class="text-danger fw-bold"><?= number_format($item['price'], 0, ',', '.') ?>₫</p>
                        <a href="product.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary mt-auto"><i class="bi bi-eye"></i> Xem chi tiết</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
