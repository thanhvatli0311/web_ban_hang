<?php
require_once __DIR__ . '/includes/db_config.php';
require_once __DIR__ . '/includes/include.php';

$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';

function getActivePromotion($pdo, $productId, $categoryId) {
    $stmt = $pdo->prepare("
        SELECT discount_percent FROM promotions
        WHERE 
            (
                (product_id = :pid) OR 
                (category_id = :cid) OR 
                (apply_all = 1)
            )
            AND start_date <= CURDATE() AND end_date >= CURDATE()
        ORDER BY 
            CASE 
                WHEN product_id = :pid THEN 1
                WHEN category_id = :cid THEN 2
                ELSE 3
            END
        LIMIT 1
    ");
    $stmt->execute([
        ':pid' => $productId,
        ':cid' => $categoryId
    ]);
    return $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kết quả tìm kiếm</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .price-line {
            text-decoration: line-through;
            color: #888;
        }
        .discount-badge {
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container my-4">
    <h2>Kết quả tìm kiếm cho: <em><?= htmlspecialchars($keyword) ?></em></h2>
    <div class="row mt-3">
        <?php
        if ($keyword !== '') {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE :kw OR description LIKE :kw LIMIT 20");
            $stmt->execute([':kw' => '%' . $keyword . '%']);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($products) {
                foreach ($products as $product):
                    $discount = getActivePromotion($pdo, $product['id'], $product['category_id']);
                    $hasPromo = $discount > 0;
                    $originalPrice = $product['price'];
                    $finalPrice = $hasPromo ? $originalPrice * (100 - $discount) / 100 : $originalPrice;
                    ?>
                    <div class="col-md-3 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <img src="<?= htmlspecialchars($product['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>

                                <?php if ($hasPromo): ?>
                                    <p class="mb-1">
                                        <span class="price-line"><?= number_format($originalPrice) ?>đ</span>
                                        <span class="text-danger fw-bold ms-2"><?= number_format($finalPrice) ?>đ</span>
                                        <span class="badge bg-success discount-badge ms-2">-<?= $discount ?>%</span>
                                    </p>
                                <?php else: ?>
                                    <p class="text-danger fw-bold"><?= number_format($originalPrice) ?>đ</p>
                                <?php endif; ?>

                                <div class="mt-auto d-flex justify-content-between gap-2">
                                    <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-outline-secondary btn-sm w-50 rounded-pill">
                                        <i class="bi bi-eye"></i> Xem
                                    </a>
                                    <form method="post" action="add_to_cart.php" class="w-50 m-0">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-success btn-sm w-100 rounded-pill">
                                            <i class="bi bi-cart-plus"></i> Thêm
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach;
            } else {
                echo "<p>Không tìm thấy sản phẩm nào.</p>";
            }
        } else {
            echo "<p>Vui lòng nhập từ khóa để tìm kiếm.</p>";
        }
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
</body>
</html>
