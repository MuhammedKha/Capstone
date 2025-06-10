<?php
require_once '../includes/config.php';

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];
    $status = ($role === 'provider') ? 'pending' : 'approved'; // approval logic

    // Check for duplicate email
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $msg = "<div class='alert alert-warning'>⚠️ Email already exists.</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $password, $role, $status);
        if ($stmt->execute()) {
            if ($role === 'provider') {
                $msg = "<div class='alert alert-info'>🕒 Registration successful. Awaiting admin approval.</div>";
            } else {
                $msg = "<div class='alert alert-success'>✅ Registered successfully. You can now <a href='login.php'>log in</a>.</div>";
            }
        } else {
            $msg = "<div class='alert alert-danger'>❌ Registration failed.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register – OABS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <div class="card p-4 shadow mx-auto" style="max-width: 500px;">
        <h3 class="text-center mb-4">Register</h3>

        <?= $msg ?>

        <form method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Full Name:</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address:</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Register As:</label>
                <select name="role" class="form-select" required>
                    <option value="client">Client</option>
                    <option value="provider">Provider</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>

        <div class="text-center mt-3">
            <p>Already have an account? <a href="login.php">Log in</a></p>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
