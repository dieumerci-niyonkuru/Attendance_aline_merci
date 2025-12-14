<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$user_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : '';

// Root path of your project (adjust if your folder name differs)
$root = '/student-attendance-system';
?>

<nav class="bg-blue-600 text-white shadow-lg">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <!-- Logo/Brand -->
            <a href="<?php echo $root; ?>/index.php" class="text-2xl font-bold">
                <i class="fas fa-graduation-cap mr-2"></i><?php echo defined('SITE_NAME') ? SITE_NAME : 'Student Attendance'; ?>
            </a>
            
            <!-- Navigation Links -->
            <div class="flex items-center space-x-6">
                <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <!-- Dashboard link based on role -->
                    <?php if($user_role == defined('ROLE_ADMIN') ? ROLE_ADMIN : 'admin'): ?>
                        <a href="<?php echo $root; ?>/admin/dashboard.php" class="hover:text-blue-200 <?php echo ($current_page == 'dashboard.php') ? 'font-bold underline' : ''; ?>">
                            <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                        </a>
                        <a href="<?php echo $root; ?>/admin/manage_students.php" class="hover:text-blue-200 <?php echo ($current_page == 'manage_students.php') ? 'font-bold underline' : ''; ?>">
                            <i class="fas fa-users mr-1"></i>Students
                        </a>
                        <a href="<?php echo $root; ?>/admin/manage_courses.php" class="hover:text-blue-200 <?php echo ($current_page == 'manage_courses.php') ? 'font-bold underline' : ''; ?>">
                            <i class="fas fa-book mr-1"></i>Courses
                        </a>
                        <a href="<?php echo $root; ?>/admin/take_attendance.php" class="hover:text-blue-200 <?php echo ($current_page == 'take_attendance.php') ? 'font-bold underline' : ''; ?>">
                            <i class="fas fa-clipboard-check mr-1"></i>Attendance
                        </a>
                        <a href="<?php echo $root; ?>/admin/view_reports.php" class="hover:text-blue-200 <?php echo ($current_page == 'view_reports.php') ? 'font-bold underline' : ''; ?>">
                            <i class="fas fa-chart-bar mr-1"></i>Reports
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $root; ?>/student/dashboard.php" class="hover:text-blue-200 <?php echo ($current_page == 'dashboard.php') ? 'font-bold underline' : ''; ?>">
                            <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                        </a>
                        <a href="<?php echo $root; ?>/student/view_attendance.php" class="hover:text-blue-200 <?php echo ($current_page == 'view_attendance.php') ? 'font-bold underline' : ''; ?>">
                            <i class="fas fa-calendar-check mr-1"></i>My Attendance
                        </a>
                        <a href="<?php echo $root; ?>/student/profile.php" class="hover:text-blue-200 <?php echo ($current_page == 'profile.php') ? 'font-bold underline' : ''; ?>">
                            <i class="fas fa-user mr-1"></i>Profile
                        </a>
                    <?php endif; ?>
                    
                    <!-- User dropdown -->
                    <div class="relative group">
                        <button class="flex items-center space-x-2 hover:text-blue-200">
                            <i class="fas fa-user-circle text-xl"></i>
                            <span><?php echo htmlspecialchars($user_name); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white text-gray-800 rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-10">
                            <a href="<?php echo $root; ?>/logout.php" class="block px-4 py-2 hover:bg-red-50 hover:text-red-600 rounded-md">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- Not logged in -->
                    <a href="<?php echo $root; ?>/login.php" class="hover:text-blue-200 <?php echo ($current_page == 'login.php') ? 'font-bold underline' : ''; ?>">
                        <i class="fas fa-sign-in-alt mr-1"></i>Login
                    </a>
                    <a href="<?php echo $root; ?>/register.php" class="bg-white text-blue-600 px-4 py-2 rounded-md hover:bg-blue-50 transition duration-300">
                        <i class="fas fa-user-plus mr-1"></i>Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
