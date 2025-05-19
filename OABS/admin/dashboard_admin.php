<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../users/login.php");
    exit;
}
echo "<h2>Welcome, Admin " . $_SESSION['name'] . "</h2>";
echo "<p><a href='../users/logout.php'>Logout</a></p>";
?>