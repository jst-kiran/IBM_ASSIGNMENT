<?php
session_start();
require_once 'config.php';
include 'navbar.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Remove product from wishlist
if(isset($_GET['remove']) && is_numeric($_GET['remove'])){
    $remove_id = $_GET['remove'];
    mysqli_query($conn, "DELETE FROM wishlist_items WHERE user_id=$user_id AND product_id=$remove_id");
    header("Location: wishlist.php");
    exit;
}

// Move product from wishlist to cart
if(isset($_GET['add_to_cart']) && is_numeric($_GET['add_to_cart'])){
    $product_id = $_GET['add_to_cart'];

    // Check if already in cart
    $in_cart = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id=$user_id AND product_id=$product_id")) > 0;

    if(!$in_cart){
        mysqli_query($conn, "INSERT INTO cart_items (user_id, product_id, quantity) VALUES ($user_id, $product_id, 1)");
    }

    // Remove from wishlist
    mysqli_query($conn, "DELETE FROM wishlist_items WHERE user_id=$user_id AND product_id=$product_id");

    header("Location: cart.php");
    exit;
}

// Fetch wishlist products
$result = mysqli_query($conn, "SELECT p.* FROM products p JOIN wishlist_items w ON p.id=w.product_id WHERE w.user_id=$user_id");
$wishlist_products = [];
while($row = mysqli_fetch_assoc($result)){
    $wishlist_products[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Wishlist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body  style=" background:#db9eda;">

<h1 class="text-center mb-4">Your Wishlist</h1>
<div class="container">
    
<?php if(count($wishlist_products) > 0): ?>
    <div class="row">
        <?php foreach($wishlist_products as $p): ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100" style="border-radius:10px;">
                    <img src="uploads/<?= $p['image'] ?>" class="card-img-top" style="height:180px; object-fit:cover; border-top-left-radius:10px; border-top-right-radius:10px;">
                    <div class="card-body" style="font-size:16px;">
                        <h5 class="card-title"><?= $p['name'] ?></h5>
                        <p class="card-text text-muted">₹<?= $p['price'] ?></p>
                        <p class="card-text"><small><?= $p['description'] ?></small></p>

                        <!-- Buttons -->
                        <a href="wishlist.php?add_to_cart=<?= $p['id'] ?>" class="btn btn-primary btn-sm mb-2" style="background:#c25dc0;">Add to Cart</a>
                        <a href="wishlist.php?remove=<?= $p['id'] ?>" class="btn btn-danger btn-sm mb-2">Remove</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p class="text-center">Your wishlist is empty.</p>
<?php endif; ?>

</div>
</body>
</html>
