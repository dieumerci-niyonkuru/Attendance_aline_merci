<?php
$page_title = "Student Dashboard";
include '../includes/header.php';
include '../includes/auth_check.php';

requireRole('student');

$student_id = $_SESSION['user_id'];

// ============================
// DEFAULTS TO PREVENT WARNINGS
// ============================
$attendance_percentage = 0;
$attendance_summary = [
    'present_count' => 0,
    'absent_count' => 0,
    'late_count' => 0,
    'excused_count' => 0
];
$total = 0;

try {
    // Get student's courses
    $query = "SELECT c.* FROM courses c 
              JOIN enrollments e ON c.id = e.course_id 
              WHERE e.student_id = :student_id 
              ORDER BY c.course_name";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get today's attendance
    $today = date('Y-m-d');
    $query = "SELECT a.status, c.course_name, c.course_code 
              FROM attendance a 
              JOIN enrollments e ON a.enrollment_id = e.id 
              JOIN courses c ON e.course_id = c.id 
              WHERE e.student_id = :student_id AND a.date = :today 
              ORDER BY a.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->bindParam(':today', $today, PDO::PARAM_STR);
    $stmt->execute();
    $today_attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get attendance summary
    $query = "SELECT 
                COUNT(*) as total_classes,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN a.status = 'excused' THEN 1 ELSE 0 END) as excused_count
              FROM attendance a 
              JOIN enrollments e ON a.enrollment_id = e.id 
              WHERE e.student_id = :student_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $attendance_summary = $result;
        $attended = $attendance_summary['present_count'] + $attendance_summary['late_count'] + $attendance_summary['excused_count'];
        $total = $attendance_summary['total_classes'];
        $attendance_percentage = $total > 0 ? round(($attended / $total) * 100, 1) : 0;
    }
    
    // Get upcoming classes
    $today_name = date('l');
    $search_term = "%{$today_name}%";
    $query = "SELECT c.* FROM courses c 
              JOIN enrollments e ON c.id = e.course_id 
              WHERE e.student_id = :student_id 
              AND c.schedule_day LIKE :today 
              ORDER BY c.schedule_time";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->bindParam(':today', $search_term, PDO::PARAM_STR);
    $stmt->execute();
    $upcoming_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent attendance
    $query = "SELECT a.*, c.course_name, c.course_code 
              FROM attendance a 
              JOIN enrollments e ON a.enrollment_id = e.id 
              JOIN courses c ON e.course_id = c.id 
              WHERE e.student_id = :student_id 
              ORDER BY a.date DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $recent_attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
    // Optionally, log the error or display a friendly message
}
?>

<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-green-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
                <p class="opacity-90">Student ID: <?php echo htmlspecialchars($_SESSION['student_id']); ?></p>
                <div class="flex items-center mt-4">
                    <div class="mr-6">
                        <p class="text-sm opacity-80">Course</p>
                        <p class="font-semibold"><?php echo isset($_SESSION['course']) ? htmlspecialchars($_SESSION['course']) : 'Not specified'; ?></p>
                    </div>
                    <div>
                        <p class="text-sm opacity-80">Year Level</p>
                        <p class="font-semibold"><?php echo isset($_SESSION['year_level']) ? htmlspecialchars($_SESSION['year_level']) : 'Not specified'; ?></p>
                    </div>
                </div>
            </div>
            <div class="mt-4 md:mt-0 text-center">
                <div class="text-5xl font-bold"><?php echo $attendance_percentage; ?>%</div>
                <p class="opacity-90">Overall Attendance</p>
            </div>
        </div>
    </div>

    <!-- Rest of your dashboard content remains unchanged... -->

<?php include '../includes/footer.php'; ?>
