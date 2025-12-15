<?php
// setup_sample_data.php - Add sample data to database
require_once 'bootstrap.php';

// Only allow admin or first-time setup
if (isLoggedIn() && !isAdmin()) {
    header("Location: " . SITE_URL . "student/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Sample Data - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Setup Sample Data</h1>
            
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['setup'])) {
                try {
                    // Check if admin user exists
                    $admin_check = fetchOne("SELECT id FROM users WHERE username = 'admin'");
                    
                    if (!$admin_check) {
                        // Create admin user (password: admin123)
                        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
                        $sql = "INSERT INTO users (student_id, username, email, password, full_name, role) 
                               VALUES ('ADMIN001', 'admin', 'admin@school.edu', ?, 'System Administrator', 'admin')";
                        executeQuery($sql, [$hashed_password]);
                        echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'>✅ Admin user created</div>";
                    } else {
                        echo "<div class='bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4'>✅ Admin user already exists</div>";
                    }
                    
                    // Insert sample courses
                    $courses = [
                        ['CS101', 'Introduction to Programming', 'Learn basic programming concepts', 'Dr. Smith', 'Monday, Wednesday', '9:00 AM - 10:30 AM'],
                        ['MATH201', 'Calculus I', 'Differential and integral calculus', 'Prof. Johnson', 'Tuesday, Thursday', '11:00 AM - 12:30 PM'],
                        ['ENG102', 'English Composition', 'Writing and communication skills', 'Dr. Williams', 'Monday, Friday', '2:00 PM - 3:30 PM']
                    ];
                    
                    $course_ids = [];
                    foreach ($courses as $course) {
                        $existing = fetchOne("SELECT id FROM courses WHERE course_code = ?", [$course[0]]);
                        if (!$existing) {
                            $sql = "INSERT INTO courses (course_code, course_name, description, instructor, schedule_day, schedule_time) 
                                   VALUES (?, ?, ?, ?, ?, ?)";
                            executeQuery($sql, $course);
                            $course_ids[] = $db->lastInsertId();
                            echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'>✅ Course {$course[1]} created</div>";
                        } else {
                            $course_ids[] = $existing['id'];
                            echo "<div class='bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4'>✅ Course {$course[1]} already exists</div>";
                        }
                    }
                    
                    // Create sample students
                    $students = [
                        ['24RP03511', 'john', 'john@student.edu', 'John Doe', 'Computer Science', '2nd Year'],
                        ['24RP03512', 'jane', 'jane@student.edu', 'Jane Smith', 'Business Administration', '3rd Year'],
                        ['24RP03513', 'bob', 'bob@student.edu', 'Bob Johnson', 'Engineering', '1st Year']
                    ];
                    
                    $student_ids = [];
                    foreach ($students as $student_data) {
                        $existing = fetchOne("SELECT id FROM users WHERE username = ?", [$student_data[1]]);
                        if (!$existing) {
                            $hashed_password = password_hash('student123', PASSWORD_DEFAULT);
                            $sql = "INSERT INTO users (student_id, username, email, password, full_name, role, course, year_level) 
                                   VALUES (?, ?, ?, ?, ?, 'student', ?, ?)";
                            executeQuery($sql, [$student_data[0], $student_data[1], $student_data[2], $hashed_password, $student_data[3], $student_data[4], $student_data[5]]);
                            $student_ids[] = $db->lastInsertId();
                            echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'>✅ Student {$student_data[3]} created</div>";
                        } else {
                            $student_ids[] = $existing['id'];
                            echo "<div class='bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4'>✅ Student {$student_data[3]} already exists</div>";
                        }
                    }
                    
                    // Enroll students in courses
                    foreach ($student_ids as $student_id) {
                        foreach ($course_ids as $course_id) {
                            $existing = fetchOne("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?", [$student_id, $course_id]);
                            if (!$existing) {
                                $sql = "INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)";
                                executeQuery($sql, [$student_id, $course_id]);
                                echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'>✅ Enrollment created</div>";
                            }
                        }
                    }
                    
                    // Add sample attendance records for the past 30 days
                    $statuses = ['present', 'present', 'present', 'present', 'present', 'late', 'absent', 'excused'];
                    
                    foreach ($student_ids as $student_id) {
                        foreach ($course_ids as $course_id) {
                            for ($i = 0; $i < 5; $i++) {
                                $random_days = rand(0, 30);
                                $date = date('Y-m-d', strtotime("-{$random_days} days"));
                                $status = $statuses[array_rand($statuses)];
                                
                                // Check if attendance already exists
                                $existing = fetchOne("
                                    SELECT id FROM attendance 
                                    WHERE student_id = ? AND course_id = ? AND date = ?
                                ", [$student_id, $course_id, $date]);
                                
                                if (!$existing) {
                                    // Get enrollment ID
                                    $enrollment = fetchOne("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?", [$student_id, $course_id]);
                                    if ($enrollment) {
                                        $sql = "INSERT INTO attendance (enrollment_id, date, status) VALUES (?, ?, ?)";
                                        executeQuery($sql, [$enrollment['id'], $date, $status]);
                                    }
                                }
                            }
                        }
                    }
                    
                    echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'>
                            <h3 class='font-bold'>✅ Setup Complete!</h3>
                            <p>Sample data has been added to the database.</p>
                            <p class='mt-2'><strong>Test Credentials:</strong></p>
                            <ul class='list-disc pl-5 mt-1'>
                                <li>Admin: admin / admin123</li>
                                <li>Student: john / student123</li>
                                <li>Student: jane / student123</li>
                                <li>Student: bob / student123</li>
                            </ul>
                            <div class='mt-4'>
                                <a href='" . SITE_URL . "login.php' class='bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-md inline-block'>
                                    <i class='fas fa-sign-in-alt mr-2'></i>Go to Login
                                </a>
                            </div>
                          </div>";
                    
                } catch (PDOException $e) {
                    echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>
                            <h3 class='font-bold'>❌ Setup Failed</h3>
                            <p>Error: " . $e->getMessage() . "</p>
                          </div>";
                }
            }
            ?>
            
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">About This Setup</h2>
                <p class="text-gray-600 mb-4">This will populate your database with sample data including:</p>
                <ul class="list-disc pl-5 text-gray-600 mb-4">
                    <li>Admin account (admin/admin123)</li>
                    <li>3 sample student accounts</li>
                    <li>3 sample courses</li>
                    <li>Enrollments for all students</li>
                    <li>Sample attendance records for the past 30 days</li>
                </ul>
                
                <form method="POST" action="">
                    <button type="submit" name="setup" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-md transition duration-300 flex items-center justify-center">
                        <i class="fas fa-database mr-2"></i>Setup Sample Data
                    </button>
                </form>
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-blue-800 mb-3">Important Notes:</h3>
                <ul class="list-disc pl-5 text-blue-700 space-y-2">
                    <li>Make sure your database is created before running this setup</li>
                    <li>If you encounter errors, check if MySQL is running</li>
                    <li>For XAMPP: username='root', password='' (empty)</li>
                    <li>You can run this multiple times - it won't duplicate existing data</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>