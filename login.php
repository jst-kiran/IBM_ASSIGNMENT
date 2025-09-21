<?php
session_start();
require_once 'config.php'; // make sure this defines $conn (MySQLi)

$emailError = $passwordError = '';
$email = $_POST['inputemail'] ?? '';

if (isset($_POST['submit'])) {
    $email = trim($_POST['inputemail'] ?? '');
    $password = $_POST['inputpassword'] ?? '';

    // basic required checks
    if (empty($email)) {
        $emailError = 'Email is required.';
    }
    if (empty($password)) {
        $passwordError = 'Password is required.';
    }

    if (empty($emailError) && empty($passwordError)) {
        $sql = 'SELECT * FROM users WHERE email = ? LIMIT 1';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $row = $result->fetch_assoc();

                if (password_verify($password, $row['password_hashed'])) {
                    // set session
                    $_SESSION['user_id']  = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['role']     = $row['role'] ?? 'user';

                    // redirect based on role
                    if ($_SESSION['role'] === 'admin') {
                        header("Location: admin_panel.php");
                    } else {
                        header("Location: welcome.php");
                    }
                    exit();
                } else {
                    $passwordError = 'Invalid password.';
                }
            } else {
                $emailError = 'Email not found.';
            }
        } else {
            $emailError = 'Database error. Try again later.';
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
     </head>
 
    <body class="container" style="padding-top: 50px; background-color: #db9eda;">
        <h1 style="text-align: center; padding-bottom:30px;">Log in</h1>

        <!-- Combined error message -->
        <?php if (!empty($emailError) || !empty($passwordError)): ?>
            <p class="text-center"><span class="text-danger"><?= htmlspecialchars($emailError . ' ' . $passwordError) ?></span></p>
        <?php endif; ?>

        <form class="row g-3" method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" style="font-size:20px ;">
            <!-- email  -->
            <div class="col-12">
                <label for="inputemail" class="form-label">Email:</label>
                <input type="email" name="inputemail" class="form-control" id="inputemail" value="<?= htmlspecialchars($email) ?>" required>
            </div>

            <!-- password  -->
            <div class="col-12">
                <label for="inputpassword" class="form-label">Password:</label>
                <input type="password" name="inputpassword" class="form-control" id="inputpassword" required>
            </div>
            
            <!-- submit  -->
            <div class="col-12 text-center mt-3">
                <button type="submit" name="submit" class="btn btn-primary" style="background-color: #c25dc0; padding:12px; font-size:20px; border-color:#ab1da9; margin-top:15px;">Submit</button>
                <p>New user? <a href="index.php" style="text-decoration: none;">sign in</a></p>
            </div>
        </form>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" integrity="sha384-7qAoOXltbVP82dhxHAUje59V5r2YsVfBafyUDxEdApLPmcdhBPg1DKg1ERo0BZlK" crossorigin="anonymous"></script>
    </body>
</html>
