<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../users/login.php");
    exit;
}

$client_id = $_SESSION['user_id'];
$msg = "";

// Handle reschedule POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['appointment_id'], $_POST['new_slot_id'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $new_slot_id = intval($_POST['new_slot_id']);

    // Get original appointment
    $stmtAppt = $conn->prepare("SELECT * FROM appointments WHERE id = ? AND client_id = ?");
    $stmtAppt->bind_param("ii", $appointment_id, $client_id);
    $stmtAppt->execute();
    $appt = $stmtAppt->get_result()->fetch_assoc();

    // Get new slot info
    $stmtSlot = $conn->prepare("SELECT * FROM availability WHERE id = ? AND status = 'available'");
    $stmtSlot->bind_param("i", $new_slot_id);
    $stmtSlot->execute();
    $slot = $stmtSlot->get_result()->fetch_assoc();

    if ($appt && $slot) {
        // Check if both providers match
        if ($appt['provider_id'] === $slot['provider_id']) {
            // Update appointment
            $stmtUpdate = $conn->prepare("
                UPDATE appointments 
                SET appointment_date = ?, start_time = ?, end_time = ?
                WHERE id = ?
            ");
            $stmtUpdate->bind_param("sssi", $slot['available_date'], $slot['start_time'], $slot['end_time'], $appointment_id);
            if ($stmtUpdate->execute()) {
                // Mark old slot as available
                $stmtFreeOld = $conn->prepare("
                    UPDATE availability 
                    SET status = 'available' 
                    WHERE provider_id = ? AND available_date = ? AND start_time = ? AND end_time = ?
                ");
                $stmtFreeOld->bind_param("isss", $appt['provider_id'], $appt['appointment_date'], $appt['start_time'], $appt['end_time']);
                $stmtFreeOld->execute();

                // Mark new slot as booked
                $stmtBookNew = $conn->prepare("UPDATE availability SET status = 'booked' WHERE id = ?");
                $stmtBookNew->bind_param("i", $new_slot_id);
                $stmtBookNew->execute();

                $msg = "<div class='alert alert-success'>✅ Appointment rescheduled successfully.</div>";
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

// Fetch appointments for this client
$appointments = $conn->prepare("
    SELECT a.id, a.appointment_date, a.start_time, a.end_time, u.name AS provider_name, a.provider_id
    FROM appointments a
    JOIN users u ON u.id = a.provider_id
    WHERE a.client_id = ? AND a.status = 'booked'
    ORDER BY a.appointment_date DESC
");
$appointments->bind_param("i", $client_id);
$appointments->execute();
$appt_results = $appointments->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reschedule Appointment – OABS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
    // Dynamic provider-based slot filtering
    function updateSlots() {
        const apptDropdown = document.getElementById("appointment_id");
        const selected = apptDropdown.options[apptDropdown.selectedIndex];
        const providerId = selected.getAttribute("data-provider");

        const slots = document.querySelectorAll("#new_slot_id option");
        slots.forEach(opt => {
            opt.style.display = opt.getAttribute("data-provider") === providerId ? "block" : "none";
        });
        document.getElementById("new_slot_id").value = ""; // reset
    }
    </script>
</head>
<body>
<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <div class="card p-4 shadow mx-auto" style="max-width: 600px;">
        <h3 class="text-center mb-4">Reschedule Appointment</h3>

        <?= $msg ?>

        <form method="POST">
            <div class="mb-3">
                <label for="appointment_id" class="form-label">Select Your Current Appointment:</label>
                <select name="appointment_id" id="appointment_id" class="form-select" onchange="updateSlots()" required>
                    <option value="">-- Select Appointment --</option>
                    <?php while ($row = $appt_results->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>" data-provider="<?= $row['provider_id'] ?>">
                            <?= htmlspecialchars($row['provider_name']) ?> – <?= $row['appointment_date'] ?>
                            (<?= $row['start_time'] ?> to <?= $row['end_time'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="new_slot_id" class="form-label">Select New Slot:</label>
                <select name="new_slot_id" id="new_slot_id" class="form-select" required>
                    <option value="">-- Select Slot --</option>
                    <?php
                    $slot_query = $conn->query("
                        SELECT a.id, a.available_date, a.start_time, a.end_time, u.name AS provider_name, a.provider_id
                        FROM availability a
                        JOIN users u ON u.id = a.provider_id
                        WHERE a.status = 'available'
                        ORDER BY a.available_date, a.start_time
                    ");
                    while ($slot = $slot_query->fetch_assoc()):
                    ?>
                        <option value="<?= $slot['id'] ?>" data-provider="<?= $slot['provider_id'] ?>">
                            <?= htmlspecialchars($slot['provider_name']) ?> – <?= $slot['available_date'] ?>
                            (<?= $slot['start_time'] ?> to <?= $slot['end_time'] ?>)
                        </option>
                    <?php endwhile; ?>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>