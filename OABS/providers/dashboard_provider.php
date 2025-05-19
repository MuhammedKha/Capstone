<?php
session_start();
if ($_SESSION['role'] !== 'provider') {
    header("Location: ../users/login.php");
    exit;
}
echo "<h2>Welcome, Provider " . $_SESSION['name'] . "</h2>";
echo "<p><a href='../users/logout.php'>Logout</a></p>";
?>