<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../users/login.php");
    exit;
}

$client_id = $_SESSION['user_id'];
$msg = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['appointment_id'], $_POST['slot_id'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $slot_id = intval($_POST['slot_id']);

    // Get the new slot details
    $stmtSlot = $conn->prepare("SELECT * FROM availability WHERE id = ? AND status = 'available'");
    $stmtSlot->bind_param("i", $slot_id);
    $stmtSlot->execute();
    $slot = $stmtSlot->get_result()->fetch_assoc();

    if ($slot) {
        // Get old appointment's slot details
        $stmtOld = $conn->prepare("SELECT appointment_date, start_time, end_time FROM appointments WHERE id = ? AND client_id = ?");
        $stmtOld->bind_param("ii", $appointment_id, $client_id);
        $stmtOld->execute();
        $old = $stmtOld->get_result()->fetch_assoc();

        // Update appointment with new slot
        $stmtUpdate = $conn->prepare("UPDATE appointments SET provider_id = ?, appointment_date = ?, start_time = ?, end_time = ? WHERE id = ? AND client_id = ?");
        $stmtUpdate->bind_param("isssii", $slot['provider_id'], $slot['available_date'], $slot['start_time'], $slot['end_time'], $appointment_id, $client_id);

        if ($stmtUpdate->execute()) {
            // Mark new slot as booked
            $stmtNewSlot = $conn->prepare("UPDATE availability SET status = 'booked' WHERE id = ?");
            $stmtNewSlot->bind_param("i", $slot_id);
            $stmtNewSlot->execute();

            // Mark old slot as available again (optional, only if matching availability exists)
            $stmtOldSlot = $conn->prepare("UPDATE availability SET status = 'available' WHERE available_date = ? AND start_time = ? AND end_time = ?");
            $stmtOldSlot->bind_param("sss", $old['appointment_date'], $old['start_time'], $old['end_time']);
            $stmtOldSlot->execute();

            $msg = "<div class='alert alert-success'>✅ Appointment rescheduled successfully.</div>";
        } else {
            $msg = "<div class='alert alert-danger'>❌ Failed to reschedule appointment.</div>";
        }
    } else {
        $msg = "<div class='alert alert-warning'>⚠️ Selected slot is not available.</div>";
    }
}

// Fetch current appointments for the client
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

// Fetch available slots
$slots = $conn->query("
    SELECT a.id, a.available_date, a.start_time, a.end_time, u.name AS provider_name
    FROM availability a
    JOIN users u ON a.provider_id = u.id
    WHERE a.status = 'available'
    ORDER BY a.available_date, a.start_time
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reschedule Appointment – OABS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <div class="card shadow p-4 mx-auto" style="max-width: 600px;">
        <h3 class="text-center mb-4">Reschedule Appointment</h3>

        <?= $msg ?>

        <form method="POST">
            <label class="form-label">Select Your Current Appointment:</label>
            <select name="appointment_id" class="form-select mb-3" required>
                <option value="">-- Select Appointment --</option>
                <?php while ($row = $appointments->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>">
                        <?= htmlspecialchars($row['provider_name']) ?> – <?= $row['appointment_date'] ?>
                        (<?= $row['start_time'] ?> to <?= $row['end_time'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <label class="form-label">Select New Slot:</label>
            <select name="slot_id" class="form-select mb-3" required>
                <option value="">-- Select Slot --</option>
                <?php while ($row = $slots->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>">
                        <?= htmlspecialchars($row['provider_name']) ?> – <?= $row['available_date'] ?>
                        (<?= $row['start_time'] ?> to <?= $row['end_time'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit" class="btn btn-warning w-100">Reschedule Appointment</button>
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