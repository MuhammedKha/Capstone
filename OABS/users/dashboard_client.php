<?php
session_start();
if ($_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}
echo "<h2>Welcome, Client " . $_SESSION['name'] . "</h2>";
echo "<p><a href='logout.php'>Logout</a></p>";
?>