<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../users/login.php");
    exit;
}

require_once '../includes/config.php';

$sql = "SELECT a.id, u.name AS client_name, p.name AS provider_name, a.date, a.start_time, a.end_time, a.status
        FROM appointments a
        JOIN users u ON a.client_id = u.id
        JOIN users p ON a.provider_id = p.id
        ORDER BY a.date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Appointments - Admin</title>
</head>
<body>
    <h2>All Booked Appointments</h2>
    <table border="1" cellpadding="8">
        <tr>
            <th>Client</th>
            <th>Provider</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['client_name']) ?></td>
                <td><?= htmlspecialchars($row['provider_name']) ?></td>
                <td><?= htmlspecialchars($row['date']) ?></td>
                <td><?= htmlspecialchars($row['start_time']) ?> - <?= htmlspecialchars($row['end_time']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
    <br>
    <a href="dashboard_admin.php">Back to Dashboard</a>
</body>
</html>
