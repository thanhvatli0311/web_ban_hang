<?php
require_once '../includes/include.php';
require_once '../includes/db_config.php';

$id = $_GET['id'] ?? 0;

// Xoá bản ghi
$stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
$stmt->execute([$id]);

// Quay lại danh sách
header("Location: banners.php");
exit;
