@echo off
echo Setting up database for Student Attendance System...
echo.

:: Check if MySQL is accessible
mysql --version >nul 2>&1
if errorlevel 1 (
    echo ❌ MySQL not found or not in PATH
    echo Please start MySQL service first
    pause
    exit
)

echo Step 1: Creating database...
mysql -u root -p -e "DROP DATABASE IF EXISTS attendance_system; CREATE DATABASE attendance_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo Step 2: Importing database schema...
mysql -u root -p attendance_system < database.sql

echo Step 3: Creating admin user...
mysql -u root -p attendance_system -e "INSERT INTO users (student_id, username, email, password, full_name, role) VALUES ('ADMIN001', 'admin', 'admin@school.edu', '\$2y\$10\$vxTq6WIS4MkNYeLEkRl8..pW1eYwP.Jp0kPZvL6bQ3VbB3hJ9L8YW', 'System Administrator', 'admin') ON DUPLICATE KEY UPDATE username='admin';"

echo Step 4: Creating test student...
mysql -u root -p attendance_system -e "INSERT INTO users (student_id, username, email, password, full_name, role, course, year_level) VALUES ('STU001', 'john', 'john@student.edu', '\$2y\$10\$vxTq6WIS4MkNYeLEkRl8..pW1eYwP.Jp0kPZvL6bQ3VbB3hJ9L8YW', 'John Doe', 'student', 'Computer Science', '2nd Year') ON DUPLICATE KEY UPDATE username='john';"

echo.
echo ✅ Database setup complete!
echo.
echo Test credentials:
echo Admin: admin / admin123
echo Student: john / admin123
echo.
pause