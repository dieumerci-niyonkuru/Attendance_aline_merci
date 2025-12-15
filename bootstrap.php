<?php
// bootstrap.php - Core configuration file

// Define root directory
define('ROOT_DIR', dirname(__FILE__));

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define site URL dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script = $_SERVER['SCRIPT_NAME'];
$dir = dirname($script);

if ($dir == '/') {
    $dir = '';
} else {
    $dir = rtrim($dir, '/');
}

define('SITE_URL', $protocol . '://' . $host . $dir . '/');
define('SITE_NAME', 'Student Attendance System');

// Include database configuration
require_once ROOT_DIR . '/config/database.php';

// Authentication functions
function requireLogin() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: " . SITE_URL . "login.php");
        exit();
    }
}

function requireStudent() {
    requireLogin();
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
        header("Location: " . SITE_URL . "login.php");
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        header("Location: " . SITE_URL . "login.php");
        exit();
    }
}

function redirectIfLoggedIn() {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        if (isset($_SESSION['user_role'])) {
            if ($_SESSION['user_role'] == 'admin') {
                header("Location: " . SITE_URL . "admin/dashboard.php");
            } else {
                header("Location: " . SITE_URL . "student/dashboard.php");
            }
            exit();
        }
    }
}

// Global database functions
function executeQuery($sql, $params = []) {
    global $db;
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query Error: " . $e->getMessage());
        return false;
    }
}

function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
}

function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

function isStudent() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'student';
}
?>