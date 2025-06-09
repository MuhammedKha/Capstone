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

// Auto-update past appointments
$conn->query("UPDATE appointments SET status = 'completed' 
              WHERE provider_id = $provider_id 
              AND appointment_date < CURDATE() 
              AND status = 'booked'");

// Manual completion from provider
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['complete_id'])) {
    $complete_id = intval($_POST['complete_id']);
    $stmt = $conn->prepare("UPDATE appointments SET status = 'completed' 
                            WHERE id = ? AND provider_id = ? AND status = 'booked'");
    $stmt->bind_param("ii", $complete_id, $provider_id);
    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success'>✅ Appointment marked as completed.</div>";
    } else {
        $msg = "<div class='alert alert-danger'>❌ Failed to update appointment status.</div>";
    }
}

// Fetch all appointments
$stmt = $conn->prepare("
    SELECT a.*, u.name AS client_name
    FROM appointments a
    JOIN users u ON a.client_id = u.id
    WHERE a.provider_id = ?
    ORDER BY a.appointment_date DESC, a.start_time DESC
");
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$result = $stmt->get_result();

$active = [];
$cancelled = [];

while ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'cancelled') {
        $cancelled[] = $row;
    } else {
        $active[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Appointments – OABS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <div class="card shadow p-4">
        <h3 class="text-center mb-4">Appointments for <?= htmlspecialchars($name) ?></h3>

        <?= $msg ?>

        <div class="mb-3 d-flex justify-content-center gap-2">
            <button class="btn btn-outline-primary filter-appointments" data-type="all">All</button>
            <button class="btn btn-outline-success filter-appointments" data-type="upcoming">Upcoming</button>
            <button class="btn btn-outline-secondary filter-appointments" data-type="past">Past</button>
            <button id="toggleCancelledBtn" class="btn btn-outline-danger">Show/Hide Cancelled</button>
        </div>

        <?php if (count($active) > 0): ?>
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
                        <?php foreach ($active as $row): ?>
                            <tr class="appointment-row" data-date="<?= htmlspecialchars($row['appointment_date']) ?>">
                                <td><?= htmlspecialchars($row['client_name']) ?></td>
                                <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                                <td><?= htmlspecialchars($row['start_time']) ?> – <?= htmlspecialchars($row['end_time']) ?></td>
                                <td>
                                    <?= match ($row['status']) {
                                        'booked' => "<span class='badge bg-success'>Booked</span>",
                                        'completed' => "<span class='badge bg-secondary'>Completed</span>",
                                        default => "<span class='badge bg-warning text-dark'>Unknown</span>",
                                    } ?>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'booked'): ?>
                                        <div class="d-flex justify-content-center gap-1">
                                            <form method="POST" action="cancel_appointment_provider.php" onsubmit="return confirm('Cancel this appointment?');">
                                                <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                            </form>
                                            <?php
                                                $today = date('Y-m-d');
                                                if ($row['appointment_date'] <= $today):
                                            ?>
                                                <form method="POST" onsubmit="return confirm('Mark this appointment as completed?');">
                                                    <input type="hidden" name="complete_id" value="<?= $row['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-secondary">Complete</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">No active appointments found.</div>
        <?php endif; ?>

        <!-- Cancelled Appointments Section -->
        <div id="cancelledAppointments" class="mt-5 d-none">
            <h5 class="text-danger text-center mb-3">Cancelled Appointments</h5>
            <?php if (count($cancelled) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Client</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cancelled as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['client_name']) ?></td>
                                    <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                                    <td><?= htmlspecialchars($row['start_time']) ?> – <?= htmlspecialchars($row['end_time']) ?></td>
                                    <td><span class="badge bg-danger">Cancelled</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">No cancelled appointments.</div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-4">
            <a href="dashboard_provider.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
<script src="../assets/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
