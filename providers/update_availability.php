<?php
// Set timezone to Melbourne
date_default_timezone_set('Australia/Melbourne');

// Start session and include config
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    header("Location: ../users/login.php");
    exit;
}

$provider_id = $_SESSION['user_id'];
$msg = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $available_date = $_POST['available_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $service_name = trim($_POST['service_name']);
    $description = trim($_POST['description']);

    if ($available_date && $start_time && $end_time && $service_name) {
        $stmt = $conn->prepare("INSERT INTO availability (provider_id, available_date, start_time, end_time, service_name, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $provider_id, $available_date, $start_time, $end_time, $service_name, $description);

        if ($stmt->execute()) {
            $msg = "<div class='alert alert-success'>✅ Availability added successfully.</div>";
        } else {
            $msg = "<div class='alert alert-danger'>❌ Failed to add availability.</div>";
        }
    } else {
        $msg = "<div class='alert alert-warning'>⚠️ Please fill in all required fields.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set Availability – OABS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <div class="card shadow p-4 mx-auto" style="max-width: 600px;">
        <h3 class="text-center mb-4">Set Your Availability</h3>
        <?= $msg ?>
        <form method="POST">
            <div class="mb-3">
                <label for="available_date" class="form-label">Date:</label>
                <input type="date" name="available_date" id="available_date" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="start_time" class="form-label">Start Time:</label>
                <input type="time" name="start_time" id="start_time" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="end_time" class="form-label">End Time:</label>
                <input type="time" name="end_time" id="end_time" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="service_name" class="form-label">Service Name:</label>
                <input type="text" name="service_name" id="service_name" class="form-control" placeholder="e.g., Haircut, Massage" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Short Description:</label>
                <textarea name="description" id="description" class="form-control" placeholder="Brief details about this service (optional)" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100">Add Availability</button>
        </form>

        <div class="text-center mt-3">
            <a href="dashboard_provider.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
<script src="../assets/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
