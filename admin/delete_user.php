<?php
// delete_user.php
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/db_config.php';


if (!isset($_GET['id'])) { header('Location: users.php'); exit; }
$id = (int)$_GET['id'];

try {
    // Cố gắng xóa
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = 'Đã xóa người dùng';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Không thể xóa người dùng: ' . $e->getMessage();
}
header('Location: users.php');
exit;
?>