<?php
// Set timezone to Melbourne
date_default_timezone_set('Australia/Melbourne');

// Start session and include config
session_start();
require_once '../includes/config.php';
require_once '../includes/mail_helper.php'; // ✅ PHPMailer helper

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../users/login.php");
    exit;
}

$client_id = $_SESSION['user_id'];
$msg = "";

// Handle cancellation form
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['appointment_id'])) {
    $appointment_id = intval($_POST['appointment_id']);

    // Get slot + user info for email
    $stmt = $conn->prepare("
        SELECT a.appointment_date, a.start_time, a.end_time, 
               u1.email AS client_email, u1.name AS client_name,
               u2.email AS provider_email, u2.name AS provider_name
        FROM appointments a
        JOIN users u1 ON a.client_id = u1.id
        JOIN users u2 ON a.provider_id = u2.id
        WHERE a.id = ? AND a.client_id = ?
    ");
    $stmt->bind_param("ii", $appointment_id, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $appt = $result->fetch_assoc();

    if ($appt) {
        // Cancel appointment
        $stmtCancel = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND client_id = ?");
        $stmtCancel->bind_param("ii", $appointment_id, $client_id);
        $stmtCancel->execute();

        // Free the availability slot
        $stmtAvail = $conn->prepare("UPDATE availability SET status = 'available' WHERE available_date = ? AND start_time = ? AND end_time = ?");
        $stmtAvail->bind_param("sss", $appt['appointment_date'], $appt['start_time'], $appt['end_time']);
        $stmtAvail->execute();

        // ✅ Email content
        $subject = "Appointment Cancelled – OABS";
        $body = "
            <p>Dear {$appt['client_name']},</p>
            <p>Your appointment with <strong>{$appt['provider_name']}</strong> on 
            <strong>{$appt['appointment_date']}</strong> from 
            <strong>{$appt['start_time']} to {$appt['end_time']}</strong> has been successfully <strong>cancelled</strong>.</p>
            <p>Regards,<br>OABS System</p>
        ";

        $providerBody = "
            <p>Dear {$appt['provider_name']},</p>
            <p>Your client <strong>{$appt['client_name']}</strong> has cancelled their appointment scheduled on 
            <strong>{$appt['appointment_date']}</strong> from 
            <strong>{$appt['start_time']} to {$appt['end_time']}</strong>.</p>
            <p>Regards,<br>OABS System</p>
        ";

        // ✅ Send emails to client, provider, admin
        sendMail($appt['client_email'], $subject, $body);
        sendMail($appt['provider_email'], $subject, $providerBody);
        sendMail("azz37447@my.holmes.edu.au", "Appointment Cancelled (Client)", "An appointment was cancelled by client {$appt['client_name']} with provider {$appt['provider_name']}.");

        $msg = "<div class='alert alert-success'>✅ Appointment cancelled successfully, and email notification sent.</div>";
    } else {
        $msg = "<div class='alert alert-warning'>⚠️ Invalid appointment selected.</div>";
    }
}

// Fetch active appointments
$stmt = $conn->prepare("
    SELECT a.id, u.name AS provider_name, a.appointment_date, a.start_time, a.end_time
    FROM appointments a
    JOIN users u ON a.provider_id = u.id
    WHERE a.client_id = ? AND a.status = 'booked'
    ORDER BY a.appointment_date DESC
");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$appointments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cancel Appointment – OABS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <div class="card p-4 shadow mx-auto" style="max-width: 600px;">
        <h3 class="text-center mb-4">Cancel Appointment</h3>

        <?= $msg ?>

        <form method="POST">
            <label for="appointment_id" class="form-label">Select Appointment to Cancel:</label>
            <select name="appointment_id" id="appointment_id" class="form-select mb-3" required>
                <option value="">-- Select --</option>
                <?php while ($row = $appointments->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>">
                        <?= htmlspecialchars($row['provider_name']) ?> – <?= $row['appointment_date'] ?>
                        (<?= $row['start_time'] ?> to <?= $row['end_time'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit" class="btn btn-danger w-100">Cancel Appointment</button>
        </form>

        <div class="text-center mt-3">
            <a href="../users/dashboard_client.php">← Back to Dashboard</a>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
