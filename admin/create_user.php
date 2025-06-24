<?php
// create_user.php
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/db_config.php';


$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!$name || !$email || !$password) {
        $errors[] = 'Vui lòng điền đầy đủ các trường.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ.';
    } else {
        // Kiểm tra trùng email
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Email đã tồn tại.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role,  created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$name, $email, $hash, $role]);
        header('Location: users.php'); exit;
    }
}
?>

<?php include '../includes/header.php'; ?>
<div class="container py-4" style="max-width:600px;">
    <h3>➕ Thêm người dùng</h3>
    <?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
    </div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Tên</label>
            <input name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Mật khẩu</label>
            <input name="password" type="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Vai trò</label>
            <select name="role" class="form-select">
                <option value="customer">Customer</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="form-check mb-3">
            <input name="is_active" type="checkbox" class="form-check-input" id="active" checked>
            <label for="active" class="form-check-label">Kích hoạt tài khoản</label>
        </div>
        <button class="btn btn-success">Thêm</button>
        <a href="users.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>
<?php include '../includes/footer.php'; ?>