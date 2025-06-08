<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    header("Location: ../users/login.php");
    exit;
}

$provider_id = $_SESSION['user_id'];
$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date = $_POST['available_date'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $service = $_POST['service_name'];
    $desc = $_POST['service_description'];

    $stmt = $conn->prepare("INSERT INTO availability (provider_id, service_name, service_description, available_date, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $provider_id, $service, $desc, $date, $start, $end);

    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success'>✅ Availability added successfully.</div>";
    } else {
        $msg = "<div class='alert alert-danger'>❌ Failed to add availability.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set Availability – OABS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <div class="card p-4 shadow mx-auto" style="max-width: 600px;">
        <h3 class="mb-4 text-center">Set Your Availability</h3>
        <?= $msg ?>
        <form method="POST">
            <div class="mb-3">
                <label for="service_name" class="form-label">Service Name</label>
                <input type="text" class="form-control" name="service_name" required>
            </div>

            <div class="mb-3">
                <label for="service_description" class="form-label">Short Description</label>
                <textarea class="form-control" name="service_description" rows="3" required></textarea>
            </div>

            <div class="mb-3">
                <label for="available_date" class="form-label">Date</label>
                <input type="date" class="form-control" name="available_date" required>
            </div>

            <div class="mb-3">
                <label for="start_time" class="form-label">Start Time</label>
                <input type="time" class="form-control" name="start_time" required>
            </div>

            <div class="mb-3">
                <label for="end_time" class="form-label">End Time</label>
                <input type="time" class="form-control" name="end_time" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Save Availability</button>
        </form>

        <div class="text-center mt-3">
            <a href="dashboard_provider.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
