<?php
session_start();
require_once 'config.php';
include 'navbar.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    echo "Invalid product.";
    exit;
}
$product_id = $_GET['id'];

// Fetch product
$result = mysqli_query($conn, "SELECT * FROM products WHERE id=$product_id");
$product = mysqli_fetch_assoc($result);
if(!$product){ echo "Product not found."; exit; }

// Fetch real-time cart/wishlist counts for top nav
$cart_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM cart_items WHERE user_id=$user_id"))['c'];
$wishlist_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as w FROM wishlist_items WHERE user_id=$user_id"))['w'];

// Check if product is in cart or wishlist
$in_cart = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id=$user_id AND product_id=$product_id")) > 0;
$in_wish = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM wishlist_items WHERE user_id=$user_id AND product_id=$product_id")) > 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $product['name'] ?> - Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body  style=" background:#db9eda;">

<!-- Top Navigation -->
<div class="container" style="margin-bottom: 50px;">
<div class="d-flex justify-content-end mb-4">
    <a href="cart.php" class="btn btn-primary me-2" style="background-color:#c25dc0;">
        Cart (<?= $cart_count ?>)
    </a>
    <a href="wishlist.php" class="btn btn-warning" style="background-color:#ff9f43;">
        Wishlist (<?= $wishlist_count ?>)
    </a>
</div>

<h1 class="text-center mb-4"><?= $product['name'] ?></h1>

<div class="row">
    <div class="col-md-6">
        <img src="uploads/<?= $product['image'] ?>" class="img-fluid" style="border-radius:10px;">
    </div>
    <div class="col-md-6">
        <h3>Price: ₹<?= $product['price'] ?></h3>
        <p><?= $product['description'] ?></p>

        <a href="add_to_cart.php?id=<?= $product['id'] ?>" 
           class="btn btn-primary mb-2 <?= $in_cart ? 'disabled' : '' ?>" style="background:#c25dc0;">
           <?= $in_cart ? 'In Cart' : 'Add to Cart' ?>
        </a>

        <a href="add_to_wishlist.php?id=<?= $product['id'] ?>" 
           class="btn btn-warning mb-2 <?= $in_wish ? 'disabled' : '' ?>" style="background:#ff9f43;">
           <?= $in_wish ? 'In Wishlist' : 'Add to Wishlist' ?>
        </a>
    </div>
</div>
</div>

</body>
</html>
