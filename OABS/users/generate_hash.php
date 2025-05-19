<?php
$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "<strong>Use this hash in your database for 'admin123':</strong><br>";
echo $hash;
?>