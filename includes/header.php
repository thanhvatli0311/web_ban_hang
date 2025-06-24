<?php
require_once __DIR__ . '/include.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạp hóa Hồng Trọng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/flatly/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .bg-primary {
            background-color: #dc3545 !important;
        }
        .navbar-dark .navbar-nav .nav-link.active,
        .navbar-dark .navbar-nav .nav-link:hover,
        .btn-primary {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
        }
        .logo-img {
            height: 40px;
            width: auto;
            object-fit: contain;
        }
        #liveResults {
            max-height: 300px;
            overflow-y: auto;
            z-index: 9999;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="/index.php">
            <img src="/assets/images/logo.png" alt="Logo" class="logo-img">
            <span>Tạp hóa Hồng Trọng</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="/index.php">Trang Chủ</a></li>
                <li class="nav-item"><a class="nav-link" href="/cart.php">Giỏ Hàng</a></li>
                <li class="nav-item"><a class="nav-link" href="/checkout.php">Thanh Toán</a></li>
            </ul>

            <!-- Tìm kiếm -->
            <form class="d-flex" id="searchForm" action="/search.php" method="get">
                <input class="form-control me-2" type="search" id="searchInput" name="q" placeholder="Tìm kiếm sản phẩm..." aria-label="Search">
                <button class="btn btn-outline-light" type="submit">🔍</button>
                <div id="liveResults" class="list-group position-absolute d-none"></div>

            </form>

            <!-- Tài khoản -->
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'customer'): ?>
                    <li class="nav-item">
                        <span class="nav-link text-white">👤 Xin chào, <?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="/logout.php">Đăng Xuất</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/login.php">Đăng Nhập</a></li>
                    <li class="nav-item"><a class="nav-link" href="/register.php">Đăng Ký</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Nội dung trang -->
<div class="container my-4">

<!-- Live Search Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('searchInput');
    const resultsBox = document.getElementById('liveResults');
    let controller;

    input.addEventListener('input', async function () {
        const keyword = this.value.trim();
        if (keyword.length < 2) {
            resultsBox.classList.add('d-none');
            return;
        }

        if (controller) controller.abort();
        controller = new AbortController();

        try {
            const res = await fetch(`/search.php?q=${encodeURIComponent(keyword)}`, {
                signal: controller.signal
            });
            const data = await res.json();

            resultsBox.innerHTML = '';
            if (data.length === 0) {
                resultsBox.innerHTML = '<div class="list-group-item">Không tìm thấy</div>';
            } else {
                data.forEach(item => {
                    const div = document.createElement('a');
                    div.href = item.link;
                    div.className = 'list-group-item list-group-item-action';
                    div.innerHTML = `<strong>${item.name}</strong><br><small>${item.type}</small>`;
                    resultsBox.appendChild(div);
                });
            }
            resultsBox.classList.remove('d-none');
        } catch (e) {
            if (e.name !== 'AbortError') console.error(e);
        }
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#searchForm')) {
            resultsBox.classList.add('d-none');
        }
    });
});
</script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>