@echo off
echo ========================================
echo  Student Attendance System Setup
echo ========================================
echo.

:: Check current directory
echo Current Directory: %cd%
echo.

:: Check PHP
echo Checking PHP...
php --version
if %errorlevel% neq 0 (
    echo ❌ PHP not found! Please install PHP or XAMPP
    pause
    exit
)

:: Check MySQL
echo.
echo Checking MySQL...
mysql --version
if %errorlevel% neq 0 (
    echo ⚠️ MySQL not found. Make sure MySQL service is running.
)

:: Create directory structure verification
echo.
echo Checking project structure...
if exist "config\database.php" (
    echo ✅ Database config found
) else (
    echo ❌ Database config missing!
)

if exist "includes\header.php" (
    echo ✅ Header file found
) else (
    echo ❌ Header file missing!
)

:: Create a simple test file
echo.
echo Creating test script...
(
echo ^<?php
echo // Quick test script
echo echo "Testing PHP...\<br\>";
echo echo "PHP Version: " . phpversion() . "\<br\>";
echo echo "Current Dir: " . __DIR__ . "\<br\>";
echo 
echo // Test database connection
echo try {
echo     ^$host = "localhost";
echo     ^$dbname = "attendance_system";
echo     ^$username = "root";
echo     ^$password = "";
echo     
echo     ^$conn = new PDO^("mysql:host=^$host;dbname=^$dbname", ^$username, ^$password^);
echo     ^$conn->setAttribute^(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION^);
echo     echo "✅ Database connection successful!\<br\>";
echo } catch^(Exception ^$e^) {
echo     echo "❌ Database connection failed: " . ^$e->getMessage^(^) . "\<br\>";
echo }
echo ?^>
) > test_setup.php

echo.
echo ========================================
echo  Setup Complete!
echo ========================================
echo.
echo Next Steps:
echo 1. Open browser and go to: http://localhost/student-attendance-system/test_setup.php
echo 2. If database fails, check database setup
echo 3. Main application: http://localhost/student-attendance-system/
echo.
echo Admin login: admin / admin123
echo.
pause