<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        // Load credentials from Render environment
        $gmailUser = getenv('GMAIL_USER') ?: 'capstoneproject0044@gmail.com';
        $gmailPass = getenv('GMAIL_PASS') ?: 'jngzfgjllikkctti';

        // Log the sender address for debugging
        error_log("Sending from: $gmailUser");

        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $gmailUser;
        $mail->Password = $gmailPass;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom($gmailUser, 'OABS System');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail error: {$mail->ErrorInfo}");
        return false;
    }
}
