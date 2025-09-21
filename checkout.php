<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch cart items with product details
$cart_items = [];
$total_price = 0;
$result = mysqli_query($conn, "SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.name, p.price 
                               FROM cart_items c 
                               JOIN products p ON c.product_id = p.id
                               WHERE c.user_id=$user_id");
while($row = mysqli_fetch_assoc($result)){
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $total_price += $row['subtotal'];
    $cart_items[] = $row;
}

// Handle form submission
$message = '';
if(isset($_POST['place_order'])){
    $shipping_address = mysqli_real_escape_string($conn, $_POST['shipping_address'] ?? '');
    $payment_method = 'Cash on Delivery';
    $status = 'Pending';
    $created_at = date('Y-m-d H:i:s');

    // Insert order
    $sql = "INSERT INTO orders (user_id, total_price, address, payment_method, status, created_at)
            VALUES ($user_id, $total_price, '$shipping_address', '$payment_method', '$status', '$created_at')";
    if(mysqli_query($conn, $sql)){
        $order_id = mysqli_insert_id($conn);

        // Insert each cart item into order_items table
        foreach($cart_items as $item){
            mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, quantity, price)
                                VALUES ($order_id, {$item['product_id']}, {$item['quantity']}, {$item['price']})");
        }

        // Clear user's cart
        mysqli_query($conn, "DELETE FROM cart_items WHERE user_id=$user_id");

        $message = "Order placed successfully! ";
        $cart_items = [];
        $total_price = 0;
    } else {
        $message = "Error placing order: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout - MyStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container" style="padding-top:50px; background:#db9eda;">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
  <a class="navbar-brand" href="welcome.php">MyStore</a>
  <div class="collapse navbar-collapse">
    <ul class="navbar-nav ms-auto">
      <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
      <li class="nav-item"><a class="nav-link" href="wishlist.php">Wishlist</a></li>
      <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
    </ul>
  </div>
</nav>

<h2 class="text-center mb-4">Checkout</h2>

<?php if($message): ?>
    <div class="alert alert-success text-center"><?= $message ?></div>
<?php endif; ?>

<?php if(count($cart_items) > 0): ?>
<form method="POST">
    <h4>Cart Summary</h4>
    <table class="table table-bordered bg-white">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($cart_items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td>₹<?= $item['price'] ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>₹<?= $item['subtotal'] ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                <td><strong>₹<?= $total_price ?></strong></td>
            </tr>
        </tbody>
    </table>

    <h4>Shipping Information</h4>
    <textarea name="shipping_address" class="form-control mb-3" placeholder="Enter your shipping address"></textarea>

    <h4>Payment Method</h4>
    <p>Cash on Delivery</p>

    <div class="text-center">
        <button type="submit" name="place_order" class="btn btn-success" style="padding:12px 30px; font-size:18px;">Place Order</button>
    </div>
</form>
<?php else: ?>
    <p class="text-center">Your cart is empty. <a href="welcome.php">Go shopping</a></p>
<?php endif; ?>

</body>
</html>
