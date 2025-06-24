<?php
require_once 'includes/include.php';
require_once 'includes/db_config.php';

$category_id = $_GET['id'] ?? 0;
$sort = $_GET['sort'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$quick_price = $_GET['quick_price'] ?? '';

// Xử lý khoảng giá nhanh
if (!empty($quick_price)) {
    switch ($quick_price) {
        case '1':
            $min_price = 0;
            $max_price = 500000;
            break;
        case '2':
            $min_price = 500000;
            $max_price = 1000000;
            break;
        case '3':
            $min_price = 1000000;
            $max_price = 2000000;
            break;
        case '4':
            $min_price = 2000000;
            $max_price = 999999999;
            break;
    }
}

$params = [':category_id' => $category_id];
$where = ['p.category_id = :category_id'];

if (!empty($min_price)) {
    $where[] = "p.price >= :min_price";
    $params[':min_price'] = $min_price;
}
if (!empty($max_price)) {
    $where[] = "p.price <= :max_price";
    $params[':max_price'] = $max_price;
}

$order_by = '';
switch ($sort) {
    case 'price_asc':
        $order_by = 'ORDER BY p.price ASC';
        break;
    case 'price_desc':
        $order_by = 'ORDER BY p.price DESC';
        break;
    case 'name':
        $order_by = 'ORDER BY p.name ASC';
        break;
    default:
        $order_by = 'ORDER BY p.created_at DESC';
        break;
}

$where_sql = implode(' AND ', $where);
$stmt = $pdo->prepare("SELECT p.* FROM products p WHERE $where_sql $order_by");
$stmt->execute($params);
$products = $stmt->fetchAll();

$category = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
$category->execute([$category_id]);
$category_name = $category->fetchColumn();

include 'includes/header.php';
?>
<style>
        .product-card {
        background: linear-gradient(135deg, #ffffff, #f3f3f3);
        border: 1px solid #e5e5e5;
        border-radius: 20px;
        overflow: hidden;
        transition: box-shadow 0.3s, transform 0.3s;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .product-card:hover {
        box-shadow: 0 0 10px rgba(0, 123, 255, 0.25), 0 0 30px rgba(0, 123, 255, 0.1);
        transform: translateY(-5px);
    }

    .product-card img {
        height: 200px;
        width: 100%;
        object-fit: cover;
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
    }

</style>

<div class="container py-4">
    <h4 class="mb-3">Danh mục: <?= htmlspecialchars($category_name) ?></h4>

    <form method="get" class="mb-4 row gx-2 align-items-end">
        <input type="hidden" name="id" value="<?= $category_id ?>">
        <div class="col-md-3">
            <label class="form-label">Khoảng giá nhanh:</label>
            <select class="form-select" name="quick_price" onchange="this.form.submit()">
                <option value="">-- Chọn --</option>
                <option value="1" <?= $quick_price == '1' ? 'selected' : '' ?>>Dưới 500.000₫</option>
                <option value="2" <?= $quick_price == '2' ? 'selected' : '' ?>>500.000₫ – 1.000.000₫</option>
                <option value="3" <?= $quick_price == '3' ? 'selected' : '' ?>>1.000.000₫ – 2.000.000₫</option>
                <option value="4" <?= $quick_price == '4' ? 'selected' : '' ?>>Trên 2.000.000₫</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Giá từ:</label>
            <input type="number" name="min_price" class="form-control" value="<?= htmlspecialchars($min_price) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Đến:</label>
            <input type="number" name="max_price" class="form-control" value="<?= htmlspecialchars($max_price) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Sắp xếp:</label>
            <select name="sort" class="form-select" onchange="this.form.submit()">
                <option value="">Mặc định</option>
                <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Giá giảm dần</option>
                <option value="name" <?= $sort == 'name' ? 'selected' : '' ?>>Tên A-Z</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">&nbsp;</label>
            <button type="submit" class="btn btn-primary w-100">Lọc</button>
        </div>
    </form>

    <div class="row">
        <?php foreach ($products as $product): ?>
            <?php
                $image = trim($product['image']);
                $img_path = (!empty($image) && file_exists($image)) ? $image : 'uploads/no-image.png';

                $today = date('Y-m-d');
                $stmt = $pdo->prepare("
                    SELECT discount_percent FROM promotions
                    WHERE (
                        product_id = :pid OR 
                        category_id = :cid OR 
                        apply_all = 1
                    ) AND :today BETWEEN start_date AND end_date
                    ORDER BY 
                        CASE 
                            WHEN product_id = :pid THEN 1
                            WHEN category_id = :cid THEN 2
                            ELSE 3
                        END
                    LIMIT 1
                ");
                $stmt->execute([
                    ':pid' => $product['id'],
                    ':cid' => $product['category_id'],
                    ':today' => $today
                ]);
                $discount_percent = $stmt->fetchColumn();
                $discounted_price = $discount_percent ? $product['price'] * (1 - $discount_percent / 100) : null;
            ?>
            <div class="col-md-3 mb-4">
                <div class="card product-card h-100">
                    <img src="<?= htmlspecialchars($img_path) ?>" class="card-img-top" style="height: 180px; object-fit: cover;">
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
                        <div class="mt-auto d-flex justify-content-between">
                            <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Xem
                            </a>
                            <form method="post" action="add_to_cart.php">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="bi bi-cart-plus"></i> Thêm
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
