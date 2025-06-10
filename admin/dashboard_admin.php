<?php
session_start();
date_default_timezone_set('Australia/Melbourne');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../users/login.php");
    exit;
}

$name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard – OABS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <div class="card p-4 shadow">
        <h2 class="mb-4 text-center">Welcome, Admin <?= htmlspecialchars($name) ?></h2>

        <div class="d-grid gap-3">
            <a href="manage_users.php" class="btn btn-primary">Manage Users (Approve/Delete)</a>
            <a href="view_all_appointments.php" class="btn btn-secondary">View All Appointments</a>
            <a href="cancel_appointment_admin.php" class="btn btn-outline-danger w-100 my-2">Cancel Appointments</a>
            <a href="../users/logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
