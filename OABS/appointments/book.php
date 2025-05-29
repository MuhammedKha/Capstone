<?php
session_start();
require_once '../includes/config.php';

// 🔒 Only clients can book
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../users/login.php");
    exit;
}

$client_id = $_SESSION['user_id'];
$msg = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $provider_id = $_POST['provider_id'];
    $appointment_date = $_POST['appointment_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Insert into appointments
    $stmt = $conn->prepare("INSERT INTO appointments (client_id, provider_id, appointment_date, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $client_id, $provider_id, $appointment_date, $start_time, $end_time);

    if ($stmt->execute()) {
        $msg = "✅ Appointment booked successfully.";
    } else {
        $msg = "❌ Booking failed. Please try again.";
    }
}

// Get available slots
$slots = $conn->query("
    SELECT a.*, u.name AS provider_name
    FROM availability a
    JOIN users u ON u.id = a.provider_id
    ORDER BY a.available_date, a.start_time
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Appointment - OABS</title>
</head>
<body>
<h2>Book an Appointment</h2>
<p style="color:green;"><?php echo $msg; ?></p>

<form method="POST">
    <label>Choose Slot:</label><br>
    <select name="provider_id" required>
        <option value="">-- Select --</option>
        <?php while ($row = $slots->fetch_assoc()) { ?>
            <option value="<?= $row['provider_id'] ?>">
                <?= $row['provider_name'] ?> - <?= $row['available_date'] ?> (<?= $row['start_time'] ?> to <?= $row['end_time'] ?>)
            </option>
        <?php } ?>
    </select><br><br>

    <label>Appointment Date:</label><br>
    <input type="date" name="appointment_date" required><br><br>

    <label>Start Time:</label><br>
    <input type="time" name="start_time" required><br><br>

    <label>End Time:</label><br>
    <input type="time" name="end_time" required><br><br>

    <button type="submit">Book Now</button>
</form>

<p><a href="../users/dashboard_client.php">Back to Dashboard</a></p>
</body>
</html>