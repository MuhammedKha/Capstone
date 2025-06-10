<?php
// Set timezone to Melbourne
date_default_timezone_set('Australia/Melbourne');

// Start session and include config
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../users/login.php");
    exit;
}

$name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - OABS</title>
</head>
<body>
    <h2>Welcome, Admin <?= htmlspecialchars($name) ?></h2>

    <ul>
        <li><a href="../admin/view_appointments.php">View All Appointments</a></li>
        <!-- Optional: Add future admin tools here -->
        <li><a href="../users/logout.php">Logout</a></li>
    </ul>
</body>
</html>
