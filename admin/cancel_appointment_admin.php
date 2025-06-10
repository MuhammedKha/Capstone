<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/mail_helper.php'; // 📧 Email support
date_default_timezone_set('Australia/Melbourne');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../users/login.php");
    exit;
}

$msg = "";

// Handle cancel request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'])) {
    $appointment_id = intval($_POST['appointment_id']);

    // Get full appointment details
    $stmt = $conn->prepare("
        SELECT a.*, 
               c.name AS client_name, c.email AS client_email, 
               p.name AS provider_name, p.email AS provider_email
        FROM appointments a
        JOIN users c ON a.client_id = c.id
        JOIN users p ON a.provider_id = p.id
        WHERE a.id = ? AND a.status = 'booked'
    ");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $appointment = $stmt->get_result()->fetch_assoc();

    if ($appointment) {
        // Cancel the appointment
        $stmtCancel = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
        $stmtCancel->bind_param("i", $appointment_id);
        $stmtCancel->execute();

        // Free the slot
        $stmtFree = $conn->prepare("
            UPDATE availability 
            SET status = 'available' 
            WHERE provider_id = ? AND available_date = ? AND start_time = ? AND end_time = ?
        ");
        $stmtFree->bind_param("isss", $appointment['provider_id'], $appointment['appointment_date'], $appointment['start_time'], $appointment['end_time']);
        $stmtFree->execute();

        // Prepare email details
        $date = $appointment['appointment_date'];
        $time = $appointment['start_time'] . ' – ' . $appointment['end_time'];
        $clientEmail = $appointment['client_email'];
        $providerEmail = $appointment['provider_email'];
        $adminEmail = 'azz37447@my.holmes.edu.au';

        $subject = "Appointment Cancelled by Admin – OABS";
        $bodyClient = "
            <p>Dear {$appointment['client_name']},</p>
            <p>Your appointment with <strong>{$appointment['provider_name']}</strong> on <strong>$date</strong> at <strong>$time</strong> has been <span style='color:red;'>cancelled</span> by the administrator.</p>
            <p>We apologise for any inconvenience. Please rebook if needed.</p>
            <p>Regards,<br>OABS Admin</p>
        ";

        $bodyProvider = "
            <p>Dear {$appointment['provider_name']},</p>
            <p>Your appointment with <strong>{$appointment['client_name']}</strong> on <strong>$date</strong> at <strong>$time</strong> has been <span style='color:red;'>cancelled</span> by the administrator.</p>
            <p>Regards,<br>OABS Admin</p>
        ";

        $bodyAdmin = "
            <p>Admin cancelled the appointment between <strong>{$appointment['client_name']}</strong> and <strong>{$appointment['provider_name']}</strong> on <strong>$date</strong> at <strong>$time</strong>.</p>
        ";

        // Send emails
        sendMail($clientEmail, $subject, $bodyClient);
        sendMail($providerEmail, $subject, $bodyProvider);
        sendMail($adminEmail, $subject, $bodyAdmin);

        $msg = "<div class='alert alert-success'>✅ Appointment cancelled and notifications sent.</div>";
    } else {
        $msg = "<div class='alert alert-warning'>⚠️ Appointment not found or already cancelled.</div>";
    }
}

// Fetch all active (booked) appointments
$query = "
    SELECT a.id, u1.name AS client_name, u2.name AS provider_name, a.appointment_date, a.start_time, a.end_time
    FROM appointments a
    JOIN users u1 ON a.client_id = u1.id
    JOIN users u2 ON a.provider_id = u2.id
    WHERE a.status = 'booked'
    ORDER BY a.appointment_date DESC, a.start_time DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cancel Appointments – Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <h2 class="mb-4">Cancel Appointments</h2>

    <?= $msg ?? '' ?>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Client</th>
                        <th>Provider</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['client_name']) ?></td>
                            <td><?= htmlspecialchars($row['provider_name']) ?></td>
                            <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                            <td><?= htmlspecialchars($row['start_time']) ?> – <?= htmlspecialchars($row['end_time']) ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                    <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">No booked appointments to cancel.</div>
    <?php endif; ?>

    <a href="dashboard_admin.php" class="btn btn-secondary mt-4">← Back to Dashboard</a>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>
