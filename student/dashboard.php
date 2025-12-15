<?php
// student/dashboard.php
require_once dirname(__DIR__) . '/bootstrap.php';
requireStudent();

$page_title = "Student Dashboard";

// Initialize variables with defaults
$student_info = ['course' => 'Not specified', 'year_level' => 'Not specified'];
$courses = [];
$today_attendance = [];
$attendance_summary = [
    'total_classes' => 0,
    'present_count' => 0,
    'absent_count' => 0,
    'late_count' => 0,
    'excused_count' => 0
];
$attendance_percentage = 0;
$upcoming_classes = [];
$recent_attendance = [];

$student_id = $_SESSION['user_id'];

try {
    // Get student info
    $student_info = fetchOne("SELECT course, year_level FROM users WHERE id = ?", [$student_id]);
    if (!$student_info) $student_info = ['course' => 'Not specified', 'year_level' => 'Not specified'];
    
    // Get student's courses
    $courses = fetchAll("
        SELECT c.* FROM courses c 
        JOIN enrollments e ON c.id = e.course_id 
        WHERE e.student_id = ? 
        ORDER BY c.course_name
    ", [$student_id]);
    
    // Get today's attendance
    $today = date('Y-m-d');
    $today_attendance = fetchAll("
        SELECT a.status, c.course_name, c.course_code 
        FROM attendance a 
        JOIN enrollments e ON a.enrollment_id = e.id 
        JOIN courses c ON e.course_id = c.id 
        WHERE e.student_id = ? AND a.date = ? 
        ORDER BY a.created_at DESC
    ", [$student_id, $today]);
    
    // Get attendance summary
    $attendance_summary = fetchOne("
        SELECT 
            COUNT(*) as total_classes,
            SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
            SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
            SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
            SUM(CASE WHEN a.status = 'excused' THEN 1 ELSE 0 END) as excused_count
        FROM attendance a 
        JOIN enrollments e ON a.enrollment_id = e.id 
        WHERE e.student_id = ?
    ", [$student_id]);
    
    if ($attendance_summary) {
        $attended = $attendance_summary['present_count'] + $attendance_summary['late_count'] + $attendance_summary['excused_count'];
        $total = $attendance_summary['total_classes'];
        $attendance_percentage = $total > 0 ? round(($attended / $total) * 100, 1) : 0;
    }
    
    // Get upcoming classes
    $today_name = date('l');
    $upcoming_classes = fetchAll("
        SELECT c.* FROM courses c 
        JOIN enrollments e ON c.id = e.course_id 
        WHERE e.student_id = ? 
        AND c.schedule_day LIKE ? 
        ORDER BY c.schedule_time
    ", [$student_id, "%{$today_name}%"]);
    
    // Get recent attendance
    $recent_attendance = fetchAll("
        SELECT a.*, c.course_name, c.course_code 
        FROM attendance a 
        JOIN enrollments e ON a.enrollment_id = e.id 
        JOIN courses c ON e.course_id = c.id 
        WHERE e.student_id = ? 
        ORDER BY a.date DESC 
        LIMIT 5
    ", [$student_id]);
    
} catch(PDOException $e) {
    // Silently handle errors
    error_log("Dashboard error: " . $e->getMessage());
}

// Include header
require_once dirname(__DIR__) . '/includes/header.php';
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
                        <p class="font-semibold"><?php echo htmlspecialchars($student_info['course']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm opacity-80">Year Level</p>
                        <p class="font-semibold"><?php echo htmlspecialchars($student_info['year_level']); ?></p>
                    </div>
                </div>
            </div>
            <div class="mt-4 md:mt-0 text-center">
                <div class="text-5xl font-bold"><?php echo $attendance_percentage; ?>%</div>
                <p class="opacity-90">Overall Attendance</p>
            </div>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="stat-card bg-white rounded-xl shadow-md p-4 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg mr-3">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-xs">Present</p>
                    <h3 class="text-xl font-bold"><?php echo $attendance_summary['present_count']; ?></h3>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white rounded-xl shadow-md p-4 border-l-4 border-red-500">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg mr-3">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-xs">Absent</p>
                    <h3 class="text-xl font-bold"><?php echo $attendance_summary['absent_count']; ?></h3>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white rounded-xl shadow-md p-4 border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg mr-3">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-xs">Late</p>
                    <h3 class="text-xl font-bold"><?php echo $attendance_summary['late_count']; ?></h3>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white rounded-xl shadow-md p-4 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg mr-3">
                    <i class="fas fa-book text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-xs">Total Classes</p>
                    <h3 class="text-xl font-bold"><?php echo $attendance_summary['total_classes']; ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Today's Attendance -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800 flex items-center">
                <i class="fas fa-calendar-day text-blue-500 mr-2"></i>
                Today's Attendance (<?php echo date('F j, Y'); ?>)
            </h2>
            
            <?php if (!empty($today_attendance)): ?>
                <div class="space-y-3">
                    <?php foreach($today_attendance as $attendance): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover-lift">
                            <div>
                                <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($attendance['course_name']); ?></h4>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($attendance['course_code']); ?></p>
                            </div>
                            <span class="px-3 py-1 text-sm font-semibold rounded-full status-<?php echo $attendance['status']; ?>">
                                <?php echo ucfirst($attendance['status']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-calendar-check text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500">No attendance recorded for today yet.</p>
                </div>
            <?php endif; ?>
            
            <!-- Upcoming Classes -->
            <?php if (!empty($upcoming_classes)): ?>
                <div class="mt-8">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800">Upcoming Classes Today</h3>
                    <div class="space-y-2">
                        <?php foreach($upcoming_classes as $class): ?>
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                <div>
                                    <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($class['course_name']); ?></h4>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($class['schedule_time']); ?></p>
                                </div>
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm font-medium rounded-full">
                                    <?php echo htmlspecialchars($class['schedule_day']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Recent Attendance & Enrolled Courses -->
        <div class="space-y-6">
            <!-- Recent Attendance -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold mb-4 text-gray-800">Recent Attendance</h2>
                
                <?php if (!empty($recent_attendance)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach($recent_attendance as $record): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo date('M d', strtotime($record['date'])); ?>
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($record['course_code']); ?></div>
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full status-<?php echo $record['status']; ?>">
                                                <?php echo ucfirst($record['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-center">
                        <a href="view_attendance.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            View All Attendance Records →
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-clipboard-list text-gray-300 text-4xl mb-3"></i>
                        <p class="text-gray-500">No attendance records found.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Enrolled Courses -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold mb-4 text-gray-800">Enrolled Courses</h2>
                
                <?php if (!empty($courses)): ?>
                    <div class="grid grid-cols-1 gap-3">
                        <?php foreach($courses as $course): ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-300">
                                <div>
                                    <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($course['course_name']); ?></h4>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($course['course_code']); ?></p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        <?php echo htmlspecialchars($course['schedule_day']); ?>
                                        at <?php echo htmlspecialchars($course['schedule_time']); ?>
                                    </p>
                                </div>
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm font-medium rounded-full">
                                    <?php echo htmlspecialchars($course['instructor']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-book-open text-gray-300 text-4xl mb-3"></i>
                        <p class="text-gray-500">You are not enrolled in any courses yet.</p>
                        <p class="text-sm text-gray-400 mt-2">Contact your administrator to get enrolled in courses.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-bold mb-4 text-gray-800">Attendance Overview</h2>
        <div class="space-y-4">
            <div>
                <div class="flex justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700">Overall Attendance Rate</span>
                    <span class="text-sm font-medium text-gray-700"><?php echo $attendance_percentage; ?>%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-green-600 h-2.5 rounded-full" style="width: <?php echo $attendance_percentage; ?>%"></div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 text-center">
                <div class="p-3 bg-green-50 rounded-lg">
                    <p class="text-2xl font-bold text-green-700"><?php echo $attendance_summary['present_count']; ?></p>
                    <p class="text-sm text-green-600">Present Days</p>
                </div>
                <div class="p-3 bg-red-50 rounded-lg">
                    <p class="text-2xl font-bold text-red-700"><?php echo $attendance_summary['absent_count']; ?></p>
                    <p class="text-sm text-red-600">Absent Days</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once dirname(__DIR__) . '/includes/footer.php';
?>