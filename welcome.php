<?php
session_start();
require_once 'config.php';
include 'navbar.php'; 


// If not logged in, redirect
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch cart and wishlist counts from DB
$cart_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM cart_items WHERE user_id=$user_id"))['c'];
$wishlist_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as w FROM wishlist_items WHERE user_id=$user_id"))['w'];

// Fetch categories
$categories = mysqli_query($conn, "SELECT * FROM categories");

// Fetch products based on selected category
$products = null;
if(isset($_GET['cat_id'])){
    $cat_id = $_GET['cat_id'];
    if($cat_id === 'all') {
        $products = mysqli_query($conn, "SELECT * FROM products");
    } elseif(is_numeric($cat_id)) {
        $products = mysqli_query($conn, "SELECT * FROM products WHERE category_id=$cat_id");
    } else {
        $products = [];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body  style=" background-color: #db9eda;">

<!-- Top Navigation -->
<div class="d-flex justify-content-end mb-4">
    <a href="cart.php" class="btn btn-primary me-2" style="background-color:#c25dc0; border-color:#ab1da9;">
        Cart (<?= $cart_count ?>)
    </a>
    <a href="wishlist.php" class="btn btn-warning" style="background-color:#ff9f43; border-color:#e08e0b;">
        Wishlist (<?= $wishlist_count ?>)
    </a>
</div>

<h1 style="text-align: center; padding-bottom:30px;">Welcome to Our Store</h1>

<!-- Categories -->
<div class="mb-4 text-center">
    <h3 style="padding-bottom:15px;">Categories</h3>
    <?php while($cat = mysqli_fetch_assoc($categories)): ?>
        <a href="welcome.php?cat_id=<?= $cat['id'] ?>" 
           class="btn btn-primary m-1" 
           style="background-color:#c25dc0; border-color:#ab1da9; color:white;">
           <?= $cat['name'] ?>
        </a>
    <?php endwhile; ?>
    <a href="welcome.php?cat_id=all" class="btn btn-primary m-1" style="background-color:#c25dc0; border-color:#ab1da9; color:white;">Show All</a>
</div>

<!-- Products -->
<div class="container" >
<?php if($products): ?>
    <h3 style="padding-bottom:15px; text-align: center;">Products</h3>
    <div class="row">
        <?php while($p = mysqli_fetch_assoc($products)):
            // Check if product is in cart/wishlist
            $in_cart = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id=$user_id AND product_id=".$p['id'])) > 0;
            $in_wish = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM wishlist_items WHERE user_id=$user_id AND product_id=".$p['id'])) > 0;
        ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100" style="border-radius:10px;">
                    <img src="uploads/<?= $p['image'] ?>" class="card-img-top" 
                         style="height:180px; object-fit:cover; border-top-left-radius:10px; border-top-right-radius:10px;">
                    <div class="card-body" style="font-size:16px;">
                        <h5 class="card-title"><?= $p['name'] ?></h5>
                        <p class="card-text text-muted">₹<?= $p['price'] ?></p>
                        <p class="card-text"><small><?= $p['description'] ?></small></p>
                        
                        <a href="add_to_cart.php?id=<?= $p['id'] ?>" 
                           class="btn btn-primary mb-2 <?= $in_cart ? 'disabled' : '' ?>" style="background:#c25dc0;">
                           <?= $in_cart ? 'In Cart' : 'Add to Cart' ?>
                        </a>
                        <a href="add_to_wishlist.php?id=<?= $p['id'] ?>" 
                           class="btn btn-warning mb-2 <?= $in_wish ? 'disabled' : '' ?>" style="background:#ff9f43;">
                           <?= $in_wish ? 'In Wishlist' : 'Add to Wishlist' ?>
                        </a>

                        <a href="product.php?id=<?= $p['id'] ?>" class="btn btn-info btn-sm">View Details</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php elseif(isset($_GET['cat_id'])): ?>
    <p style="text-align:center;">No products found in this category.</p>
<?php endif; ?>
</div>

</body>
</html>
