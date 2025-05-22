<?php
session_start();
require_once '../includes/config.php';

// Allow only providers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    header("Location: ../users/login.php");
    exit;
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $provider_id = $_SESSION['user_id'];
    $available_date = $_POST['available_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $stmt = $conn->prepare("INSERT INTO availability (provider_id, available_date, start_time, end_time) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $provider_id, $available_date, $start_time, $end_time);

    if ($stmt->execute()) {
        $msg = "✅ Availability added successfully.";
    } else {
        $msg = "❌ Failed to add availability.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Availability - OABS</title>
</head>
<body>
<h2>Set Your Availability</h2>
<p style="color:green;"><?php echo $msg; ?></p>
<form method="POST">
    <label>Date:</label><br>
    <input type="date" name="available_date" required><br><br>

    <label>Start Time:</label><br>
    <input type="time" name="start_time" required><br><br>

    <label>End Time:</label><br>
    <input type="time" name="end_time" required><br><br>

    <button type="submit">Save Availability</button>
</form>

<p><a href="dashboard_provider.php">Back to Dashboard</a></p>
</body>
</html>