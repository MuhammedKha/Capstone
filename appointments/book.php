<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../users/login.php");
    exit;
}

$client_id = $_SESSION['user_id'];
$msg = "";

// Handle form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $slot_id = $_POST['slot_id'];

    // Get slot info
    $stmtSlot = $conn->prepare("SELECT * FROM availability WHERE id = ?");
    $stmtSlot->bind_param("i", $slot_id);
    $stmtSlot->execute();
    $slot = $stmtSlot->get_result()->fetch_assoc();

    if ($slot) {
        $provider_id = $slot['provider_id'];
        $date = $slot['available_date'];
        $start = $slot['start_time'];
        $end = $slot['end_time'];

        $stmt = $conn->prepare("INSERT INTO appointments (client_id, provider_id, appointment_date, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $client_id, $provider_id, $date, $start, $end);

        if ($stmt->execute()) {
            $msg = "✅ Appointment booked successfully.";
        } else {
            $msg = "❌ Booking failed.";
        }
    } else {
        $msg = "❌ Invalid slot selected.";
    }
}

// Fetch available slots
$slots = $conn->query("
    SELECT a.id, a.available_date, a.start_time, a.end_time, u.name AS provider_name
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
    <label>Select Available Slot:</label><br>
    <select name="slot_id" required>
        <option value="">-- Select --</option>
        <?php while ($row = $slots->fetch_assoc()) { ?>
            <option value="<?= $row['id'] ?>">
                <?= htmlspecialchars($row['provider_name']) ?> - <?= $row['available_date'] ?> (<?= $row['start_time'] ?> to <?= $row['end_time'] ?>)
            </option>
        <?php } ?>
    </select><br><br>

    <button type="submit">Book Now</button>
</form>

<p><a href="../users/dashboard_client.php">Back to Dashboard</a></p>
</body>
</html>