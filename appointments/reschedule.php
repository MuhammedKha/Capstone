<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../users/login.php");
    exit;
}

$client_id = $_SESSION['user_id'];
$msg = "";

// Handle reschedule
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $appointment_id = $_POST['appointment_id'];
    $new_slot_id = $_POST['slot_id'];

    // Get new slot info
    $slotStmt = $conn->prepare("SELECT * FROM availability WHERE id = ?");
    $slotStmt->bind_param("i", $new_slot_id);
    $slotStmt->execute();
    $new_slot = $slotStmt->get_result()->fetch_assoc();

    if ($new_slot) {
        $provider_id = $new_slot['provider_id'];
        $date = $new_slot['available_date'];
        $start = $new_slot['start_time'];
        $end = $new_slot['end_time'];

        $updateStmt = $conn->prepare("
            UPDATE appointments 
            SET provider_id = ?, appointment_date = ?, start_time = ?, end_time = ?, status = 'rescheduled' 
            WHERE id = ? AND client_id = ?
        ");
        $updateStmt->bind_param("isssii", $provider_id, $date, $start, $end, $appointment_id, $client_id);

        if ($updateStmt->execute()) {
            $msg = "✅ Appointment rescheduled.";
        } else {
            $msg = "❌ Rescheduling failed.";
        }
    } else {
        $msg = "❌ Invalid slot.";
    }
}

// Current appointments
$apptStmt = $conn->prepare("
    SELECT a.id, u.name AS provider_name, a.appointment_date, a.start_time, a.end_time 
    FROM appointments a
    JOIN users u ON a.provider_id = u.id
    WHERE a.client_id = ? AND a.status = 'scheduled'
");
$apptStmt->bind_param("i", $client_id);
$apptStmt->execute();
$appointments = $apptStmt->get_result();

// Available slots
$slots = $conn->query("
    SELECT a.id, a.available_date, a.start_time, a.end_time, u.name AS provider_name
    FROM availability a
    JOIN users u ON u.id = a.provider_id
    ORDER BY a.available_date, a.start_time
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reschedule Appointment - OABS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <h2 class="mb-4 text-center">Reschedule Appointment</h2>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-info"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="appointment_id" class="form-label">Select Your Current Appointment:</label>
            <select name="appointment_id" id="appointment_id" class="form-select" required>
                <option value="">-- Select Appointment --</option>
                <?php while ($row = $appointments->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>">
                        <?= htmlspecialchars($row['provider_name']) ?> - <?= $row['appointment_date'] ?> (<?= $row['start_time'] ?> to <?= $row['end_time'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="slot_id" class="form-label">Select New Slot:</label>
            <select name="slot_id" id="slot_id" class="form-select" required>
                <option value="">-- Select Slot --</option>
                <?php while ($row = $slots->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>">
                        <?= htmlspecialchars($row['provider_name']) ?> - <?= $row['available_date'] ?> (<?= $row['start_time'] ?> to <?= $row['end_time'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-warning">Reschedule Appointment</button>
    </form>

    <p class="mt-3"><a href="../users/dashboard_client.php">Back to Dashboard</a></p>
</div>

<?php include '../templates/footer.php'; ?>
</body>
</html>