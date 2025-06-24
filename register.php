<?php
require_once 'includes/include.php';
require_once 'includes/db_config.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Kiểm tra rỗng
    if ($name === '' || $email === '' || $password === '' || $confirm_password === '') {
        $errors[] = "Vui lòng điền đầy đủ thông tin.";
    }

    // Kiểm tra định dạng email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ.";
    }

    // Kiểm tra mật khẩu trùng khớp
    if ($password !== $confirm_password) {
        $errors[] = "Mật khẩu xác nhận không khớp.";
    }

    // Kiểm tra email đã tồn tại
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Email đã được sử dụng.";
        }
    }

    // Đăng ký
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $email, $hashedPassword])) {
            $success = "🎉 Đăng ký thành công! Bạn có thể <a href='login.php'>đăng nhập ngay</a>.";
        } else {
            $errors[] = "Lỗi khi lưu dữ liệu. Vui lòng thử lại.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-5" style="max-width: 600px;">
    <h3 class="mb-4">📝 Đăng ký tài khoản</h3>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <div class="mb-3">
            <label class="form-label">Họ tên</label>
            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($name ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($email ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Xác nhận mật khẩu</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button class="btn btn-success w-100">Đăng ký</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
