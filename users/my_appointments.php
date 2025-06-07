<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}

$client_id = $_SESSION['user_id'];

$result = $conn->prepare("
    SELECT a.*, u.name AS provider_name 
    FROM appointments a
    JOIN users u ON a.provider_id = u.id
    WHERE a.client_id = ?
    ORDER BY a.appointment_date DESC, a.start_time DESC
");
$result->bind_param("i", $client_id);
$result->execute();
$data = $result->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Appointments – OABS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <div class="card shadow p-4">
        <h2 class="mb-4 text-center">My Appointments</h2>

        <?php if ($data->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Provider</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $data->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['provider_name']) ?></td>
                                <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                                <td><?= htmlspecialchars($row['start_time']) ?> – <?= htmlspecialchars($row['end_time']) ?></td>
                                <td>
                                    <?php
                                        $status = $row['status'];
                                        echo match ($status) {
                                            'booked' => "<span class='badge bg-success'>Booked</span>",
                                            'cancelled' => "<span class='badge bg-danger'>Cancelled</span>",
                                            'completed' => "<span class='badge bg-secondary'>Completed</span>",
                                            default => "<span class='badge bg-warning text-dark'>Unknown</span>",
                                        };
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">You have no appointments.</div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="dashboard_client.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>