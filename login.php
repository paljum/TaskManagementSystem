<?php
include 'db.php';


if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT id, username, password, role FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id']; 
            $_SESSION['user'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "User not found";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css?v=1.1">
    <title>Login</title>
</head>
<body class="auth-wrapper">

<div class="container">
    <h2>Login</h2>
    <p>Welcome back! Please enter your details.</p>

    <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button name="login">Login</button>
    </form>

    <a href="register.php">Don't have an account? Register</a>
</div>

</body>
</html>