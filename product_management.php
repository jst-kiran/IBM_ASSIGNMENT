<?php
session_start();
require_once 'config.php';

// Admin check
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role FROM users WHERE id=".$_SESSION['user_id']));
if($user['role'] !== 'admin'){
    die("Access Denied!");
}

// Fetch categories for dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories");

// Add Product
if(isset($_POST['add'])){
    $cat = $_POST['category_id'];
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];

    $img = '';
    if(!empty($_FILES['image']['name'])){
        $img = time().'_'.$_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/$img");
    }

    mysqli_query($conn, "INSERT INTO products (category_id,name,description,price,image) VALUES ('$cat','$name','$desc','$price','$img')");
}

// Update Product
if(isset($_POST['update'])){
    $id = $_POST['id'];
    $cat = $_POST['category_id'];
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];

    if(!empty($_FILES['image']['name'])){
        $img = time().'_'.$_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/$img");
        mysqli_query($conn,"UPDATE products SET category_id='$cat', name='$name', description='$desc', price='$price', image='$img' WHERE id=$id");
    } else {
        mysqli_query($conn,"UPDATE products SET category_id='$cat', name='$name', description='$desc', price='$price' WHERE id=$id");
    }
}

// Delete Product
if(isset($_GET['delete'])){
    mysqli_query($conn,"DELETE FROM products WHERE id=".$_GET['delete']);
}

// Edit Product
$edit = null;
if(isset($_GET['edit'])){
    $edit = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM products WHERE id=".$_GET['edit']));
}

// Fetch all products
$products = mysqli_query($conn,"SELECT p.*, c.name AS cat_name FROM products p LEFT JOIN categories c ON p.category_id=c.id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="padding:30px; background:#db9eda;">
<div class="container">

    <!-- Navigation -->
    <div class="text-center mb-4">
        <a href="admin_panel.php" class="btn btn-secondary">Categories</a>
        <a href="product_management.php" class="btn btn-secondary">Products</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <h2 class="text-center mb-4">Product Management</h2>

    <!-- Add/Edit Form -->
    <form method="POST" enctype="multipart/form-data" class="mb-4 row">
        <?php if($edit) echo '<input type="hidden" name="id" value="'.$edit['id'].'">'; ?>
        <div class="col-md-3">
            <select name="category_id" class="form-control" required>
                <option value="">Select Category</option>
                <?php while($c = mysqli_fetch_assoc($categories)): ?>
                    <option value="<?= $c['id'] ?>" <?= $edit && $edit['category_id']==$c['id'] ? 'selected' : '' ?>><?= $c['name'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2"><input type="text" name="name" class="form-control" placeholder="Name" value="<?= $edit ? $edit['name'] : '' ?>" required></div>
        <div class="col-md-3"><input type="text" name="description" class="form-control" placeholder="Description" value="<?= $edit ? $edit['description'] : '' ?>" required></div>
        <div class="col-md-2"><input type="number" name="price" class="form-control" placeholder="Price" value="<?= $edit ? $edit['price'] : '' ?>" required></div>
        <div class="col-md-2"><input type="file" name="image" class="form-control"></div>
        <div class="col-12 mt-2">
            <button type="submit" name="<?= $edit ? 'update' : 'add' ?>" class="btn btn-primary"><?= $edit ? 'Update' : 'Add Product' ?></button>
        </div>
    </form>

    <!-- Products Table -->
    <table class="table table-bordered bg-white">
        <tr style="background:#c25dc0;color:white;">
            <th>ID</th><th>Category</th><th>Name</th><th>Description</th><th>Price</th><th>Image</th><th>Action</th>
        </tr>
        <?php while($p = mysqli_fetch_assoc($products)): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= $p['cat_name'] ?></td>
                <td><?= $p['name'] ?></td>
                <td><?= $p['description'] ?></td>
                <td><?= $p['price'] ?></td>
                <td><?php if($p['image']) echo '<img src="uploads/'.$p['image'].'" width="50">'; ?></td>
                <td>
                    <a href="?edit=<?= $p['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

</div>
</body>
</html>
