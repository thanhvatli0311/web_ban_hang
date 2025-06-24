<?php
require_once 'includes/include.php';
require_once 'includes/db_config.php';

// Lấy danh mục sản phẩm
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Lấy sản phẩm mới nhất và nổi bật
$new_products = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 8")->fetchAll();
$hot_products = $pdo->query("SELECT * FROM products ORDER BY views DESC LIMIT 8")->fetchAll();

// Lấy danh sách banner đang hoạt động theo ngày và gom nhóm theo vị trí
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT * FROM banners WHERE :today BETWEEN start_date AND end_date ORDER BY start_date DESC");
$stmt->execute([':today' => $today]);
$banners = $stmt->fetchAll();

$banner_positions = [];
foreach ($banners as $banner) {
    $position = $banner['position'];
    $banner_positions[$position][] = $banner;
}
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="includes/css/style.css">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script>
    document.addEventListener("DOMContentLoaded", function () {
        function setupBannerSlider(sliderId) {
            const slider = document.getElementById(sliderId);
            if (!slider) return;

            const slides = slider.querySelectorAll('.banner-slide');
            const prevBtn = document.querySelector(`.banner-prev[data-target="${sliderId}"]`);
            const nextBtn = document.querySelector(`.banner-next[data-target="${sliderId}"]`);
            let index = 0;

            function showSlide(i) {
                slides.forEach((slide, idx) => {
                    slide.classList.toggle('active', idx === i);
                });
            }

            function nextSlide() {
                index = (index + 1) % slides.length;
                showSlide(index);
            }

            function prevSlide() {
                index = (index - 1 + slides.length) % slides.length;
                showSlide(index);
            }

            if (nextBtn) nextBtn.addEventListener('click', nextSlide);
            if (prevBtn) prevBtn.addEventListener('click', prevSlide);

            // Auto slide every 5 seconds
            setInterval(nextSlide, 5000);
        }

        // Khởi tạo cho tất cả slider trên trang
        ['banner-top'].forEach(setupBannerSlider);
    });
</script>

