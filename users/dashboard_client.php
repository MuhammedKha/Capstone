<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}

$name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Dashboard – OABS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <div class="card shadow p-4">
        <h2 class="mb-4 text-center">Welcome, <?= htmlspecialchars($name) ?> 👋</h2>

        <div class="row g-4">
            <div class="col-md-6">
                <a href="../appointments/book.php" class="btn btn-primary w-100 py-3">📅 Book Appointment</a>
            </div>
            <div class="col-md-6">
                <a href="../appointments/cancel.php" class="btn btn-danger w-100 py-3">❌ Cancel Appointment</a>
            </div>
            <div class="col-md-6">
                <a href="../appointments/reschedule.php" class="btn btn-warning w-100 py-3">♻️ Reschedule Appointment</a>
            </div>
            <div class="col-md-6">
                <a href="my_appointments.php" class="btn btn-info w-100 py-3">📖 View My Appointments</a>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="logout.php" class="btn btn-outline-secondary">Logout</a>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
