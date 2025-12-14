<?php
$page_title = "Home";
include 'includes/header.php';
include 'includes/auth_check.php';

redirectIfLoggedIn();
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-r from-blue-500 to-purple-600">
    <div class="text-center text-white px-4">
        <h1 class="text-5xl font-bold mb-6 animate-pulse">
            <i class="fas fa-graduation-cap mr-4"></i>
            Welcome to Student Attendance System
        </h1>
        
        <p class="text-xl mb-10 max-w-2xl mx-auto">
            A comprehensive system for managing student attendance, tracking participation, 
            and generating reports for educational institutions.
        </p>
        
        <div class="flex flex-col sm:flex-row justify-center gap-6">
            <a href="login.php" 
               class="bg-white text-blue-600 hover:bg-blue-50 px-8 py-4 rounded-lg text-lg font-semibold transform hover:scale-105 transition duration-300 shadow-lg">
                <i class="fas fa-sign-in-alt mr-2"></i>Login to System
            </a>
            
            <a href="register.php" 
               class="bg-transparent border-2 border-white hover:bg-white hover:text-blue-600 px-8 py-4 rounded-lg text-lg font-semibold transform hover:scale-105 transition duration-300">
                <i class="fas fa-user-plus mr-2"></i>Register as Student
            </a>
        </div>
        
        <!-- Features Section -->
        <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white bg-opacity-10 p-6 rounded-xl backdrop-blur-sm">
                <i class="fas fa-user-check text-4xl mb-4"></i>
                <h3 class="text-2xl font-bold mb-3">Easy Attendance</h3>
                <p>Quick and efficient attendance tracking with QR code support</p>
            </div>
            
            <div class="bg-white bg-opacity-10 p-6 rounded-xl backdrop-blur-sm">
                <i class="fas fa-chart-line text-4xl mb-4"></i>
                <h3 class="text-2xl font-bold mb-3">Real-time Reports</h3>
                <p>Generate comprehensive attendance reports instantly</p>
            </div>
            
            <div class="bg-white bg-opacity-10 p-6 rounded-xl backdrop-blur-sm">
                <i class="fas fa-mobile-alt text-4xl mb-4"></i>
                <h3 class="text-2xl font-bold mb-3">Mobile Friendly</h3>
                <p>Access the system from any device, anywhere</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>