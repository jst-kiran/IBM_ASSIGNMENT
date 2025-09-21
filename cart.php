<?php
session_start();
require_once 'config.php';
include 'navbar.php'; 

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Update quantity if form submitted
if(isset($_POST['update'])){
    foreach($_POST['quantity'] as $product_id => $qty){
        $qty = max(1, (int)$qty); // Minimum quantity 1
        mysqli_query($conn, "UPDATE cart_items SET quantity=$qty WHERE user_id=$user_id AND product_id=$product_id");
    }
    header("Location: cart.php");
    exit;
}

// Remove item
if(isset($_GET['remove']) && is_numeric($_GET['remove'])){
    $remove_id = $_GET['remove'];
    mysqli_query($conn, "DELETE FROM cart_items WHERE user_id=$user_id AND product_id=$remove_id");
    header("Location: cart.php");
    exit;
}

// Get all cart items
$result = mysqli_query($conn, "SELECT p.*, c.quantity FROM products p 
    JOIN cart_items c ON p.id=c.product_id 
    WHERE c.user_id=$user_id");

$cart_products = [];
$total = 0;
while($row = mysqli_fetch_assoc($result)){
    $cart_products[] = $row;
    $total += $row['price'] * $row['quantity'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body  style=" background:#db9eda;">

<h1 class="text-center mb-4">Your Cart</h1>
<div class="container">
    
<?php if(count($cart_products) > 0): ?>
<form method="POST">
<table class="table table-bordered bg-white">
    <thead>
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($cart_products as $p): ?>
        <tr>
            <td><?= $p['name'] ?></td>
            <td>₹<?= $p['price'] ?></td>
            <td><input type="number" name="quantity[<?= $p['id'] ?>]" value="<?= $p['quantity'] ?>" min="1" style="width:60px;"></td>
            <td>₹<?= $p['price'] * $p['quantity'] ?></td>
            <td><a href="cart.php?remove=<?= $p['id'] ?>" class="btn btn-danger btn-sm">Remove</a></td>
        </tr>
    <?php endforeach; ?>
        <tr>
            <td colspan="3" class="text-end"><strong>Total:</strong></td>
            <td colspan="2">₹<?= $total ?></td>
        </tr>
    </tbody>
</table>
<div class="text-end mb-4">
    <button type="submit" name="update" class="btn btn-primary">Update Cart</button>
    <a href="checkout.php" class="btn btn-success">Proceed to Pay</a>
</div>
</form>
<?php else: ?>
<p class="text-center">Your cart is empty.</p>
<?php endif; ?>
</div>

</body>
</html>
