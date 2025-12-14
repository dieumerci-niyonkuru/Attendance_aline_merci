<?php
// Check if user is logged in
function requireLogin() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: ../login.php");
        exit();
    }
}

// Check if user has specific role
function requireRole($required_role) {
    requireLogin();
    
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $required_role) {
        $_SESSION['error'] = "You don't have permission to access this page.";
        if ($_SESSION['user_role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: student/dashboard.php");
        }
        exit();
    }
}

// Redirect if already logged in
function redirectIfLoggedIn() {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        if ($_SESSION['user_role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: student/dashboard.php");
        }
        exit();
    }
}
?>