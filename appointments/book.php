<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../users/login.php");
    exit;
}

$client_id = $_SESSION['user_id'];
$msg = "";

// Handle booking form
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['slot_id'])) {
    $slot_id = intval($_POST['slot_id']);

    // Check if slot exists and is available
    $stmt = $conn->prepare("SELECT * FROM availability WHERE id = ? AND status = 'available'");
    $stmt->bind_param("i", $slot_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $slot = $result->fetch_assoc();

    if ($slot) {
        // Insert appointment
        $stmtBook = $conn->prepare("INSERT INTO appointments (client_id, provider_id, appointment_date, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
        $stmtBook->bind_param("iisss", $client_id, $slot['provider_id'], $slot['available_date'], $slot['start_time'], $slot['end_time']);

        if ($stmtBook->execute()) {
            // Mark slot as booked
            $stmtUpdate = $conn->prepare("UPDATE availability SET status = 'booked' WHERE id = ?");
            $stmtUpdate->bind_param("i", $slot_id);
            $stmtUpdate->execute();

            $msg = "<div class='alert alert-success'>✅ Appointment booked successfully.</div>";
        } else {
            $msg = "<div class='alert alert-danger'>❌ Failed to book appointment.</div>";
        }
    } else {
        $msg = "<div class='alert alert-warning'>⚠️ This slot is no longer available.</div>";
    }
}

// Fetch all available slots
$slots = $conn->query("
    SELECT a.id, a.available_date, a.start_time, a.end_time, u.name AS provider_name
    FROM availability a
    JOIN users u ON u.id = a.provider_id
    WHERE a.status = 'available'
    ORDER BY a.available_date, a.start_time
");
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
    <div class="card p-4 shadow mx-auto" style="max-width: 600px;">
        <h3 class="text-center mb-4">Book an Appointment</h3>

        <?= $msg ?>

        <form method="POST">
            <label for="slot_id" class="form-label">Select Available Slot</label>
            <select name="slot_id" id="slot_id" class="form-select mb-3" required>
                <option value="">-- Select --</option>
                <?php while ($row = $slots->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>">
                        <?= htmlspecialchars($row['provider_name']) ?> – <?= $row['available_date'] ?>
                        (<?= $row['start_time'] ?> to <?= $row['end_time'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn btn-primary w-100">Book Now</button>
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
