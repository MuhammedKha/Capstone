<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}

$name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Client Dashboard - OABS</title>
</head>
<body>
    <h2>Welcome, Client <?= htmlspecialchars($name) ?></h2>

    <ul>
        <li><a href="../appointments/book.php">Book Appointment</a></li>
        <li><a href="my_appointments.php">My Appointments</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</body>
</html>