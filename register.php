<?php
$page_title = "Register";
include 'includes/header.php';
include 'includes/auth_check.php';

redirectIfLoggedIn();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input
    $student_id = trim($_POST['student_id']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $course = trim($_POST['course']);
    $year_level = trim($_POST['year_level']);
    
    // Validation
    $errors = [];
    
    // Required fields
    $required = ['student_id', 'full_name', 'email', 'username', 'password', 'confirm_password'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
        }
    }
    
    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Password validation
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if username or email already exists
    if (empty($errors)) {
        try {
            // Check username
            $query = "SELECT id FROM users WHERE username = :username";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $errors[] = "Username already exists";
            }
            
            // Check email
            $query = "SELECT id FROM users WHERE email = :email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $errors[] = "Email already registered";
            }
            
            // Check student ID
            if (!empty($student_id)) {
                $query = "SELECT id FROM users WHERE student_id = :student_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $errors[] = "Student ID already registered";
                }
            }
        } catch(PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO users (student_id, username, email, password, full_name, role, course, year_level) 
                     VALUES (:student_id, :username, :email, :password, :full_name, 'student', :course, :year_level)";
            
            $stmt = $db->prepare($query);
            
            // Bind parameters
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
            $stmt->bindParam(':full_name', $full_name, PDO::PARAM_STR);
            $stmt->bindParam(':course', $course, PDO::PARAM_STR);
            $stmt->bindParam(':year_level', $year_level, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: login.php");
                exit();
            }
        } catch(PDOException $e) {
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
}
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 to-purple-50 py-12">
    <div class="max-w-4xl mx-auto px-4">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Student Registration</h1>
            <p class="text-lg text-gray-600">Create your account to access the attendance system</p>
        </div>
        
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="md:flex">
                <!-- Left side - Form -->
                <div class="md:w-2/3 p-8">
                    <?php if (!empty($errors)): ?>
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc pl-5 space-y-1">
                                            <?php foreach ($errors as $error): ?>
                                                <li><?php echo htmlspecialchars($error); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-6">
                        <!-- Personal Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Student ID *
                                </label>
                                <input type="text" id="student_id" name="student_id" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                       value="<?php echo isset($_POST['student_id']) ? htmlspecialchars($_POST['student_id']) : ''; ?>">
                            </div>
                            
                            <div>
                                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Full Name *
                                </label>
                                <input type="text" id="full_name" name="full_name" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                    Email Address *
                                </label>
                                <input type="email" id="email" name="email" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                            
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                                    Username *
                                </label>
                                <input type="text" id="username" name="username" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                            </div>
                        </div>
                        
                        <!-- Academic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="course" class="block text-sm font-medium text-gray-700 mb-1">
                                    Course/Program
                                </label>
                                <select id="course" name="course"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                    <option value="">Select Course</option>
                                    <option value="Computer Science" <?php echo (isset($_POST['course']) && $_POST['course'] == 'Computer Science') ? 'selected' : ''; ?>>Computer Science</option>
                                    <option value="Business Administration" <?php echo (isset($_POST['course']) && $_POST['course'] == 'Business Administration') ? 'selected' : ''; ?>>Business Administration</option>
                                    <option value="Engineering" <?php echo (isset($_POST['course']) && $_POST['course'] == 'Engineering') ? 'selected' : ''; ?>>Engineering</option>
                                    <option value="Nursing" <?php echo (isset($_POST['course']) && $_POST['course'] == 'Nursing') ? 'selected' : ''; ?>>Nursing</option>
                                    <option value="Education" <?php echo (isset($_POST['course']) && $_POST['course'] == 'Education') ? 'selected' : ''; ?>>Education</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="year_level" class="block text-sm font-medium text-gray-700 mb-1">
                                    Year Level
                                </label>
                                <select id="year_level" name="year_level"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                    <option value="">Select Year</option>
                                    <option value="1st Year" <?php echo (isset($_POST['year_level']) && $_POST['year_level'] == '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                                    <option value="2nd Year" <?php echo (isset($_POST['year_level']) && $_POST['year_level'] == '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                                    <option value="3rd Year" <?php echo (isset($_POST['year_level']) && $_POST['year_level'] == '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                                    <option value="4th Year" <?php echo (isset($_POST['year_level']) && $_POST['year_level'] == '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Password -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                    Password *
                                </label>
                                <input type="password" id="password" name="password" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                <p class="mt-1 text-xs text-gray-500">Minimum 6 characters</p>
                            </div>
                            
                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">
                                    Confirm Password *
                                </label>
                                <input type="password" id="confirm_password" name="confirm_password" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            </div>
                        </div>
                        
                        <!-- Terms and Submit -->
                        <div class="pt-4">
                            <div class="flex items-center mb-6">
                                <input type="checkbox" id="terms" name="terms" required
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="terms" class="ml-2 block text-sm text-gray-900">
                                    I agree to the <a href="#" class="text-blue-600 hover:text-blue-500">Terms of Service</a> and <a href="#" class="text-blue-600 hover:text-blue-500">Privacy Policy</a>
                                </label>
                            </div>
                            
                            <div class="flex gap-4">
                                <button type="submit" 
                                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-300 transform hover:scale-105">
                                    <i class="fas fa-user-plus mr-2"></i>Register Account
                                </button>
                                
                                <a href="login.php" 
                                   class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-3 px-4 rounded-lg text-center transition duration-300">
                                    <i class="fas fa-sign-in-alt mr-2"></i>Already have account?
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Right side - Info -->
                <div class="md:w-1/3 bg-gradient-to-b from-blue-600 to-purple-600 text-white p-8">
                    <div class="h-full flex flex-col justify-center">
                        <h3 class="text-2xl font-bold mb-6">Why Register?</h3>
                        
                        <ul class="space-y-4">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-300 mr-3 mt-1"></i>
                                <span>Track your attendance in real-time</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-300 mr-3 mt-1"></i>
                                <span>View detailed attendance reports</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-300 mr-3 mt-1"></i>
                                <span>Receive attendance notifications</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-300 mr-3 mt-1"></i>
                                <span>Access from any device</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-300 mr-3 mt-1"></i>
                                <span>Secure and private</span>
                            </li>
                        </ul>
                        
                        <div class="mt-10 p-4 bg-white bg-opacity-20 rounded-lg">
                            <h4 class="font-bold mb-2">Need Help?</h4>
                            <p class="text-sm opacity-90">
                                Contact your department administrator or email support@school.edu
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>