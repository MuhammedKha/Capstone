<?php
date_default_timezone_set('Australia/Melbourne');
session_start();
require_once '../includes/config.php';
require_once '../includes/mail_helper.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../users/login.php");
    exit;
}

$client_id = $_SESSION['user_id'];
$msg = "";

// Rescheduling logic
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['appointment_id'], $_POST['new_slot_id'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $new_slot_id = intval($_POST['new_slot_id']);

    $stmtAppt = $conn->prepare("SELECT * FROM appointments WHERE id = ? AND client_id = ?");
    $stmtAppt->bind_param("ii", $appointment_id, $client_id);
    $stmtAppt->execute();
    $appt = $stmtAppt->get_result()->fetch_assoc();

    $stmtSlot = $conn->prepare("
        SELECT a.*, u.email AS provider_email, u.name AS provider_name 
        FROM availability a 
        JOIN users u ON a.provider_id = u.id 
        WHERE a.id = ? AND a.status = 'available'
    ");
    $stmtSlot->bind_param("i", $new_slot_id);
    $stmtSlot->execute();
    $slot = $stmtSlot->get_result()->fetch_assoc();

    if ($appt && $slot) {
        if ($appt['provider_id'] === $slot['provider_id']) {
            $stmtUpdate = $conn->prepare("
                UPDATE appointments 
                SET appointment_date = ?, start_time = ?, end_time = ? 
                WHERE id = ? AND client_id = ?
            ");
            $stmtUpdate->bind_param("sssii", $slot['available_date'], $slot['start_time'], $slot['end_time'], $appointment_id, $client_id);

            if ($stmtUpdate->execute()) {
                $stmtFreeOld = $conn->prepare("
                    UPDATE availability SET status = 'available' 
                    WHERE provider_id = ? AND available_date = ? AND start_time = ? AND end_time = ?
                ");
                $stmtFreeOld->bind_param("isss", $appt['provider_id'], $appt['appointment_date'], $appt['start_time'], $appt['end_time']);
                $stmtFreeOld->execute();

                $stmtBookNew = $conn->prepare("UPDATE availability SET status = 'booked' WHERE id = ?");
                $stmtBookNew->bind_param("i", $new_slot_id);
                $stmtBookNew->execute();

                $stmtClient = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
                $stmtClient->bind_param("i", $client_id);
                $stmtClient->execute();
                $client = $stmtClient->get_result()->fetch_assoc();

                $subject = "Appointment Rescheduled – OABS";
                $bodyClient = "
                    <p>Dear {$client['name']},</p>
                    <p>Your appointment with {$slot['provider_name']} has been <strong>rescheduled</strong> to:</p>
                    <ul>
                        <li><strong>Date:</strong> {$slot['available_date']}</li>
                        <li><strong>Time:</strong> {$slot['start_time']} to {$slot['end_time']}</li>
                    </ul>
                    <p>Thank you for using OABS.</p>
                ";

                sendMail($client['email'], $subject, $bodyClient);

                sendMail($slot['provider_email'], "Client Rescheduled Appointment – OABS", "
                    <p>Your client {$client['name']} has rescheduled their appointment to:</p>
                    <ul>
                        <li><strong>Date:</strong> {$slot['available_date']}</li>
                        <li><strong>Time:</strong> {$slot['start_time']} to {$slot['end_time']}</li>
                    </ul>
                ");

                // ADMIN email alert
                sendMail("azz37447@my.holmes.edu.au", "Appointment Rescheduled (Admin Notification)", "
                    <p><strong>Reschedule Alert</strong></p>
                    <ul>
                        <li><strong>Client:</strong> {$client['name']} ({$client['email']})</li>
                        <li><strong>Provider:</strong> {$slot['provider_name']}</li>
                        <li><strong>New Date:</strong> {$slot['available_date']}</li>
                        <li><strong>Time:</strong> {$slot['start_time']} to {$slot['end_time']}</li>
                    </ul>
                ");

                $msg = "<div class='alert alert-success'>✅ Appointment rescheduled and email notifications sent.</div>";
            } else {
                $msg = "<div class='alert alert-danger'>❌ Failed to reschedule appointment.</div>";
            }
        } else {
            $msg = "<div class='alert alert-warning'>⚠️ You can only reschedule with the same provider.</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger'>❌ Invalid appointment or slot.</div>";
    }
}

// Fetch upcoming appointments
$stmtAppointments = $conn->prepare("
    SELECT a.id, a.appointment_date, a.start_time, a.end_time, a.provider_id, u.name AS provider_name
    FROM appointments a
    JOIN users u ON u.id = a.provider_id
    WHERE a.client_id = ? AND a.status = 'booked'
    ORDER BY a.appointment_date DESC
");
$stmtAppointments->bind_param("i", $client_id);
$stmtAppointments->execute();
$appointments = $stmtAppointments->get_result();

// Fetch available future slots
$all_slots_raw = $conn->query("
    SELECT a.id, a.available_date, a.start_time, a.end_time, a.provider_id, u.name AS provider_name
    FROM availability a
    JOIN users u ON u.id = a.provider_id
    WHERE a.status = 'available'
    ORDER BY a.available_date, a.start_time
");

$slots = [];
$now = new DateTime();
while ($row = $all_slots_raw->fetch_assoc()) {
    $slotDT = new DateTime($row['available_date'] . ' ' . $row['start_time']);
    if ($slotDT > $now) {
        $slots[] = $row;
    }
}
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
            <div class="mb-3">
                <label for="appointment_id" class="form-label">Select Your Current Appointment:</label>
                <select name="appointment_id" id="appointment_id" class="form-select" required>
                    <option value="">-- Select Appointment --</option>
                    <?php while ($row = $appointments->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>" data-provider="<?= $row['provider_id'] ?>">
                            <?= htmlspecialchars($row['provider_name']) ?> – <?= $row['appointment_date'] ?> (<?= $row['start_time'] ?> to <?= $row['end_time'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="new_slot_id" class="form-label">Select New Slot:</label>
                <select name="new_slot_id" id="new_slot_id" class="form-select" required>
                    <option value="">-- Select Slot --</option>
                    <?php foreach ($slots as $slot): ?>
                        <option value="<?= $slot['id'] ?>" data-provider="<?= $slot['provider_id'] ?>">
                            <?= htmlspecialchars($slot['provider_name']) ?> – <?= $slot['available_date'] ?> (<?= $slot['start_time'] ?> to <?= $slot['end_time'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-warning w-100">Reschedule Appointment</button>
        </form>

        <div class="text-center mt-3">
            <a href="../users/dashboard_client.php">← Back to Dashboard</a>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
<script src="../assets/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
