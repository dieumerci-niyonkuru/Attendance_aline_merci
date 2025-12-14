<?php
$page_title = "Take Attendance";
include '../includes/header.php';
include '../includes/auth_check.php';

requireRole('admin');

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['take_attendance'])) {
    $course_id = $_POST['course_id'];
    $attendance_date = $_POST['attendance_date'];
    $student_attendance = $_POST['attendance'];
    
    try {
        // Start transaction
        $db->beginTransaction();
        
        foreach ($student_attendance as $student_id => $status) {
            // Get enrollment ID
            $query = "SELECT id FROM enrollments WHERE student_id = :student_id AND course_id = :course_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
            $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($enrollment = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $enrollment_id = $enrollment['id'];
                
                // Check if attendance already exists
                $query = "SELECT id FROM attendance WHERE enrollment_id = :enrollment_id AND date = :date";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':enrollment_id', $enrollment_id, PDO::PARAM_INT);
                $stmt->bindParam(':date', $attendance_date, PDO::PARAM_STR);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    // Update existing attendance
                    $query = "UPDATE attendance SET status = :status, recorded_by = :recorded_by WHERE enrollment_id = :enrollment_id AND date = :date";
                } else {
                    // Insert new attendance
                    $query = "INSERT INTO attendance (enrollment_id, date, status, recorded_by) 
                             VALUES (:enrollment_id, :date, :status, :recorded_by)";
                }
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':enrollment_id', $enrollment_id, PDO::PARAM_INT);
                $stmt->bindParam(':date', $attendance_date, PDO::PARAM_STR);
                $stmt->bindParam(':status', $status, PDO::PARAM_STR);
                $stmt->bindParam(':recorded_by', $_SESSION['user_id'], PDO::PARAM_INT);
                $stmt->execute();
            }
        }
        
        $db->commit();
        $_SESSION['success'] = "Attendance recorded successfully!";
        
    } catch(PDOException $e) {
        $db->rollBack();
        $error = "Error recording attendance: " . $e->getMessage();
    }
}

// Get courses for dropdown
try {
    $query = "SELECT * FROM courses ORDER BY course_name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error loading courses: " . $e->getMessage();
}

// Get students for selected course
$students = [];
if (isset($_GET['course_id']) || isset($_POST['load_students'])) {
    $selected_course_id = isset($_POST['course_id']) ? $_POST['course_id'] : $_GET['course_id'];
    
    try {
        $query = "SELECT u.id, u.student_id, u.full_name, e.id as enrollment_id 
                  FROM users u 
                  JOIN enrollments e ON u.id = e.student_id 
                  WHERE e.course_id = :course_id AND u.role = 'student' 
                  ORDER BY u.full_name";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':course_id', $selected_course_id, PDO::PARAM_INT);
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get course info
        $query = "SELECT * FROM courses WHERE id = :course_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':course_id', $selected_course_id, PDO::PARAM_INT);
        $stmt->execute();
        $selected_course = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        $error = "Error loading students: " . $e->getMessage();
    }
}
?>

