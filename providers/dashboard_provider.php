<?php
session_start();

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
    <div class="card p-5 shadow mx-auto text-center" style="max-width: 600px;">
        <h2 class="mb-4">Welcome, Provider <?= htmlspecialchars($name) ?> 👋</h2>

        <div class="d-grid gap-3">
            <a href="update_availability.php" class="btn btn-success btn-lg">📅 Set Availability</a>
            <!-- Optional future enhancement -->
            <!-- <a href="view_appointments.php" class="btn btn-info btn-lg">📋 View Appointments</a> -->
            <a href="../users/logout.php" class="btn btn-secondary btn-lg">Logout</a>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
