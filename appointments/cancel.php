<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../users/login.php");
    exit;
}

$client_id = $_SESSION['user_id'];
$msg = "";

// Handle cancellation form
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['appointment_id'])) {
    $appointment_id = intval($_POST['appointment_id']);

    // Get slot details from appointment
    $stmt = $conn->prepare("SELECT appointment_date, start_time, end_time FROM appointments WHERE id = ? AND client_id = ?");
    $stmt->bind_param("ii", $appointment_id, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $appt = $result->fetch_assoc();

    if ($appt) {
        // Cancel appointment
        $stmtCancel = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND client_id = ?");
        $stmtCancel->bind_param("ii", $appointment_id, $client_id);
        $stmtCancel->execute();

        // Free the availability slot
        $stmtAvail = $conn->prepare("UPDATE availability SET status = 'available' WHERE available_date = ? AND start_time = ? AND end_time = ?");
        $stmtAvail->bind_param("sss", $appt['appointment_date'], $appt['start_time'], $appt['end_time']);
        $stmtAvail->execute();

        $msg = "<div class='alert alert-success'>✅ Appointment cancelled successfully.</div>";
    } else {
        $msg = "<div class='alert alert-warning'>⚠️ Invalid appointment selected.</div>";
    }
}

// Fetch active appointments
$stmt = $conn->prepare("
    SELECT a.id, u.name AS provider_name, a.appointment_date, a.start_time, a.end_time
    FROM appointments a
    JOIN users u ON a.provider_id = u.id
    WHERE a.client_id = ? AND a.status = 'booked'
    ORDER BY a.appointment_date DESC
");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$appointments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cancel Appointment – OABS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <div class="card p-4 shadow mx-auto" style="max-width: 600px;">
        <h3 class="text-center mb-4">Cancel Appointment</h3>

        <?= $msg ?>

        <form method="POST">
            <label for="appointment_id" class="form-label">Select Appointment to Cancel:</label>
            <select name="appointment_id" id="appointment_id" class="form-select mb-3" required>
                <option value="">-- Select --</option>
                <?php while ($row = $appointments->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>">
                        <?= htmlspecialchars($row['provider_name']) ?> – <?= $row['appointment_date'] ?>
                        (<?= $row['start_time'] ?> to <?= $row['end_time'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit" class="btn btn-danger w-100">Cancel Appointment</button>
        </form>

        <div class="text-center mt-3">
            <a href="../users/dashboard_client.php">← Back to Dashboard</a>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>