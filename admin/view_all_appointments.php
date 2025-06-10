<?php
session_start();
require_once '../includes/config.php';
date_default_timezone_set('Australia/Melbourne');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../users/login.php");
    exit;
}

$appointments = $conn->query("
    SELECT a.*, u1.name AS client_name, u2.name AS provider_name
    FROM appointments a
    JOIN users u1 ON a.client_id = u1.id
    JOIN users u2 ON a.provider_id = u2.id
    ORDER BY a.appointment_date DESC, a.start_time
");

$today = date('Y-m-d');
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Appointments – Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <h2 class="mb-4">All Appointments</h2>

    <div class="btn-group mb-3" role="group">
        <button class="btn btn-outline-primary filter-btn" data-type="all">All</button>
        <button class="btn btn-outline-success filter-btn" data-type="upcoming">Upcoming</button>
        <button class="btn btn-outline-warning filter-btn" data-type="past">Past</button>
        <button class="btn btn-outline-danger filter-btn" data-type="cancelled">Cancelled</button>
    </div>

    <table class="table table-bordered" id="appointmentsTable">
        <thead>
            <tr>
                <th>Client</th><th>Provider</th><th>Date</th><th>Time</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $appointments->fetch_assoc()): ?>
                <?php
                    $apptDate = $row['appointment_date'];
                    $isPast = $apptDate < $today;
                    $rowClass = ($row['status'] === 'cancelled') ? 'table-danger' : ($isPast ? 'table-warning' : 'table-success');
                ?>
                <tr class="<?= $rowClass ?>" data-date="<?= $apptDate ?>" data-status="<?= $row['status'] ?>">
                    <td><?= htmlspecialchars($row['client_name']) ?></td>
                    <td><?= htmlspecialchars($row['provider_name']) ?></td>
                    <td><?= $row['appointment_date'] ?></td>
                    <td><?= $row['start_time'] ?> to <?= $row['end_time'] ?></td>
                    <td><?= ucfirst($row['status']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="dashboard_admin.php" class="btn btn-link mt-3">← Back to Dashboard</a>
</div>

<script src="../assets/js/script.js"></script>
<script>
document.querySelectorAll(".filter-btn").forEach(btn => {
    btn.addEventListener("click", () => {
        const type = btn.getAttribute("data-type");
        const today = new Date().toISOString().split("T")[0];
        const rows = document.querySelectorAll("#appointmentsTable tbody tr");

        rows.forEach(row => {
            const date = row.getAttribute("data-date");
            const status = row.getAttribute("data-status");

            let show = true;
            if (type === "upcoming") show = status === "booked" && date >= today;
            else if (type === "past") show = status === "booked" && date < today;
            else if (type === "cancelled") show = status === "cancelled";

            row.style.display = show || type === "all" ? "" : "none";
        });
    });
});
</script>
</body>
</html>
