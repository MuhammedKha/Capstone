<?php
// Set timezone to Melbourne
date_default_timezone_set('Australia/Melbourne');

// Start session and include config
session_start();
require_once '../includes/config.php';
require_once '../includes/mail_helper.php'; // Include mail helper for PHPMailer

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../users/login.php");
    exit;
}

// Get client ID from session
$client_id = $_SESSION['user_id'];
$msg = "";

// Handle booking
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['slot_id'])) {
    $slot_id = intval($_POST['slot_id']);

    $stmt = $conn->prepare("SELECT * FROM availability WHERE id = ? AND status = 'available'");
    $stmt->bind_param("i", $slot_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $slot = $result->fetch_assoc();

    if ($slot) {
        $slotDT = new DateTime($slot['available_date'] . ' ' . $slot['start_time']);
        $now = new DateTime();

        if ($slotDT > $now) {
            $stmtBook = $conn->prepare("INSERT INTO appointments (client_id, provider_id, appointment_date, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
            $stmtBook->bind_param("iisss", $client_id, $slot['provider_id'], $slot['available_date'], $slot['start_time'], $slot['end_time']);
            if ($stmtBook->execute()) {
                $stmtUpdate = $conn->prepare("UPDATE availability SET status = 'booked' WHERE id = ?");
                $stmtUpdate->bind_param("i", $slot_id);
                $stmtUpdate->execute();

                // Fetch user emails
                $stmtEmails = $conn->prepare("
                    SELECT 
                        c.name AS client_name, c.email AS client_email,
                        p.name AS provider_name, p.email AS provider_email
                    FROM users c, users p
                    WHERE c.id = ? AND p.id = ?
                ");
                $stmtEmails->bind_param("ii", $client_id, $slot['provider_id']);
                $stmtEmails->execute();
                $resultEmails = $stmtEmails->get_result();
                $emails = $resultEmails->fetch_assoc();

                // Compose emails
                $apptDate = $slot['available_date'];
                $start = $slot['start_time'];
                $end = $slot['end_time'];
                $service = $slot['service_name'] ?? "Appointment";

                $subject = "Appointment Booked: $apptDate ($start – $end)";
                $body = "
                    <p>Dear {$emails['client_name']},</p>
                    <p>Your appointment for <strong>$service</strong> with <strong>{$emails['provider_name']}</strong> has been booked:</p>
                    <ul>
                        <li><strong>Date:</strong> $apptDate</li>
                        <li><strong>Time:</strong> $start to $end</li>
                    </ul>
                    <p>You’ll be notified of any updates.</p>
                    <p>Regards,<br>OABS Team</p>
                ";

                $providerBody = "
                    <p>Dear {$emails['provider_name']},</p>
                    <p><strong>{$emails['client_name']}</strong> has booked an appointment with you for <strong>$service</strong> on:</p>
                    <ul>
                        <li><strong>Date:</strong> $apptDate</li>
                        <li><strong>Time:</strong> $start to $end</li>
                    </ul>
                    <p>Login to your dashboard to manage appointments.</p>
                    <p>Regards,<br>OABS System</p>
                ";

                $adminEmail = 'azz37447@my.holmes.edu.au'; 
                sendMail($emails['client_email'], $subject, $body);
                sendMail($emails['provider_email'], $subject, $providerBody);
                sendMail($adminEmail, "Admin Alert – $subject", $providerBody); 

                $msg = "<div class='alert alert-success'>✅ Appointment booked successfully. Confirmation emails sent.</div>";
            } else {
                $msg = "<div class='alert alert-danger'>❌ Failed to book appointment.</div>";
            }
        } else {
            $msg = "<div class='alert alert-warning'>⚠️ This slot has already passed.</div>";
        }
    } else {
        $msg = "<div class='alert alert-warning'>⚠️ This slot is no longer available.</div>";
    }
}

// Fetch all available slots
$raw_slots = $conn->query("
    SELECT a.id, a.available_date, a.start_time, a.end_time, a.service_name, u.name AS provider_name
    FROM availability a
    JOIN users u ON u.id = a.provider_id
    WHERE a.status = 'available'
    ORDER BY a.available_date, a.start_time
");

// Filter future-only slots
$slots = [];
$now = new DateTime();
while ($row = $raw_slots->fetch_assoc()) {
    $slotDT = new DateTime($row['available_date'] . ' ' . $row['start_time']);
    if ($slotDT > $now) {
        $slots[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Appointment – OABS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <div class="card p-4 shadow mx-auto" style="max-width: 700px;">
        <h3 class="text-center mb-4">Book an Appointment</h3>

        <?= $msg ?>

        <form method="POST">
            <label for="slot_id" class="form-label">Select Available Slot</label>
            <select name="slot_id" id="slot_id" class="form-select mb-3" required>
                <option value="">-- Select --</option>
                <?php foreach ($slots as $row): ?>
                    <option value="<?= $row['id'] ?>">
                        <?= htmlspecialchars($row['provider_name']) ?> – <?= $row['available_date'] ?>
                        (<?= $row['start_time'] ?> to <?= $row['end_time'] ?>)
                        <?php if (!empty($row['service_name'])): ?>
                            – <?= htmlspecialchars($row['service_name']) ?>
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary w-100">Book Now</button>
        </form>

        <div class="text-center mt-3">
            <a href="../users/dashboard_client.php" class="btn btn-link">← Back to Dashboard</a>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
