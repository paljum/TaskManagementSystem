<?php
include 'db.php';


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_POST['register'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username === "" || $password === "") {
        $error = "All fields are required";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user';

        $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $username, $hash, $role);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: login.php");
            exit();
        } else {
            $error = "Registration failed. Username might be taken.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css?v=1.1">
    <title>Register</title>
</head>
<body class="auth-wrapper">
<div class="container">
    <h2>Register</h2>
    <?php if (isset($error)) echo "<p style='color:red; text-align:center;'>$error</p>"; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="text" name="username" id="username" placeholder="Username" required>
        <div id="user_status" style="font-size: 0.8rem; margin-bottom: 10px;"></div>
        <input type="password" name="password" placeholder="Password" required>
        <button name="register" style="width:100%;">Register</button>
    </form>

    <div style="margin-top:20px; text-align:center;">
        <a href="login.php" style="color: var(--emerald-bright); text-decoration: none;">Already have an account? Login</a>
    </div>
</div>

<script>
document.getElementById('username').addEventListener('input', function() {
    let u = this.value;
    let status = document.getElementById('user_status');
    if (u.length > 2) {
        fetch('db.php?check_user=' + encodeURIComponent(u))
            .then(res => res.text())
            .then(data => {
                if (data === "taken") {
                    status.innerHTML = "Username already exists";
                    status.style.color = "#fca5a5";
                } else {
                    status.innerHTML = "Username available";
                    status.style.color = "#10b981";
                }
            });
    } else {
        status.innerHTML = "";
    }
});
</script>
</body>
</html>