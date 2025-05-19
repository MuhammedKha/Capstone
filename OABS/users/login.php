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

        // 🐛 DEBUG BLOCK (Remove after successful login)
        echo "<pre>";
        echo "Entered Password: [$password]" . PHP_EOL;
        echo "Stored Hash: [$hashedPassword]" . PHP_EOL;
        echo "Match: " . (password_verify($password, $hashedPassword) ? 'Yes' : 'No');
        echo "</pre>";

        // ✅ Actual password check
        if (password_verify($password, $hashedPassword)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;

            // Redirect by role
            if ($role === 'client') {
                header("Location: dashboard_client.php");
            } elseif ($role === 'provider') {
                header("Location: ../providers/dashboard_provider.php");
            } elseif ($role === 'admin') {
                header("Location: ../admin/dashboard_admin.php");
            }
            exit;
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