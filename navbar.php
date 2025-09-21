<?php
// Start session if not started
if (session_status() == PHP_SESSION_NONE) session_start();
require_once 'config.php';

$user_id = $_SESSION['user_id'] ?? null;

$cart_count = 0;
$wishlist_count = 0;

if ($user_id) {
    // Count cart items
    $res = mysqli_query($conn, "SELECT SUM(quantity) AS total_qty FROM cart_items WHERE user_id=$user_id");
    $row = mysqli_fetch_assoc($res);
    $cart_count = $row['total_qty'] ?? 0;

    // Count wishlist items
    $res2 = mysqli_query($conn, "SELECT COUNT(*) AS total_wish FROM wishlist_items WHERE user_id=$user_id");
    $row2 = mysqli_fetch_assoc($res2);
    $wishlist_count = $row2['total_wish'] ?? 0;
}
?>

<nav class="navbar navbar-expand-lg navbar-light mb-4 sticky-top" style="background-color: #d1e7dd; border-radius:10px;">
    <div class="container">
        <a class="navbar-brand fw-bold" href="welcome.php">MyStore</a>
        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav">
                <li class="nav-item me-3">
                    <a class="btn btn-success" href="cart.php" style="background:#198754; border-color:#145c32;">
                        Cart (<?= $cart_count ?>)
                    </a>
                </li>
                <li class="nav-item me-3">
                    <a class="btn btn-success" href="wishlist.php" style="background:#198754; border-color:#145c32;">
                        Wishlist (<?= $wishlist_count ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-danger" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
