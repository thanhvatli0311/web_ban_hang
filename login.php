<?php
require_once 'includes/include.php';
require_once 'includes/db_config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = "Vui lòng nhập đầy đủ Email và Mật khẩu.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Lưu session người dùng
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];

            // Điều hướng theo vai trò
            if ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $errors[] = "Email hoặc mật khẩu không đúng.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-5" style="max-width: 500px;">
    <h3 class="mb-4">Đăng nhập</h3>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input 
                type="email" 
                name="email" 
                id="email" 
                class="form-control" 
                required 
                autocomplete="off" 
                value="<?= htmlspecialchars($email ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mật khẩu</label>
            <input 
                type="password" 
                name="password" 
                id="password" 
                class="form-control" 
                required 
                autocomplete="off">
        </div>
        <button class="btn btn-primary w-100" type="submit">Đăng nhập</button>

        <div class="text-center mt-3">
            <p>Bạn chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
