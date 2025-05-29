<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'provider') {
    header("Location: ../users/login.php");
    exit;
}

$name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Provider Dashboard - OABS</title>
</head>
<body>
    <h2>Welcome, Provider <?= htmlspecialchars($name) ?></h2>

    <ul>
        <li><a href="update_availability.php">Set Availability</a></li>
        <!-- Optional: Add future "View Appointments" here -->
        <li><a href="../users/logout.php">Logout</a></li>
    </ul>
</body>
</html>