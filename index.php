<?php
require_once 'config.php';

$username = $_POST['username'] ?? '';
$email = $_POST['inputemail'] ?? '';
$password = $_POST['inputpassword'] ?? '';
$confirm_password = $_POST['confirmpassword'] ?? '';
$message = '';

$usernameError = $emailError = $passwordError = $confirm_passwordError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation
    if (!strlen($username)) {
        $usernameError = "Username is required!";
    } else if (!preg_match('/^[a-zA-Z0-9_-]{4,10}$/', $username)) {
        $usernameError = "Username must be 4-10 characters long and contain only letters, numbers, underscores, or hyphens.";
    }

    if (!strlen($email)) {
        $emailError = "Email is required.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailError = "Email format is invalid.";
    }

    if (!strlen($password)) {
        $passwordError = "Password is required.";
    } else if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[_@])[A-Za-z0-9_@]{8,}$/', $password)) {
        $passwordError = 'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and only underscores or @ symbols as special characters.';
    }

    if (!strlen($confirm_password)) {
        $confirm_passwordError = 'Please confirm your password.';
    } elseif ($password !== $confirm_password) {
        $confirm_passwordError = 'Passwords do not match.';
    }

    // ✅ If no errors, insert into database
    if (!$usernameError && !$emailError && !$passwordError && !$confirm_passwordError) {
        $hashedPassword = password_hash($confirm_password, PASSWORD_DEFAULT);
        $role = "user"; // 👈 everyone registering via this form is a normal user

        $sql = "INSERT INTO users (username, email, password_hashed, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssss', $username, $email, $hashedPassword, $role);

        if ($stmt->execute()) {
            $message = "User registered successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>


<html>
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
     </head>
 
    <body class="container" style="padding-top: 50px; background-color: #db9eda;">
        <h1 style="text-align: center; padding-bottom:30px;">Sign into the website</h1>
            <?php if (!empty($message)): ?>
                <div style="margin-bottom: 15px; padding: 10px; background-color: #d4edda; color: #155724; border-radius: 5px;">
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>
            <form class="row g-3" method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" style="font-size:20px ;">
    <!-- username  -->
    <div class="col-md-6">
        <label for="username" class="form-label">Username:</label>
        <input type="text" name="username" class="form-control" id="username" 
               value="<?= htmlspecialchars($username) ?>">
        <span class="text-danger"><?= $usernameError; ?></span>
    </div>

    <!-- email  -->
    <div class="col-md-6">
        <label for="inputemail" class="form-label">Email:</label>
        <input type="email" name="inputemail" class="form-control" id="inputemail" 
               value="<?= htmlspecialchars($email) ?>">
        <span class="text-danger"><?= $emailError; ?></span>
    </div>

    <!-- password  -->
    <div class="col-12">
        <label for="inputpassword" class="form-label">Password:</label>
        <input type="password" name="inputpassword" class="form-control" id="inputpassword">
        <span class="text-danger"><?= $passwordError; ?></span>
    </div>

    <!-- confirm password  -->
    <div class="col-12">
        <label for="confirmpassword" class="form-label">Confirm Password:</label>
        <input type="password" name="confirmpassword" class="form-control" id="confirmpassword">
        <span class="text-danger"><?= $confirm_passwordError; ?></span>
    </div>

    <!-- submit  -->
    <div class="col-12 text-center mt-3">
        <button type="submit" name="submit" class="btn btn-primary" 
                style="background-color: #c25dc0; padding:12px; font-size:20px; border-color:#ab1da9; margin-top:15px;">
            Submit
        </button>
        <p>Already have account? <a href="login.php" style="text-decoration: none;">Login</a></p>
    </div>
</form>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    </body>
</html>
