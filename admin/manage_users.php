<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../users/login.php");
    exit;
}

// Handle delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $userId = intval($_POST['delete_user']);
        $conn->query("DELETE FROM users WHERE id = $userId");
    }

    if (isset($_POST['approve_provider'])) {
        $userId = intval($_POST['approve_provider']);
        $conn->query("UPDATE users SET status = 'approved' WHERE id = $userId");
    }

    if (isset($_POST['delete_all_clients'])) {
        $conn->query("DELETE FROM users WHERE role = 'client'");
    }

    if (isset($_POST['delete_all_providers'])) {
        $conn->query("DELETE FROM users WHERE role = 'provider'");
    }
}

// Fetch all users
$result = $conn->query("SELECT * FROM users ORDER BY role, name");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Users – Admin | OABS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <h3 class="mb-4 text-center">Manage Users</h3>

    <div class="d-flex justify-content-center mb-4 gap-2">
        <form method="POST">
            <button type="submit" name="delete_all_clients" class="btn btn-outline-danger">Delete All Clients</button>
        </form>
        <form method="POST">
            <button type="submit" name="delete_all_providers" class="btn btn-outline-danger">Delete All Providers</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= ucfirst($row['role']) ?></td>
                        <td>
                            <?php if ($row['role'] === 'client' || $row['status'] === 'approved'): ?>
                                ✅
                            <?php else: ?>
                                <span class="text-muted">⏳ Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <?php if ($row['role'] === 'provider' && $row['status'] === 'pending'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="approve_provider" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    <input type="hidden" name="delete_user" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="text-center mt-4">
        <a href="dashboard_admin.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
