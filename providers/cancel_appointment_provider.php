<?php
// Set timezone to Melbourne
date_default_timezone_set('Australia/Melbourne');

// Start session and include config
session_start();
require_once '../includes/config.php';
require_once '../includes/mail_helper.php'; // 📧 Add this to enable email

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    header("Location: ../users/login.php");
    exit;
}

$provider_id = $_SESSION['user_id'];
$provider_name = $_SESSION['name'];
$msg = "";

// Handle cancellation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['appointment_id'])) {
    $appointment_id = intval($_POST['appointment_id']);

    // Fetch full appointment details
    $stmt = $conn->prepare("
        SELECT a.*, 
               c.name AS client_name, c.email AS client_email, 
               p.name AS provider_name, p.email AS provider_email
        FROM appointments a
        JOIN users c ON a.client_id = c.id
        JOIN users p ON a.provider_id = p.id
        WHERE a.id = ? AND a.provider_id = ?
    ");
    $stmt->bind_param("ii", $appointment_id, $provider_id);
    $stmt->execute();
    $appointment = $stmt->get_result()->fetch_assoc();

    if ($appointment && $appointment['status'] === 'booked') {
        // Cancel appointment
        $stmtCancel = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
        $stmtCancel->bind_param("i", $appointment_id);
        $stmtCancel->execute();

        // Free up the slot in availability
        $stmtFree = $conn->prepare("
            UPDATE availability 
            SET status = 'available' 
            WHERE provider_id = ? AND available_date = ? AND start_time = ? AND end_time = ?
        ");
        $stmtFree->bind_param("isss", $provider_id, $appointment['appointment_date'], $appointment['start_time'], $appointment['end_time']);
        $stmtFree->execute();

        // ✉️ Prepare email content
        $date = $appointment['appointment_date'];
        $time = $appointment['start_time'] . ' – ' . $appointment['end_time'];
        $clientEmail = $appointment['client_email'];
        $providerEmail = $appointment['provider_email'];
        $adminEmail = 'azz37447@my.holmes.edu.au';

        $subject = "Appointment Cancelled – OABS";
        $body = "
            <p>Dear {$appointment['client_name']},</p>
            <p>Your appointment scheduled with {$appointment['provider_name']} on <strong>$date</strong> at <strong>$time</strong> has been <span style='color:red;'>cancelled</span> by the provider.</p>
            <p>Please log in to book a new appointment if needed.</p>
            <p>Regards,<br>OABS Team</p>
        ";

        // Send emails to client, provider, admin
        sendMail($clientEmail, $subject, $body);
        sendMail($providerEmail, $subject, "<p>You cancelled an appointment with {$appointment['client_name']} on $date at $time.</p>");
        sendMail($adminEmail, $subject, "<p>Provider <strong>{$appointment['provider_name']}</strong> cancelled an appointment with <strong>{$appointment['client_name']}</strong> on <strong>$date</strong> at <strong>$time</strong>.</p>");

        $msg = "<div class='alert alert-success'>✅ Appointment cancelled and notifications sent.</div>";
    } else {
        $msg = "<div class='alert alert-warning'>⚠️ Appointment not found or already cancelled.</div>";
    }
}

// Fetch remaining booked appointments
$stmt = $conn->prepare("
    SELECT a.id, a.appointment_date, a.start_time, a.end_time, u.name AS client_name
    FROM appointments a
    JOIN users u ON a.client_id = u.id
    WHERE a.provider_id = ? AND a.status = 'booked'
    ORDER BY a.appointment_date, a.start_time
");
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$appointments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cancel Appointments – OABS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <div class="card shadow p-4">
        <h3 class="mb-4 text-center">Cancel Booked Appointments</h3>
        <?= $msg ?>

        <?php if ($appointments->num_rows > 0): ?>
            <form method="POST">
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Client</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $appointments->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['client_name']) ?></td>
                                <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                                <td><?= htmlspecialchars($row['start_time']) ?> – <?= htmlspecialchars($row['end_time']) ?></td>
                                <td>
                                    <button type="submit" name="appointment_id" value="<?= $row['id'] ?>" class="btn btn-danger btn-sm">Cancel</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-info text-center">No booked appointments found.</div>
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
