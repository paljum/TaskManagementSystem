<?php
session_start(); 
$host = "localhost";
$user = "root";//np03cy4a240057
$pass = "";//R81xKg6Y2O
$dbname = "task_db";

// $host = "localhost";
// $user = " ";
// $pass = " ";
// $dbname = "task_db";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_GET['check_user'])) {
    $u = $_GET['check_user'];
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $u);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    echo mysqli_stmt_num_rows($stmt) > 0 ? "taken" : "available";
    exit();
}
?>