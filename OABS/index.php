<?php
// Start session (if needed for testing later)
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>OABS Render Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f4f4f4;
        }
        .container {
            padding: 20px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        h1 {
            color: green;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ OABS is Running on Render!</h1>
        <p>PHP Version: <strong><?php echo phpversion(); ?></strong></p>
        <p>Server Software: <strong><?php echo $_SERVER['SERVER_SOFTWARE']; ?></strong></p>
        <p>Document Root: <strong><?php echo $_SERVER['DOCUMENT_ROOT']; ?></strong></p>
        <p>Current File Path: <strong><?php echo __FILE__; ?></strong></p>
        <p>Tested at: <strong><?php echo date("Y-m-d H:i:s"); ?></strong></p>
    </div>
</body>
</html>
