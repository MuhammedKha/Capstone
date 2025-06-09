<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    header("Location: ../users/login.php");
    exit;
}

$provider_id = $_SESSION['user_id'];
$name = $_SESSION['name'];
$view = $_GET['view'] ?? 'upcoming';

$today = date("Y-m-d");

// Fetch appointments
$query = "
    SELECT a.id, u.name AS client_name, a.appointment_date, a.start_time, a.end_time, a.status
    FROM appointments a
    JOIN users u ON u.id = a.client_id
    WHERE a.provider_id = ?
";

if ($view === 'cancelled') {
    $query .= " AND a.status = 'cancelled'";
} elseif ($view === 'past') {
    $query .= " AND a.appointment_date < '$today' AND a.status = 'booked'";
} else { // upcoming
    $query .= " AND a.appointment_date >= '$today' AND a.status = 'booked'";
}

$query .= " ORDER BY a.appointment_date ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$appointments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Appointments – OABS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <div class="card shadow p-4">
        <h3 class="mb-4 text-center">Appointments for <?= htmlspecialchars($name) ?></h3>

        <div class="d-flex justify-content-center mb-4 gap-3">
            <a href="?view=upcoming" class="btn btn-outline-primary <?= $view === 'upcoming' ? 'active' : '' ?>">Upcoming</a>
            <a href="?view=past" class="btn btn-outline-secondary <?= $view === 'past' ? 'active' : '' ?>">Past</a>
            <a href="?view=cancelled" class="btn btn-outline-danger <?= $view === 'cancelled' ? 'active' : '' ?>">Cancelled</a>
        </div>

        <?php if ($appointments->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Client</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $appointments->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['client_name']) ?></td>
                                <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                                <td><?= htmlspecialchars($row['start_time']) ?> – <?= htmlspecialchars($row['end_time']) ?></td>
                                <td>
                                    <?= match ($row['status']) {
                                        'booked' => "<span class='badge bg-success'>Booked</span>",
                                        'cancelled' => "<span class='badge bg-danger'>Cancelled</span>",
                                        'completed' => "<span class='badge bg-secondary'>Completed</span>",
                                        default => "<span class='badge bg-warning text-dark'>Unknown</span>",
                                    } ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">No <?= htmlspecialchars($view) ?> appointments found.</div>
        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="dashboard_provider.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
<script src="../assets/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>