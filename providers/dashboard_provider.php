<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    header("Location: ../users/login.php");
    exit;
}

$provider_id = $_SESSION['user_id'];
$name = $_SESSION['name'];
$msg = "";

// Handle cancellation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cancel_id'])) {
    $cancel_id = intval($_POST['cancel_id']);

    // Get the appointment details
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ? AND provider_id = ?");
    $stmt->bind_param("ii", $cancel_id, $provider_id);
    $stmt->execute();
    $appt = $stmt->get_result()->fetch_assoc();

    if ($appt && $appt['status'] === 'booked') {
        // Cancel appointment
        $stmtCancel = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
        $stmtCancel->bind_param("i", $cancel_id);
        $stmtCancel->execute();

        // Mark slot available again
        $stmtAvail = $conn->prepare("UPDATE availability SET status = 'available' WHERE provider_id = ? AND available_date = ? AND start_time = ? AND end_time = ?");
        $stmtAvail->bind_param("isss", $provider_id, $appt['appointment_date'], $appt['start_time'], $appt['end_time']);
        $stmtAvail->execute();

        $msg = "<div class='alert alert-success'>✅ Appointment cancelled successfully.</div>";
    } else {
        $msg = "<div class='alert alert-danger'>❌ Failed to cancel appointment.</div>";
    }
}

// Fetch appointments
$stmt = $conn->prepare("
    SELECT a.id, u.name AS client_name, a.appointment_date, a.start_time, a.end_time, a.status
    FROM appointments a
    JOIN users u ON u.id = a.client_id
    WHERE a.provider_id = ?
    ORDER BY a.appointment_date DESC
");
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$appointments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Provider Dashboard – OABS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <div class="card shadow p-4">
        <h2 class="text-center mb-4">Welcome, <?= htmlspecialchars($name) ?></h2>

        <ul class="list-group list-group-flush mb-4">
            <li class="list-group-item"><a href="update_availability.php" class="text-decoration-none">Set Availability</a></li>
            <li class="list-group-item"><a href="cancel_appointment_provider.php" class="text-decoration-none">Cancel Appointments</a></li>
            <li class="list-group-item"><a href="../users/logout.php" class="text-decoration-none text-danger">Logout</a></li>
        </ul>

        <?= $msg ?>

        <div class="card p-4 shadow-sm">
            <h4 class="mb-3">Your Appointments</h4>
            <?php if ($appointments->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Client</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $appointments->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['client_name']) ?></td>
                                <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                                <td><?= htmlspecialchars($row['start_time']) ?> - <?= htmlspecialchars($row['end_time']) ?></td>
                                <td>
                                    <?= match ($row['status']) {
                                        'booked' => "<span class='badge bg-success'>Booked</span>",
                                        'cancelled' => "<span class='badge bg-danger'>Cancelled</span>",
                                        'completed' => "<span class='badge bg-secondary'>Completed</span>",
                                        default => "<span class='badge bg-warning text-dark'>Unknown</span>",
                                    } ?>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'booked'): ?>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                            <input type="hidden" name="cancel_id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                        </form>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">No appointments found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
<script src="../assets/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>