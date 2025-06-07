<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../users/login.php");
    exit;
}

require_once '../includes/config.php';

$sql = "SELECT a.id, u.name AS client_name, p.name AS provider_name, 
               a.appointment_date, a.start_time, a.end_time, a.status
        FROM appointments a
        JOIN users u ON a.client_id = u.id
        JOIN users p ON a.provider_id = p.id
        ORDER BY a.appointment_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Appointments - Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            border-collapse: collapse;
            width: 90%;
            margin: 20px auto;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px 15px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        h2 {
            text-align: center;
        }
        .back-link {
            text-align: center;
            display: block;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <h2>All Booked Appointments</h2>

    <table>
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
                <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                <td><?= htmlspecialchars($row['start_time']) ?> - <?= htmlspecialchars($row['end_time']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <a class="back-link" href="dashboard_admin.php">← Back to Admin Dashboard</a>

</body>
</html>
