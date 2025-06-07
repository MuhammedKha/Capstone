<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../users/login.php");
    exit;
}

$client_id = $_SESSION['user_id'];
$msg = "";

// Handle booking
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $slot_id = $_POST['slot_id'];

    $stmtSlot = $conn->prepare("SELECT * FROM availability WHERE id = ?");
    $stmtSlot->bind_param("i", $slot_id);
    $stmtSlot->execute();
    $slot = $stmtSlot->get_result()->fetch_assoc();

    if ($slot) {
        $stmt = $conn->prepare("
            INSERT INTO appointments (client_id, provider_id, appointment_date, start_time, end_time)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iisss",
            $client_id,
            $slot['provider_id'],
            $slot['available_date'],
            $slot['start_time'],
            $slot['end_time']
        );

        $msg = $stmt->execute()
            ? "✅ Appointment booked successfully."
            : "❌ Booking failed. Please try again.";
    } else {
        $msg = "❌ Invalid slot selected.";
    }
}

// Fetch available slots
$slots = $conn->query("
    SELECT a.id, a.available_date, a.start_time, a.end_time, u.name AS provider_name
    FROM availability a
    JOIN users u ON u.id = a.provider_id
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
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow p-4">
                <h3 class="mb-4 text-center">Book an Appointment</h3>

                <?php if (!empty($msg)): ?>
                    <div class="alert <?= str_starts_with($msg, '✅') ? 'alert-success' : 'alert-danger' ?>">
                        <?= $msg ?>
                    </div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <div class="mb-3">
                        <label for="slot_id" class="form-label">Select Available Slot</label>
                        <select name="slot_id" id="slot_id" class="form-select" required>
                            <option value="">-- Select --</option>
                            <?php while ($row = $slots->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>">
                                    <?= htmlspecialchars($row['provider_name']) ?> – <?= $row['available_date'] ?>
                                    (<?= $row['start_time'] ?> to <?= $row['end_time'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Book Now</button>
                </form>

                <p class="mt-3 text-center">
                    <a href="../users/dashboard_client.php" class="btn btn-link">← Back to Dashboard</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