<div class="container py-4">

    <!-- Banner đầu trang -->
    <?php if (!empty($banner_positions['homepage-top'])): ?>
        <div class="mb-4 position-relative overflow-hidden">
            <div class="banner-slider position-relative" id="banner-top">
                <?php foreach ($banner_positions['homepage-top'] as $index => $bn): ?>
                    <a href="<?= htmlspecialchars($bn['link']) ?>" target="_blank" class="banner-slide <?= $index === 0 ? 'active' : '' ?>">
                        <img src="assets/banners/<?= htmlspecialchars($bn['image']) ?>" alt="<?= htmlspecialchars($bn['title']) ?>" class="banner-img">
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if (count($banner_positions['homepage-top']) > 1): ?>
                <!-- Nút điều hướng -->
                <button class="banner-prev" data-target="banner-top">&#10094;</button>
                <button class="banner-next" data-target="banner-top">&#10095;</button>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Danh mục -->
    <div class="mb-5">
        <h4 class="mb-3"><i class="bi bi-tags"></i> Danh Mục Sản Phẩm</h4>
        <div class="d-flex flex-wrap gap-3">
            <?php foreach ($categories as $cat): ?>
                <a href="category.php?id=<?= $cat['id'] ?>" class="text-decoration-none text-dark">
                    <div class="category-card shadow-sm">
                        <i class="bi bi-folder-fill text-primary me-1"></i>
                        <?= htmlspecialchars($cat['name']) ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Sản phẩm mới nhất -->
    <?php
    $promo_stmt = $pdo->prepare("
        SELECT discount_percent FROM promotions
        WHERE (product_id = :pid OR category_id = :cid OR apply_all = 1)
          AND :today BETWEEN start_date AND end_date
        ORDER BY 
            CASE 
                WHEN product_id = :pid THEN 1
                WHEN category_id = :cid THEN 2
                ELSE 3
            END
        LIMIT 1
    ");
    ?>

    <div class="mb-5">
        <h4 class="mb-3"><i class="bi bi-box-seam"></i> Sản Phẩm Mới Nhất</h4>
        <div class="row">
            <?php foreach ($new_products as $product): ?>
                <?php
                    $img_path = (!empty($product['image']) && file_exists($product['image'])) ? $product['image'] : 'uploads/no-image.png';

                    $promo_stmt->execute([
                        ':pid' => $product['id'],
                        ':cid' => $product['category_id'],
                        ':today' => $today
                    ]);
                    $discount_percent = $promo_stmt->fetchColumn();
                    $discounted_price = $discount_percent ? $product['price'] * (1 - $discount_percent / 100) : null;
                ?>
                <div class="col-md-3 mb-4">
                    <div class="product-box">
                    <div class="card product-card h-100">
                        <img 
                            src="data:image/svg+xml;charset=UTF-8,<?= urlencode('<svg width=\'100%\' height=\'180\' xmlns=\'http://www.w3.org/2000/svg\'><rect width=\'100%\' height=\'100%\' fill=\'#e0e0e0\'/><text x=\'50%\' y=\'50%\' alignment-baseline=\'middle\' text-anchor=\'middle\' fill=\'#999\' font-size=\'14\'>Đang tải ảnh...</text></svg>') ?>"
                            data-src="<?= htmlspecialchars($img_path) ?>"
                            alt="<?= htmlspecialchars($product['name']) ?>"
                            class="card-img-top lazy"
                        >
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title mb-1"><?= htmlspecialchars($product['name']) ?></h6>
                            <?php if ($discounted_price): ?>
                                <p class="text-danger fw-semibold mb-1">
                                    <del class="text-muted"><?= number_format($product['price'], 0, ',', '.') ?>₫</del>
                                    <span class="ms-1"><?= number_format($discounted_price, 0, ',', '.') ?>₫</span>
                                    <span class="badge bg-success ms-1">-<?= $discount_percent ?>%</span>
                                </p>
                            <?php else: ?>
                                <p class="text-danger fw-semibold mb-1"><?= number_format($product['price'], 0, ',', '.') ?>₫</p>
                            <?php endif; ?>
                            <p class="text-muted small mb-2"><?= mb_strimwidth($product['description'], 0, 60, '...') ?></p>
                            <div class="d-flex justify-content-between mt-auto">
                                <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Xem
                                </a>
                                <form method="post" action="add_to_cart.php" class="m-0">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-cart-plus me-1"></i> Thêm
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Banner giữa -->
    <?php if (!empty($banner_positions['homepage-middle'])): ?>
        <div class="mb-4">
            <?php foreach ($banner_positions['homepage-middle'] as $bn): ?>
                <a href="<?= htmlspecialchars($bn['link']) ?>" target="_blank">
                    <img src="assets/banners/<?= htmlspecialchars($bn['image']) ?>" alt="<?= htmlspecialchars($bn['title']) ?>" class="banner-img mb-3">
                </a>
            <?php endforeach; ?>
        </div>
                    <?php if (count($banner_positions['homepage-top']) > 1): ?>
                <!-- Nút điều hướng -->
                <button class="banner-prev" data-target="banner-top">&#10094;</button>
                <button class="banner-next" data-target="banner-top">&#10095;</button>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Sản phẩm nổi bật -->
    <div class="mb-5">
        <h4 class="mb-3"><i class="bi bi-star-fill text-warning"></i> Sản Phẩm Nổi Bật</h4>
        <div class="row">
            <?php foreach ($hot_products as $product): ?>
                <?php
                    $img_path = (!empty($product['image']) && file_exists($product['image'])) ? $product['image'] : 'uploads/no-image.png';
                    $promo_stmt->execute([
                        ':pid' => $product['id'],
                        ':cid' => $product['category_id'],
                        ':today' => $today
                    ]);
                    $discount_percent = $promo_stmt->fetchColumn();
                    $discounted_price = $discount_percent ? $product['price'] * (1 - $discount_percent / 100) : null;
                ?>
                <div class="col-md-3 mb-4">
                    <div class="card product-card h-100">
                        <img 
                            src="data:image/svg+xml;charset=UTF-8,<?= urlencode('<svg width=\'100%\' height=\'180\' xmlns=\'http://www.w3.org/2000/svg\'><rect width=\'100%\' height=\'100%\' fill=\'#e0e0e0\'/><text x=\'50%\' y=\'50%\' alignment-baseline=\'middle\' text-anchor=\'middle\' fill=\'#999\' font-size=\'14\'>Đang tải ảnh...</text></svg>') ?>"
                            data-src="<?= htmlspecialchars($img_path) ?>"
                            alt="<?= htmlspecialchars($product['name']) ?>"
                            class="card-img-top lazy"
                        >
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title mb-1"><?= htmlspecialchars($product['name']) ?></h6>
                            <?php if ($discounted_price): ?>
                                <p class="text-danger fw-semibold mb-1">
                                    <del class="text-muted"><?= number_format($product['price'], 0, ',', '.') ?>₫</del>
                                    <span class="ms-1"><?= number_format($discounted_price, 0, ',', '.') ?>₫</span>
                                    <span class="badge bg-success ms-1">-<?= $discount_percent ?>%</span>
                                </p>
                            <?php else: ?>
                                <p class="text-danger fw-semibold mb-1"><?= number_format($product['price'], 0, ',', '.') ?>₫</p>
                            <?php endif; ?>
                            <p class="text-muted small mb-2"><?= mb_strimwidth($product['description'], 0, 60, '...') ?></p>
                            <div class="d-flex justify-content-between mt-auto">
                                <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Xem
                                </a>
                                <form method="post" action="add_to_cart.php" class="m-0">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-cart-plus me-1"></i> Thêm
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Banner cuối trang -->
    <?php if (!empty($banner_positions['homepage-bottom'])): ?>
        <div class="mb-4">
            <?php foreach ($banner_positions['homepage-bottom'] as $bn): ?>
                <a href="<?= htmlspecialchars($bn['link']) ?>" target="_blank">
                    <img src="assets/banners/<?= htmlspecialchars($bn['image']) ?>" alt="<?= htmlspecialchars($bn['title']) ?>" class="banner-img mb-3">
                </a>
            <?php endforeach; ?>
        </div>
                    <?php if (count($banner_positions['homepage-top']) > 1): ?>
                <!-- Nút điều hướng -->
                <button class="banner-prev" data-target="banner-top">&#10094;</button>
                <button class="banner-next" data-target="banner-top">&#10095;</button>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<!-- Lazy load script -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll("img.lazy").forEach(img => {
            const realSrc = img.getAttribute("data-src");
            if (realSrc) {
                const tempImg = new Image();
                tempImg.onload = () => img.src = realSrc;
                tempImg.src = realSrc;
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
