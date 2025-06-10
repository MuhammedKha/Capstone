<?php
session_start();
require_once '../includes/config.php';
date_default_timezone_set('Australia/Melbourne');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../users/login.php");
    exit;
}

$msg = "";

// Handle approval
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $stmt = $conn->prepare("UPDATE users SET approved = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $msg = "✅ Provider approved successfully.";
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $msg = "🗑️ User deleted.";
}

// Handle delete all
if (isset($_GET['delete_all']) && in_array($_GET['delete_all'], ['clients', 'providers'])) {
    $role = $_GET['delete_all'];
    $stmt = $conn->prepare("DELETE FROM users WHERE role = ?");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $msg = "🗑️ All $role deleted.";
}

// Fetch users
$users = $conn->query("SELECT * FROM users WHERE role != 'admin' ORDER BY role, name");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users – Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../templates/header.php'; ?>
<div class="container my-5">
    <h2 class="mb-4">User Management</h2>
    <?php if ($msg): ?>
        <div class="alert alert-info"><?= $msg ?></div>
    <?php endif; ?>

    <div class="mb-3">
        <a href="?delete_all=clients" class="btn btn-outline-danger me-2">Delete All Clients</a>
        <a href="?delete_all=providers" class="btn btn-outline-danger">Delete All Providers</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= ucfirst($row['role']) ?></td>
                    <td>
                        <?php
                            if ($row['role'] === 'provider') {
                                echo $row['approved'] ? '✅ Approved' : '⏳ Pending';
                            } else {
                                echo '✔️';
                            }
                        ?>
                    </td>
                    <td>
                        <?php if ($row['role'] === 'provider' && !$row['approved']): ?>
                            <a href="?approve=<?= $row['id'] ?>" class="btn btn-sm btn-success">Approve</a>
                        <?php endif; ?>
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="dashboard_admin.php" class="btn btn-link mt-3">← Back to Dashboard</a>
</div>
<?php include '../templates/footer.php'; ?>
</body>
</html>
