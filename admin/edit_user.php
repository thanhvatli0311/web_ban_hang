<?php
// edit_user.php
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/db_config.php';


if (!isset($_GET['id'])) { header('Location: users.php'); exit; }
$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) exit('Không tìm thấy người dùng');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!$name || !$email) {
        $errors[] = 'Vui lòng điền đầy đủ các trường.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ.';
    }
    // Check email unique
    $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id <> ?");
    $stmt2->execute([$email, $id]);
    if ($stmt2->fetchColumn() > 0) {
        $errors[] = 'Email đã tồn tại.';
    }

    if (empty($errors)) {
        $pdo->prepare(
            "UPDATE users SET name=?, email=?, role=? WHERE id=?"
        )->execute([$name, $email, $role,  $id]);
        header('Location: users.php'); exit;
    }
}
?>

<?php include '../includes/header.php'; ?>
<div class="container py-4" style="max-width:600px;">
    <h3>✏️ Chỉnh sửa người dùng</h3>
    <?php if ($errors): ?>
    <div class="alert alert-danger"><ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Tên</label>
            <input name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? $user['name']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Vai trò</label>
            <select name="role" class="form-select">
                <option value="customer" <?= ($user['role']==='customer')?'selected':'' ?>>Customer</option>
                <option value="admin" <?= ($user['role']==='admin')?'selected':'' ?>>Admin</option>
            </select>
        </div>

        <button class="btn btn-warning">Cập nhật</button>
        <a href="users.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>
<?php include '../includes/footer.php'; ?>