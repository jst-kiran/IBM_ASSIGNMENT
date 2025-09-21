<?php
session_start();
require_once 'config.php';
// include 'navbar.php'; 

// Admin check
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role FROM users WHERE id=".$_SESSION['user_id']));
if($user['role'] !== 'admin'){
    die("Access Denied!");
}

// --- CATEGORY MANAGEMENT ---
if(isset($_POST['add_cat'])){
    $name = $_POST['cat_name'];
    if($name) mysqli_query($conn,"INSERT INTO categories(name) VALUES('$name')");
}
if(isset($_POST['update_cat'])){
    $id = $_POST['cat_id'];
    $name = $_POST['cat_name'];
    if($name) mysqli_query($conn,"UPDATE categories SET name='$name' WHERE id=$id");
}
if(isset($_GET['delete_cat'])){
    mysqli_query($conn,"DELETE FROM categories WHERE id=".$_GET['delete_cat']);
}
$categories = mysqli_query($conn,"SELECT * FROM categories");

// --- PRODUCT MANAGEMENT ---
if(isset($_POST['add_prod'])){
    $cat = $_POST['prod_cat'];
    $name = $_POST['prod_name'];
    $desc = $_POST['prod_desc'];
    $price = $_POST['prod_price'];
    $img = '';
    if(!empty($_FILES['prod_img']['name'])){
        $img = time().'_'.$_FILES['prod_img']['name'];
        move_uploaded_file($_FILES['prod_img']['tmp_name'],"uploads/$img");
    }
    mysqli_query($conn,"INSERT INTO products(category_id,name,description,price,image) VALUES('$cat','$name','$desc','$price','$img')");
}
if(isset($_POST['update_prod'])){
    $id = $_POST['prod_id'];
    $cat = $_POST['prod_cat'];
    $name = $_POST['prod_name'];
    $desc = $_POST['prod_desc'];
    $price = $_POST['prod_price'];
    if(!empty($_FILES['prod_img']['name'])){
        $img = time().'_'.$_FILES['prod_img']['name'];
        move_uploaded_file($_FILES['prod_img']['tmp_name'],"uploads/$img");
        mysqli_query($conn,"UPDATE products SET category_id='$cat',name='$name',description='$desc',price='$price',image='$img' WHERE id=$id");
    } else {
        mysqli_query($conn,"UPDATE products SET category_id='$cat',name='$name',description='$desc',price='$price' WHERE id=$id");
    }
}
if(isset($_GET['delete_prod'])){
    mysqli_query($conn,"DELETE FROM products WHERE id=".$_GET['delete_prod']);
}
$products = mysqli_query($conn,"SELECT p.*, c.name AS cat_name FROM products p LEFT JOIN categories c ON p.category_id=c.id");

// --- Edit category/product ---
$edit_cat = isset($_GET['edit_cat']) ? mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM categories WHERE id=".$_GET['edit_cat'])) : null;
$edit_prod = isset($_GET['edit_prod']) ? mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM products WHERE id=".$_GET['edit_prod'])) : null;

// --- ORDER MANAGEMENT ---
if(isset($_POST['update_order_status'])){
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    mysqli_query($conn,"UPDATE orders SET status='$status' WHERE id=$order_id");
}

