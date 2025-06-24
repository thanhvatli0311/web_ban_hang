<?php
require_once 'includes/include.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'])) {
    foreach ($_POST['quantity'] as $id => $qty) {
        $qty = max(1, (int)$qty);
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity'] = $qty;
        }
    }
}

header("Location: cart.php");
exit;
