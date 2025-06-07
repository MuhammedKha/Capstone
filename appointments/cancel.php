<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../users/login.php");
    exit;
}

$client_id = $_SESSION['user_id'];
$msg = "";

// Handle cancellation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['appointment_id'])) {
    $appointment_id = $_POST['appointment_id'];

    // Only cancel appointments belonging to this client
    $stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND client_id = ?");
    $stmt->bind_param("ii", $appointment_id, $client_id);
    if ($stmt->execute()) {
        $msg = "✅ Appointment cancelled successfully.";
    } else {
        $msg = "❌ Failed to cancel appointment.";
    }
}

// Get active appointments
$result = $conn->prepare("
    SELECT a.id, u.name AS provider_name, a.appointment_date, a.start_time, a.end_time 
    FROM appointments a
    JOIN users u ON a.provider_id = u.id
    WHERE a.client_id = ? AND a.status = 'scheduled'
    ORDER BY a.appointment_date
");
$result->bind_param("i", $client_id);
$result->execute();
$appointments = $result->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cancel Appointment - OABS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <h2 class="mb-4 text-center">Cancel Appointment</h2>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-info"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="appointment_id" class="form-label">Select Appointment to Cancel:</label>
            <select name="appointment_id" id="appointment_id" class="form-select" required>
                <option value="">-- Select --</option>
                <?php while ($row = $appointments->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>">
                        <?= htmlspecialchars($row['provider_name']) ?> - <?= $row['appointment_date'] ?> (<?= $row['start_time'] ?> to <?= $row['end_time'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-danger">Cancel Appointment</button>
    </form>

    <p class="mt-3"><a href="../users/dashboard_client.php">Back to Dashboard</a></p>
</div>

<?php include '../templates/footer.php'; ?>
</body>
</html>