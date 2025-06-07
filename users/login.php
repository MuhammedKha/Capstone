<?php
session_start();
require_once '../includes/config.php';

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $name, $hashedPassword, $role);
            $stmt->fetch();

            if (password_verify($password, $hashedPassword)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;

                switch ($role) {
                    case 'client':
                        header("Location: dashboard_client.php");
                        break;
                    case 'provider':
                        header("Location: ../providers/dashboard_provider.php");
                        break;
                    case 'admin':
                        header("Location: ../admin/dashboard_admin.php");
                        break;
                    default:
                        $msg = "❌ Unknown user role. Contact support.";
                        session_destroy();
                        break;
                }
                exit;
            } else {
                $msg = "❌ Incorrect password.";
            }
        } else {
            $msg = "❌ No account found with that email.";
        }

        $stmt->close();
    } else {
        $msg = "❌ Please enter both email and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login – OABS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include '../templates/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow p-4">
                <h2 class="mb-4 text-center">Login to OABS</h2>

                <?php if (!empty($msg)): ?>
                    <div class="alert alert-danger"><?php echo $msg; ?></div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Enter password">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>

                <p class="mt-3 text-center">
                    Don't have an account?
                    <a href="register.php">Register here</a><br>
                    <a href="../index.php" class="d-block mt-2">← Back to Home</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>