$orders = mysqli_query($conn,"SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="padding:30px; background:#db9eda;">
<div>

    <!-- Navigation -->
    <div class="text-center mb-4">
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <h2 class="text-center mb-4">Admin Dashboard</h2>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" id="cat-tab" data-bs-toggle="tab" data-bs-target="#categories">Categories</button></li>
        <li class="nav-item"><button class="nav-link" id="prod-tab" data-bs-toggle="tab" data-bs-target="#products">Products</button></li>
        <li class="nav-item"><button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders">Orders</button></li>
    </ul>

    <div class="tab-content">
        <!-- CATEGORY TAB -->
        <div class="tab-pane fade show active" id="categories">
            <!-- Add/Edit Category -->
            <form method="POST" class="mb-3 row">
                <?php if($edit_cat) echo '<input type="hidden" name="cat_id" value="'.$edit_cat['id'].'">'; ?>
                <div class="col-md-6">
                    <input type="text" name="cat_name" class="form-control" placeholder="Category Name" value="<?= $edit_cat ? $edit_cat['name'] : '' ?>" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="<?= $edit_cat ? 'update_cat' : 'add_cat' ?>" class="btn btn-primary"><?= $edit_cat ? 'Update' : 'Add' ?></button>
                </div>
            </form>

            <!-- Category List -->
            <table class="table table-bordered bg-white">
                <tr style="background:#c25dc0;color:white;"><th>ID</th><th>Name</th><th>Action</th></tr>
                <?php while($c = mysqli_fetch_assoc($categories)): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><?= $c['name'] ?></td>
                        <td>
                            <a href="?edit_cat=<?= $c['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?delete_cat=<?= $c['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- PRODUCT TAB -->
        <div class="tab-pane fade" id="products">
            <!-- Add/Edit Product -->
            <form method="POST" enctype="multipart/form-data" class="mb-3 row">
                <?php if($edit_prod) echo '<input type="hidden" name="prod_id" value="'.$edit_prod['id'].'">'; ?>
                <div class="col-md-2">
                    <select name="prod_cat" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php
                        mysqli_data_seek($categories,0); // reset pointer
                        while($c=mysqli_fetch_assoc($categories)): ?>
                            <option value="<?= $c['id'] ?>" <?= $edit_prod && $edit_prod['category_id']==$c['id'] ? 'selected':'' ?>><?= $c['name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2"><input type="text" name="prod_name" class="form-control" placeholder="Name" value="<?= $edit_prod ? $edit_prod['name'] : '' ?>" required></div>
                <div class="col-md-3"><input type="text" name="prod_desc" class="form-control" placeholder="Description" value="<?= $edit_prod ? $edit_prod['description'] : '' ?>" required></div>
                <div class="col-md-2"><input type="number" name="prod_price" class="form-control" placeholder="Price" value="<?= $edit_prod ? $edit_prod['price'] : '' ?>" required></div>
                <div class="col-md-2"><input type="file" name="prod_img" class="form-control"></div>
                <div class="col-md-1"><button type="submit" name="<?= $edit_prod ? 'update_prod':'add_prod' ?>" class="btn btn-primary"><?= $edit_prod ? 'Update':'Add' ?></button></div>
            </form>

            <!-- Product List -->
            <table class="table table-bordered bg-white">
                <tr style="background:#c25dc0;color:white;"><th>ID</th><th>Category</th><th>Name</th><th>Description</th><th>Price</th><th>Image</th><th>Action</th></tr>
                <?php while($p=mysqli_fetch_assoc($products)): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td><?= $p['cat_name'] ?></td>
                        <td><?= $p['name'] ?></td>
                        <td><?= $p['description'] ?></td>
                        <td><?= $p['price'] ?></td>
                        <td><?php if($p['image']) echo '<img src="uploads/'.$p['image'].'" width="50">'; ?></td>
                        <td>
                            <a href="?edit_prod=<?= $p['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?delete_prod=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- ORDERS TAB -->
        <div class="tab-pane fade" id="orders">
            <h4>Orders</h4>
            <table class="table table-bordered bg-white">
                <tr style="background:#c25dc0;color:white;">
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Total Price</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Update Status</th>
                </tr>
                <?php while($o=mysqli_fetch_assoc($orders)): ?>
                    <tr>
                        <td><?= $o['id'] ?></td>
                        <td><?= htmlspecialchars($o['username']) ?></td>
                        <td>₹<?= $o['total_price'] ?></td>
                        <td><?= htmlspecialchars($o['payment_method']) ?></td>
                        <td><?= htmlspecialchars($o['status']) ?></td>
                        <td><?= $o['created_at'] ?></td>
                        <td>
                            <form method="POST" class="d-flex">
                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                <select name="status" class="form-select me-2">
                                    <option value="Pending" <?= $o['status']=='Pending'?'selected':'' ?>>Pending</option>
                                    <option value="Processing" <?= $o['status']=='Processing'?'selected':'' ?>>Processing</option>
                                    <option value="Completed" <?= $o['status']=='Completed'?'selected':'' ?>>Completed</option>
                                    <option value="Cancelled" <?= $o['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_order_status" class="btn btn-primary btn-sm">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
