<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    header("Location: ../users/login.php");
    exit;
}

$name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Provider Dashboard – OABS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <div class="card shadow p-4">
        <h2 class="text-center mb-4">Welcome, <?= htmlspecialchars($name) ?></h2>

        <ul class="list-group list-group-flush mb-4">
            <li class="list-group-item">
                <a href="update_availability.php" class="text-decoration-none">Set Availability</a>
            </li>
            <li class="list-group-item">
                <a href="view_appointments.php" class="text-decoration-none">View Appointments</a>
            </li>
            <li class="list-group-item">
                <a href="../users/logout.php" class="text-decoration-none text-danger">Logout</a>
            </li>
        </ul>

        <div class="alert alert-info text-center">
            Use the menu above to manage your services and view your bookings.
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
<script src="../assets/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
