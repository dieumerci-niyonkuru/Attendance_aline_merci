<?php
// register.php - Registration page
require_once 'bootstrap.php';
redirectIfLoggedIn();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = trim($_POST['student_id']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $course = trim($_POST['course']);
    $year_level = trim($_POST['year_level']);
    
    // Validation
    if (empty($student_id)) $errors[] = "Student ID is required";
    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($username)) $errors[] = "Username is required";
    if (empty($password)) $errors[] = "Password is required";
    if (empty($confirm_password)) $errors[] = "Confirm password is required";
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($errors)) {
        try {
            // Check if username, email or student ID already exists
            $sql = "SELECT id FROM users WHERE username = :username OR email = :email OR student_id = :student_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':student_id', $student_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $errors[] = "Username, email or student ID already exists";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $sql = "INSERT INTO users (student_id, username, email, password, full_name, role, course, year_level) 
                       VALUES (:student_id, :username, :email, :password, :full_name, 'student', :course, :year_level)";
                
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':student_id', $student_id);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':full_name', $full_name);
                $stmt->bindParam(':course', $course);
                $stmt->bindParam(':year_level', $year_level);
                
                if ($stmt->execute()) {
                    $success = "Registration successful! You can now login.";
                } else {
                    $errors[] = "Registration failed. Please try again.";
                }
            }
        } catch(PDOException $e) {
            $errors[] = "Registration error: " . $e->getMessage();
        }
    }
}

// Set page title for header
$page_title = "Register - " . SITE_NAME;
require_once 'includes/header.php';
?>

<div class="max-w-2xl mx-auto bg-white rounded-lg shadow-xl p-8">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Student Registration</h1>
        <p class="text-gray-600 mt-2">Create your account to access the attendance system</p>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 alert">
            <div class="flex justify-between items-start">
                <div>
                    <strong class="font-bold">Error!</strong>
                    <ul class="list-disc pl-5 mt-2">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <button type="button" class="text-red-700 hover:text-red-900" onclick="this.parentElement.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 alert">
            <div class="flex justify-between items-start">
                <div>
                    <strong class="font-bold">Success!</strong>
                    <p class="mt-1"><?php echo htmlspecialchars($success); ?></p>
                    <p class="mt-2">
                        <a href="login.php" class="text-green-800 font-semibold hover:underline inline-flex items-center">
                            Click here to login <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </p>
                </div>
                <button type="button" class="text-green-700 hover:text-green-900" onclick="this.parentElement.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" class="space-y-6" id="registrationForm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Student ID *</label>
                <input type="text" name="student_id" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300"
                       placeholder="Enter student ID"
                       value="<?php echo isset($_POST['student_id']) ? htmlspecialchars($_POST['student_id']) : ''; ?>">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                <input type="text" name="full_name" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300"
                       placeholder="Enter full name"
                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                <input type="email" name="email" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300"
                       placeholder="Enter email address"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
                <input type="text" name="username" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300"
                       placeholder="Choose a username"
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                <div class="relative">
                    <input type="password" name="password" id="password" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300"
                           placeholder="Enter password">
                    <button type="button" class="absolute right-3 top-2 text-gray-500" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
                <div class="relative">
                    <input type="password" name="confirm_password" id="confirm_password" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300"
                           placeholder="Confirm password">
                    <button type="button" class="absolute right-3 top-2 text-gray-500" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Course/Program</label>
                <select name="course" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300">
                    <option value="">Select Course</option>
                    <option value="Computer Science" <?php echo (isset($_POST['course']) && $_POST['course'] == 'Computer Science') ? 'selected' : ''; ?>>Computer Science</option>
                    <option value="Business Administration" <?php echo (isset($_POST['course']) && $_POST['course'] == 'Business Administration') ? 'selected' : ''; ?>>Business Administration</option>
                    <option value="Engineering" <?php echo (isset($_POST['course']) && $_POST['course'] == 'Engineering') ? 'selected' : ''; ?>>Engineering</option>
                    <option value="Nursing" <?php echo (isset($_POST['course']) && $_POST['course'] == 'Nursing') ? 'selected' : ''; ?>>Nursing</option>
                    <option value="Education" <?php echo (isset($_POST['course']) && $_POST['course'] == 'Education') ? 'selected' : ''; ?>>Education</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Year Level</label>
                <select name="year_level" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300">
                    <option value="">Select Year</option>
                    <option value="1st Year" <?php echo (isset($_POST['year_level']) && $_POST['year_level'] == '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                    <option value="2nd Year" <?php echo (isset($_POST['year_level']) && $_POST['year_level'] == '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                    <option value="3rd Year" <?php echo (isset($_POST['year_level']) && $_POST['year_level'] == '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                    <option value="4th Year" <?php echo (isset($_POST['year_level']) && $_POST['year_level'] == '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                </select>
            </div>
        </div>
        
        <div class="flex items-center">
            <input type="checkbox" id="terms" name="terms" required 
                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="terms" class="ml-2 text-sm text-gray-700">
                I agree to the <a href="<?php echo SITE_URL; ?>terms.php" class="text-blue-600 hover:underline">terms and conditions</a>
            </label>
        </div>
        
        <div class="pt-4">
            <button type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-md transition duration-300 flex items-center justify-center">
                <i class="fas fa-user-plus mr-2"></i>Register Account
            </button>
            
            <p class="text-center mt-4 text-sm text-gray-600">
                Already have an account? 
                <a href="login.php" class="text-blue-600 hover:text-blue-800 font-medium">
                    Login here
                </a>
            </p>
        </div>
    </form>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = field.nextElementSibling.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Password strength indicator
document.getElementById('password').addEventListener('input', function(e) {
    const password = e.target.value;
    const strength = checkPasswordStrength(password);
    
    // You can add visual feedback here
    if (password.length > 0) {
        if (strength < 2) {
            e.target.classList.add('border-red-500');
            e.target.classList.remove('border-green-500', 'border-yellow-500');
        } else if (strength < 4) {
            e.target.classList.add('border-yellow-500');
            e.target.classList.remove('border-red-500', 'border-green-500');
        } else {
            e.target.classList.add('border-green-500');
            e.target.classList.remove('border-red-500', 'border-yellow-500');
        }
    } else {
        e.target.classList.remove('border-red-500', 'border-yellow-500', 'border-green-500');
    }
});

function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 6) strength++;
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    return strength;
}
</script>

<?php 
require_once 'includes/footer.php';
?>