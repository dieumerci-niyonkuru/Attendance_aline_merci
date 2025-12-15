<?php
// login.php - Login page
require_once 'bootstrap.php';
redirectIfLoggedIn();

$error = '';
$username_input = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $username_input = htmlspecialchars($username);
    
    try {
        $sql = "SELECT id, student_id, username, email, password, full_name, role, course, year_level 
                FROM users WHERE username = :username OR email = :username";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['student_id'] = $user['student_id'];
                $_SESSION['course'] = $user['course'];
                $_SESSION['year_level'] = $user['year_level'];
                
                if ($user['role'] == 'admin') {
                    header("Location: " . SITE_URL . "admin/dashboard.php");
                } else {
                    header("Location: " . SITE_URL . "student/dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid username or password";
            }
        } else {
            $error = "Invalid username or password";
        }
    } catch(PDOException $e) {
        $error = "Login error. Please try again.";
    }
}

// Set page title for header
$page_title = "Login - " . SITE_NAME;
require_once 'includes/header.php';
?>

<div class="min-h-[85vh] flex flex-col justify-center">
    <div class="max-w-md w-full mx-auto px-4">
        <!-- Logo/Brand -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl shadow-md mb-4">
                <i class="fas fa-calendar-check text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800"><?php echo SITE_NAME; ?></h1>
            <p class="text-gray-600 text-sm mt-1">Student Attendance System</p>
        </div>
        
        <!-- Login Card -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
            <!-- Card Header -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Sign In to Your Account</h2>
                <p class="text-gray-600 text-sm">Enter your credentials to continue</p>
            </div>
            
            <!-- Card Body -->
            <div class="p-6">
                <?php if ($error): ?>
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg flex items-start">
                        <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-3"></i>
                        <div class="flex-1">
                            <p class="text-red-700 text-sm"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endif; ?>
                
                <!-- Login Form -->
                <form method="POST" action="" class="space-y-4" id="loginForm">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username or Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" name="username" required 
                                   class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                   placeholder="Enter username or email"
                                   value="<?php echo $username_input; ?>">
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <a href="#" class="text-xs text-blue-600 hover:text-blue-800 hover:underline">
                                Forgot password?
                            </a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" name="password" id="password" required 
                                   class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                   placeholder="Enter password">
                            <button type="button" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700"
                                    onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" 
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 text-sm text-gray-700">
                            Remember me
                        </label>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3 px-4 rounded-lg transition duration-300 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                    </button>
                </form>
                
                <!-- Register Link -->
                <div class="mt-6 pt-5 border-t border-gray-200">
                    <p class="text-center text-sm text-gray-600">
                        Don't have an account? 
                        <a href="register.php" class="text-blue-600 hover:text-blue-800 font-medium hover:underline">
                            Create one now
                        </a>
                    </p>
                </div>
            </div>
            
            <!-- Card Footer - Optional Info -->
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    <span>Contact administrator if you need assistance</span>
                </div>
            </div>
        </div>
        
        <!-- Footer Links -->
        <div class="mt-4 text-center">
            <p class="text-xs text-gray-500">
                © <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. 
                <a href="<?php echo SITE_URL; ?>terms.php" class="text-blue-600 hover:underline">Terms</a> • 
                <a href="<?php echo SITE_URL; ?>privacy.php" class="text-blue-600 hover:underline">Privacy</a>
            </p>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = field.parentElement.querySelector('button i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Form submission loading state
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        const originalHTML = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Signing in...';
        
        // Revert after 5 seconds if page doesn't redirect (safety)
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
        }, 5000);
    }
});
</script>

<?php 
require_once 'includes/footer.php';
?>