<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Take Attendance</h1>
        <p class="text-gray-600">Select a course and date to mark attendance for enrolled students.</p>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <!-- Course Selection Form -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <form method="POST" action="" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="course_id" class="block text-sm font-medium text-gray-700 mb-1">Select Course</label>
                    <select id="course_id" name="course_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Select Course --</option>
                        <?php foreach($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>"
                                <?php echo (isset($selected_course_id) && $selected_course_id == $course['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="attendance_date" class="block text-sm font-medium text-gray-700 mb-1">Attendance Date</label>
                    <input type="date" id="attendance_date" name="attendance_date" required
                           value="<?php echo isset($_POST['attendance_date']) ? $_POST['attendance_date'] : date('Y-m-d'); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="flex items-end">
                    <button type="submit" name="load_students"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md transition duration-300">
                        Load Students
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Attendance Form -->
    <?php if (isset($selected_course) && !empty($students)): ?>
        <form method="POST" action="">
            <input type="hidden" name="course_id" value="<?php echo $selected_course_id; ?>">
            <input type="hidden" name="attendance_date" value="<?php echo isset($_POST['attendance_date']) ? $_POST['attendance_date'] : date('Y-m-d'); ?>">
            
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-blue-50 to-purple-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($selected_course['course_name']); ?></h2>
                            <p class="text-gray-600"><?php echo htmlspecialchars($selected_course['course_code']); ?></p>
                            <p class="text-sm text-gray-500 mt-1">
                                Date: <?php echo date('F j, Y', strtotime($_POST['attendance_date'])); ?> | 
                                Students: <?php echo count($students); ?>
                            </p>
                        </div>
                        <button type="submit" name="take_attendance"
                                class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-md transition duration-300">
                            <i class="fas fa-save mr-2"></i>Save Attendance
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Attendance</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach($students as $student): ?>
                                <?php
                                // Get last attendance for this student in this course
                                try {
                                    $query = "SELECT status, date FROM attendance a 
                                             JOIN enrollments e ON a.enrollment_id = e.id 
                                             WHERE e.student_id = :student_id AND e.course_id = :course_id 
                                             ORDER BY a.date DESC LIMIT 1";
                                    $stmt = $db->prepare($query);
                                    $stmt->bindParam(':student_id', $student['id'], PDO::PARAM_INT);
                                    $stmt->bindParam(':course_id', $selected_course_id, PDO::PARAM_INT);
                                    $stmt->execute();
                                    $last_attendance = $stmt->fetch(PDO::FETCH_ASSOC);
                                } catch(PDOException $e) {
                                    $last_attendance = null;
                                }
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($student['student_id']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex space-x-4">
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="present" 
                                                       class="text-green-600 focus:ring-green-500" checked>
                                                <span class="ml-2 text-sm text-gray-700">Present</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="absent" 
                                                       class="text-red-600 focus:ring-red-500">
                                                <span class="ml-2 text-sm text-gray-700">Absent</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="late" 
                                                       class="text-yellow-600 focus:ring-yellow-500">
                                                <span class="ml-2 text-sm text-gray-700">Late</span>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php if ($last_attendance): ?>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                                <?php echo $last_attendance['status'] == 'present' ? 'bg-green-100 text-green-800' : 
                                                       ($last_attendance['status'] == 'absent' ? 'bg-red-100 text-red-800' : 
                                                       ($last_attendance['status'] == 'late' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')); ?>">
                                                <?php echo ucfirst($last_attendance['status']); ?>
                                            </span>
                                            on <?php echo date('M d', strtotime($last_attendance['date'])); ?>
                                        <?php else: ?>
                                            <span class="text-gray-400">No record</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Quick Actions -->
                <div class="p-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="markAll('present')" 
                                class="bg-green-100 hover:bg-green-200 text-green-700 px-4 py-2 rounded-md text-sm font-medium">
                            Mark All Present
                        </button>
                        <button type="button" onclick="markAll('absent')" 
                                class="bg-red-100 hover:bg-red-200 text-red-700 px-4 py-2 rounded-md text-sm font-medium">
                            Mark All Absent
                        </button>
                        <button type="button" onclick="markAll('late')" 
                                class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 px-4 py-2 rounded-md text-sm font-medium">
                            Mark All Late
                        </button>
                    </div>
                </div>
            </div>
        </form>
        
        <script>
        function markAll(status) {
            const radioButtons = document.querySelectorAll('input[type="radio"]');
            radioButtons.forEach(radio => {
                if (radio.value === status) {
                    radio.checked = true;
                }
            });
        }
        </script>
        
    <?php elseif (isset($selected_course) && empty($students)): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
            <i class="fas fa-exclamation-triangle text-yellow-500 text-3xl mb-4"></i>
            <h3 class="text-lg font-semibold text-yellow-800 mb-2">No Students Enrolled</h3>
            <p class="text-yellow-700">There are no students enrolled in this course yet.</p>
            <a href="manage_students.php" class="inline-block mt-4 text-blue-600 hover:text-blue-800 font-medium">
                <i class="fas fa-user-plus mr-1"></i> Enroll Students
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>