<?php
require_once 'includes/include.php';

$id = $_GET['id'] ?? null;
if ($id && isset($_SESSION['cart'][$id])) {
    unset($_SESSION['cart'][$id]);
}

header('Location: cart.php');
exit;
