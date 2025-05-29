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
<html>
<head>
    <title>My Appointments - OABS</title>
</head>
<body>
<h2>My Appointments</h2>

<table border="1" cellpadding="8">
    <tr>
        <th>Provider</th>
        <th>Date</th>
        <th>Time</th>
        <th>Status</th>
    </tr>
    <?php while ($row = $data->fetch_assoc()) { ?>
        <tr>
            <td><?= htmlspecialchars($row['provider_name']) ?></td>
            <td><?= htmlspecialchars($row['appointment_date']) ?></td>
            <td><?= htmlspecialchars($row['start_time']) ?> - <?= htmlspecialchars($row['end_time']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
        </tr>
    <?php } ?>
</table>

<p><a href="dashboard_client.php">Back to Dashboard</a></p>
</body>
</html>