<?php
// includes/auth_check.php - SIMPLIFIED VERSION

// No need to include database here - header.php already includes it

// Check if user is logged in
function requireLogin() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        // Get the base URL
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $script = $_SERVER['SCRIPT_NAME'];
        $dir = dirname($script);
        $dir = rtrim($dir, '/');
        $base_url = $protocol . $host . $dir;
        
        header("Location: " . $base_url . "/login.php");
        exit();
    }
}

// Check if user has specific role
function requireRole($required_role) {
    requireLogin();
    
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $required_role) {
        $_SESSION['error'] = "You don't have permission to access this page.";
        
        // Get the base URL
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $script = $_SERVER['SCRIPT_NAME'];
        $dir = dirname($script);
        $dir = rtrim($dir, '/');
        $base_url = $protocol . $host . $dir;
        
        if ($_SESSION['user_role'] == 'admin') {
            header("Location: " . $base_url . "/admin/dashboard.php");
        } else {
            header("Location: " . $base_url . "/student/dashboard.php");
        }
        exit();
    }
}

// Redirect if already logged in
function redirectIfLoggedIn() {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        // Get the base URL
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $script = $_SERVER['SCRIPT_NAME'];
        $dir = dirname($script);
        $dir = rtrim($dir, '/');
        $base_url = $protocol . $host . $dir;
        
        if ($_SESSION['user_role'] == 'admin') {
            header("Location: " . $base_url . "/admin/dashboard.php");
        } else {
            header("Location: " . $base_url . "/student/dashboard.php");
        }
        exit();
    }
}
?>