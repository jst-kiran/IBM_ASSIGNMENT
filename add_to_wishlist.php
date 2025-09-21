<?php
session_start();
require_once 'config.php';

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

// Check if product exists
$product_check = mysqli_query($conn, "SELECT * FROM products WHERE id=$product_id");
if(mysqli_num_rows($product_check) == 0){
    echo "Product not found.";
    exit;
}

// Check if already in wishlist
$exists = mysqli_query($conn, "SELECT * FROM wishlist_items WHERE user_id=$user_id AND product_id=$product_id");
if(mysqli_num_rows($exists) == 0){
    mysqli_query($conn, "INSERT INTO wishlist_items (user_id, product_id) VALUES ($user_id, $product_id)");
}

// Redirect to wishlist page to see the added product
header("Location: wishlist.php");
exit;
