<?php
session_start();

// Dummy admin credentials
$adminUser = "admin";
$adminPass = "12345";

if ($_POST['username'] === $adminUser && $_POST['password'] === $adminPass) {
    $_SESSION['username'] = $adminUser;
    header("Location: dashboard.php");
    exit();
} else {
    echo "<script>alert('Invalid username or password'); window.location='index.php';</script>";
}
?>
