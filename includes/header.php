<?php
// includes/header.php - Header template
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' | ' . SITE_NAME : SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out;
        }
        
        .nav-active { 
            position: relative; 
            font-weight: 600; 
        }
        
        .nav-active::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            border-radius: 2px;
        }
        
        .status-present { background-color: #d1fae5; color: #065f46; }
        .status-absent { background-color: #fee2e2; color: #991b1b; }
        .status-late { background-color: #fef3c7; color: #92400e; }
        .status-excused { background-color: #dbeafe; color: #1e40af; }
        
        .hover-lift:hover { 
            transform: translateY(-2px); 
            transition: transform 0.2s ease, box-shadow 0.2s ease; 
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }
        
        .stat-card { 
            transition: all 0.3s ease; 
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }
        
        .stat-card:hover { 
            transform: translateY(-5px) scale(1.02); 
            box-shadow: 0 12px 24px rgba(0,0,0,0.1); 
        }
        
        /* Glass effect */
        .glass-nav {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
    <!-- Navigation -->
    <nav class="glass-nav sticky top-0 z-50 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-3">
                <!-- Logo and Brand -->
                <div class="flex items-center space-x-3">
                    <div class="bg-gradient-to-br from-blue-500 to-purple-600 w-10 h-10 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-calendar-check text-white text-lg"></i>
                    </div>
                    <div>
                        <a href="/student-attendance-system/" class="text-xl font-bold text-gray-800 hover:text-blue-600 transition duration-300">
                            <span class="gradient-text"><?php echo SITE_NAME; ?></span>
                        </a>
                        <p class="text-xs text-gray-500 mt-0.5">Attendance Management System</p>
                    </div>
                </div>
                
                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-1">
                    <?php if(isLoggedIn()): ?>
                        <?php if(isAdmin()): ?>
                            <!-- Admin Navigation -->
                            <a href="/student-attendance-system/admin/dashboard.php" 
                               class="px-4 py-2.5 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition duration-300 hover-lift <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'nav-active text-blue-600 bg-blue-50' : ''; ?>">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                            </a>
                            <a href="/student-attendance-system/admin/manage_students.php" 
                               class="px-4 py-2.5 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition duration-300 hover-lift <?php echo basename($_SERVER['PHP_SELF']) == 'manage_students.php' ? 'nav-active text-blue-600 bg-blue-50' : ''; ?>">
                                <i class="fas fa-users mr-2"></i>Students
                            </a>
                            <a href="/student-attendance-system/admin/manage_courses.php" 
                               class="px-4 py-2.5 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition duration-300 hover-lift <?php echo basename($_SERVER['PHP_SELF']) == 'manage_courses.php' ? 'nav-active text-blue-600 bg-blue-50' : ''; ?>">
                                <i class="fas fa-book mr-2"></i>Courses
                            </a>
                            <a href="/student-attendance-system/admin/take_attendance.php" 
                               class="px-4 py-2.5 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition duration-300 hover-lift <?php echo basename($_SERVER['PHP_SELF']) == 'take_attendance.php' ? 'nav-active text-blue-600 bg-blue-50' : ''; ?>">
                                <i class="fas fa-clipboard-check mr-2"></i>Attendance
                            </a>
                        <?php else: ?>
                            <!-- Student Navigation -->
                            <a href="/student-attendance-system/student/dashboard.php" 
                               class="px-4 py-2.5 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition duration-300 hover-lift <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'nav-active text-blue-600 bg-blue-50' : ''; ?>">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                            </a>
                            <a href="/student-attendance-system/student/view_attendance.php" 
                               class="px-4 py-2.5 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition duration-300 hover-lift <?php echo basename($_SERVER['PHP_SELF']) == 'view_attendance.php' ? 'nav-active text-blue-600 bg-blue-50' : ''; ?>">
                                <i class="fas fa-calendar-check mr-2"></i>My Attendance
                            </a>
                            <a href="/student-attendance-system/student/profile.php" 
                               class="px-4 py-2.5 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition duration-300 hover-lift <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'nav-active text-blue-600 bg-blue-50' : ''; ?>">
                                <i class="fas fa-user mr-2"></i>Profile
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <!-- User Info / Auth Buttons -->
                <div class="flex items-center space-x-4">
                    <?php if(isLoggedIn()): ?>
                        <!-- User Profile Dropdown -->
                        <div class="relative group">
                            <button class="flex items-center space-x-2 bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm hover:shadow-md transition duration-300">
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-white text-sm"></i>
                                </div>
                                <div class="text-left">
                                    <p class="text-xs font-medium text-gray-700"><?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
                                    <p class="text-xs text-gray-500 capitalize"><?php echo $_SESSION['user_role']; ?></p>
                                </div>
                                <i class="fas fa-chevron-down text-gray-400 text-xs ml-1"></i>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0 z-50 animate-fadeIn">
                                <div class="py-1">
                                    <div class="px-4 py-3 border-b border-gray-100">
                                        <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo $_SESSION['email']; ?></p>
                                    </div>
                                    <?php if(isAdmin()): ?>
                                        <a href="/student-attendance-system/admin/profile.php" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-blue-50">
                                            <i class="fas fa-user-circle mr-3 text-blue-500"></i>My Profile
                                        </a>
                                    <?php else: ?>
                                        <a href="/student-attendance-system/student/profile.php" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-blue-50">
                                            <i class="fas fa-user-circle mr-3 text-blue-500"></i>My Profile
                                        </a>
                                    <?php endif; ?>
                                    <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-blue-50">
                                        <i class="fas fa-cog mr-3 text-gray-500"></i>Settings
                                    </a>
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <!-- LOGOUT IS OUTSIDE STUDENT FOLDER -->
                                    <a href="/student-attendance-system/logout.php" class="flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50">
                                        <i class="fas fa-sign-out-alt mr-3"></i>Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                    <?php else: ?>
                        <!-- Public Navigation -->
                        <div class="flex items-center space-x-3">
                            <a href="/student-attendance-system/login.php" 
                               class="text-sm font-medium text-gray-700 hover:text-blue-600 transition duration-300 px-4 py-2 hover:bg-blue-50 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'nav-active text-blue-600 bg-blue-50' : ''; ?>">
                                <i class="fas fa-sign-in-alt mr-2"></i>Login
                            </a>
                            <a href="/student-attendance-system/register.php" 
                               class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium px-5 py-2.5 rounded-lg shadow-md hover:shadow-lg transition duration-300 transform hover:-translate-y-0.5">
                                <i class="fas fa-user-plus mr-2"></i>Register
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-button" class="md:hidden text-gray-600 hover:text-blue-600 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden bg-white rounded-lg shadow-lg p-4 mt-2 border border-gray-200 animate-fadeIn">
                <?php if(isLoggedIn()): ?>
                    <?php if(isAdmin()): ?>
                        <!-- Admin Mobile Menu -->
                        <a href="/student-attendance-system/admin/dashboard.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg mb-1 <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-blue-50 text-blue-600' : ''; ?>">
                            <i class="fas fa-tachometer-alt mr-3 text-blue-500"></i>Dashboard
                        </a>
                        <a href="/student-attendance-system/admin/manage_students.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg mb-1 <?php echo basename($_SERVER['PHP_SELF']) == 'manage_students.php' ? 'bg-blue-50 text-blue-600' : ''; ?>">
                            <i class="fas fa-users mr-3 text-green-500"></i>Students
                        </a>
                        <a href="/student-attendance-system/admin/manage_courses.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg mb-1 <?php echo basename($_SERVER['PHP_SELF']) == 'manage_courses.php' ? 'bg-blue-50 text-blue-600' : ''; ?>">
                            <i class="fas fa-book mr-3 text-purple-500"></i>Courses
                        </a>
                        <a href="/student-attendance-system/admin/take_attendance.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg mb-1 <?php echo basename($_SERVER['PHP_SELF']) == 'take_attendance.php' ? 'bg-blue-50 text-blue-600' : ''; ?>">
                            <i class="fas fa-clipboard-check mr-3 text-orange-500"></i>Attendance
                        </a>
                    <?php else: ?>
                        <!-- Student Mobile Menu -->
                        <a href="/student-attendance-system/student/dashboard.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg mb-1 <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-blue-50 text-blue-600' : ''; ?>">
                            <i class="fas fa-tachometer-alt mr-3 text-blue-500"></i>Dashboard
                        </a>
                        <a href="/student-attendance-system/student/view_attendance.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg mb-1 <?php echo basename($_SERVER['PHP_SELF']) == 'view_attendance.php' ? 'bg-blue-50 text-blue-600' : ''; ?>">
                            <i class="fas fa-calendar-check mr-3 text-green-500"></i>My Attendance
                        </a>
                        <a href="/student-attendance-system/student/profile.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg mb-1 <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'bg-blue-50 text-blue-600' : ''; ?>">
                            <i class="fas fa-user mr-3 text-purple-500"></i>Profile
                        </a>
                    <?php endif; ?>
                    <div class="border-t border-gray-200 my-3"></div>
                    <!-- LOGOUT IS OUTSIDE STUDENT FOLDER -->
                    <a href="/student-attendance-system/logout.php" class="flex items-center px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg">
                        <i class="fas fa-sign-out-alt mr-3"></i>Logout
                    </a>
                <?php else: ?>
                    <!-- Public Mobile Menu -->
                    <a href="/student-attendance-system/login.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 rounded-lg mb-2 <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'bg-blue-50 text-blue-600' : ''; ?>">
                        <i class="fas fa-sign-in-alt mr-3 text-blue-500"></i>Login
                    </a>
                    <a href="/student-attendance-system/register.php" class="flex items-center px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg justify-center">
                        <i class="fas fa-user-plus mr-3"></i>Register Now
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8">
        <!-- Page Header -->
        <div class="mb-6 md:mb-8">
            <?php if(isset($page_title) && !in_array(basename($_SERVER['PHP_SELF']), ['dashboard.php', 'index.php'])): ?>
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900"><?php echo str_replace(' - ' . SITE_NAME, '', $page_title); ?></h1>
                        <?php if(isset($page_subtitle)): ?>
                            <p class="text-gray-600 mt-1"><?php echo $page_subtitle; ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if(isset($page_actions)): ?>
                        <div class="flex items-center space-x-3">
                            <?php echo $page_actions; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="h-1 w-24 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full mt-3"></div>
            <?php endif; ?>
        </div>
        
        <!-- Content Area -->
        <div class="animate-fadeIn">