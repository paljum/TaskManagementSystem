<?php
include 'db.php'; 

if (isset($_POST['admin_login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT id, username, password, role FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            if ($row['role'] === 'admin') {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                header("Location: index.php");
                exit();
            } else {
                $error = "Access Denied: You are not an Admin.";
            }
        } else {
            $error = "Wrong password.";
        }
    } else {
        $error = "Admin user not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css?v=1.1">
    <title>Admin Login</title>
</head>
<body class="auth-wrapper">
<div class="container">
    <h2>Admin Portal</h2>
    <p>Secure login for administrators only.</p>

    <?php if (isset($error)) echo "<p style='color:red; background:#fee; padding:10px;'>$error</p>"; ?>

    <form method="POST" action="admin_login.php">
        <input type="text" name="username" placeholder="Admin Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="admin_login">LOGIN AS ADMIN</button>
    </form>

    <a href="login.php">Regular User Login</a>
</div>
</body>
</html>