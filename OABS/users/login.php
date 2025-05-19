<?php
session_start();
require_once '../includes/config.php';

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $name, $hashedPassword, $role);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            // ✅ Login success
            $_SESSION['user_id'] = $id;
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;

            // Redirect to role-specific dashboard
            if ($role === 'client') {
                header("Location: dashboard_client.php");
                exit;
            } elseif ($role === 'provider') {
                header("Location: ../providers/dashboard_provider.php");
                exit;
            } elseif ($role === 'admin') {
                header("Location: ../admin/dashboard_admin.php");
                exit;
            }
        } else {
            $msg = "❌ Invalid password.";
        }
    } else {
        $msg = "❌ Email not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - OABS</title>
</head>
<body>
<h2>Login</h2>
<p style="color:red;"><?php echo $msg; ?></p>
<form method="POST">
    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Login</button>
</form>
<p>Not registered? <a href="register.php">Register here</a></p>
</body>
</html>