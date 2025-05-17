<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check user roles
function isClient() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Client';
}

function isProvider() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Provider';
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
}

// Redirect unauthorised users
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: /Capstone/OABS/users/login.php");
        exit();
    }
}
