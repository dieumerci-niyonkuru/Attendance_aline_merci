<?php
// student/profile.php
require_once dirname(__DIR__) . '/bootstrap.php';
requireStudent();

$page_title = "My Profile";

$student_id = $_SESSION['user_id'];
$student = [];
$message = '';
$error = '';

try {
    // Get student information
    $student = fetchOne("SELECT * FROM users WHERE id = ?", [$student_id]);
    
    if (!$student) {
        $error = "Student information not found.";
    }
    
    // Handle profile update
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $course = trim($_POST['course']);
        $year_level = trim($_POST['year_level']);
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } else {
            // Check if email already exists (excluding current user)
            $existing = fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $student_id]);
            
            if ($existing) {
                $error = "Email already registered by another user.";
            } else {
                // Update profile
                $sql = "UPDATE users SET full_name = ?, email = ?, course = ?, year_level = ? WHERE id = ?";
                $stmt = $db->prepare($sql);
                if ($stmt->execute([$full_name, $email, $course, $year_level, $student_id])) {
                    // Update session variables
                    $_SESSION['full_name'] = $full_name;
                    $_SESSION['email'] = $email;
                    $_SESSION['course'] = $course;
                    $_SESSION['year_level'] = $year_level;
                    
                    $message = "Profile updated successfully!";
                    // Refresh student data
                    $student['full_name'] = $full_name;
                    $student['email'] = $email;
                    $student['course'] = $course;
                    $student['year_level'] = $year_level;
                } else {
                    $error = "Failed to update profile. Please try again.";
                }
            }
        }
    }
    
    // Handle password change
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if (!password_verify($current_password, $student['password'])) {
            $error = "Current password is incorrect.";
        } elseif (strlen($new_password) < 6) {
            $error = "New password must be at least 6 characters long.";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match.";
        } else {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            
            if ($stmt->execute([$hashed_password, $student_id])) {
                $message = "Password changed successfully!";
            } else {
                $error = "Failed to change password. Please try again.";
            }
        }
    }
    
} catch (PDOException $e) {
    $error = "Error loading profile: " . $e->getMessage();
}

// Include header
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">My Profile</h1>
        <p class="text-gray-600">Manage your personal information and account settings.</p>
    </div>
    
    <!-- Messages -->
    <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Profile Information -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800">Personal Information</h2>
            
            <form method="POST" action="" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Student ID</label>
                    <input type="text" value="<?php echo htmlspecialchars($student['student_id']); ?>" 
                           class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md" readonly>
                    <p class="text-xs text-gray-500 mt-1">Student ID cannot be changed</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" value="<?php echo htmlspecialchars($student['username']); ?>" 
                           class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md" readonly>
                    <p class="text-xs text-gray-500 mt-1">Username cannot be changed</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="full_name" required
                           value="<?php echo htmlspecialchars($student['full_name']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                    <input type="email" name="email" required
                           value="<?php echo htmlspecialchars($student['email']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Course/Program</label>
                        <select name="course" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Course</option>
                            <option value="Computer Science" <?php echo ($student['course'] == 'Computer Science') ? 'selected' : ''; ?>>Computer Science</option>
                            <option value="Business Administration" <?php echo ($student['course'] == 'Business Administration') ? 'selected' : ''; ?>>Business Administration</option>
                            <option value="Engineering" <?php echo ($student['course'] == 'Engineering') ? 'selected' : ''; ?>>Engineering</option>
                            <option value="Nursing" <?php echo ($student['course'] == 'Nursing') ? 'selected' : ''; ?>>Nursing</option>
                            <option value="Education" <?php echo ($student['course'] == 'Education') ? 'selected' : ''; ?>>Education</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Year Level</label>
                        <select name="year_level" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Year</option>
                            <option value="1st Year" <?php echo ($student['year_level'] == '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                            <option value="2nd Year" <?php echo ($student['year_level'] == '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                            <option value="3rd Year" <?php echo ($student['year_level'] == '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                            <option value="4th Year" <?php echo ($student['year_level'] == '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Created</label>
                    <input type="text" value="<?php echo date('F j, Y', strtotime($student['created_at'])); ?>" 
                           class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md" readonly>
                </div>
                
                <div class="pt-4">
                    <button type="submit" name="update_profile"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-md transition duration-300">
                        <i class="fas fa-save mr-2"></i>Update Profile
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Change Password -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800">Change Password</h2>
            
            <form method="POST" action="" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Password *</label>
                    <input type="password" name="current_password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password *</label>
                    <input type="password" name="new_password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password *</label>
                    <input type="password" name="confirm_password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="pt-4">
                    <button type="submit" name="change_password"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-md transition duration-300">
                        <i class="fas fa-key mr-2"></i>Change Password
                    </button>
                </div>
            </form>
            
            <!-- Account Statistics -->
            <div class="mt-8 pt-8 border-t border-gray-200">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Account Statistics</h3>
                
                <div class="space-y-3">
                    <?php
                    // Get enrollment count
                    $enrollment = fetchOne("SELECT COUNT(*) as course_count FROM enrollments WHERE student_id = ?", [$student_id]);
                    
                    // Get attendance statistics
                    $attendance = fetchOne("
                        SELECT 
                            COUNT(*) as total_attendance,
                            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days
                        FROM attendance WHERE student_id = ?
                    ", [$student_id]);
                    ?>
                    
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span class="text-gray-700">Enrolled Courses</span>
                        <span class="font-semibold text-blue-600"><?php echo $enrollment['course_count'] ?? 0; ?></span>
                    </div>
                    
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span class="text-gray-700">Total Attendance Records</span>
                        <span class="font-semibold text-blue-600"><?php echo $attendance['total_attendance'] ?? 0; ?></span>
                    </div>
                    
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span class="text-gray-700">Present Days</span>
                        <span class="font-semibold text-green-600"><?php echo $attendance['present_days'] ?? 0; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once dirname(__DIR__) . '/includes/footer.php';
?>