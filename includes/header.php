<?php
session_start(); // Make sure session is started
require_once __DIR__ . '/../config/constants.php';

// Define root for consistent paths
$root = '/student-attendance-system';
?>

<?php
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../config/constants.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' | ' : ''; ?><?php echo SITE_NAME; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/custom.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
</head>
<body class="bg-gray-50">

    <!-- Include navigation -->
    <?php require_once __DIR__ . '/navigation.php'; ?>

    <main class="container mx-auto px-4 py-8">
