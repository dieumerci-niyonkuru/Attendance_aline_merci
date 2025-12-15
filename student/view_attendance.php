<?php
// student/view_attendance.php
require_once dirname(__DIR__) . '/bootstrap.php';
requireStudent();

$page_title = "My Attendance";

// Initialize variables
$student_id = $_SESSION['user_id'];
$filter_course = $_GET['course'] ?? '';
$filter_month = $_GET['month'] ?? date('Y-m');
$filter_status = $_GET['status'] ?? '';

// Default values
$student_courses = [];
$attendance_records = [];
$summary = [
    'total' => 0,
    'present' => 0,
    'absent' => 0,
    'late' => 0,
    'excused' => 0
];

try {
    // Get student's courses for filter dropdown
    $student_courses = fetchAll("
        SELECT c.id, c.course_code, c.course_name 
        FROM courses c 
        JOIN enrollments e ON c.id = e.course_id 
        WHERE e.student_id = ? 
        ORDER BY c.course_name
    ", [$student_id]);
    
    // Build base query
    $query = "
        SELECT a.*, c.course_code, c.course_name, c.instructor 
        FROM attendance a 
        JOIN enrollments e ON a.enrollment_id = e.id 
        JOIN courses c ON e.course_id = c.id 
        WHERE e.student_id = ?
    ";
    
    $params = [$student_id];
    
    // Apply filters
    if ($filter_course) {
        $query .= " AND c.id = ?";
        $params[] = $filter_course;
    }
    
    if ($filter_month) {
        $query .= " AND DATE_FORMAT(a.date, '%Y-%m') = ?";
        $params[] = $filter_month;
    }
    
    if ($filter_status) {
        $query .= " AND a.status = ?";
        $params[] = $filter_status;
    }
    
    $query .= " ORDER BY a.date DESC";
    
    $attendance_records = fetchAll($query, $params);
    
    // Get summary
    $summary_query = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent,
            SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late,
            SUM(CASE WHEN a.status = 'excused' THEN 1 ELSE 0 END) as excused
        FROM attendance a 
        JOIN enrollments e ON a.enrollment_id = e.id 
        JOIN courses c ON e.course_id = c.id 
        WHERE e.student_id = ?
    ";
    
    $summary_params = [$student_id];
    
    if ($filter_course) {
        $summary_query .= " AND c.id = ?";
        $summary_params[] = $filter_course;
    }
    
    if ($filter_month) {
        $summary_query .= " AND DATE_FORMAT(a.date, '%Y-%m') = ?";
        $summary_params[] = $filter_month;
    }
    
    if ($filter_status) {
        $summary_query .= " AND a.status = ?";
        $summary_params[] = $filter_status;
    }
    
    $summary_result = fetchOne($summary_query, $summary_params);
    if ($summary_result) {
        $summary = $summary_result;
    }
    
} catch(PDOException $e) {
    error_log("View attendance error: " . $e->getMessage());
}

// Include header
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">My Attendance Records</h1>
        <p class="text-gray-600">View and filter your attendance history across all courses.</p>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <form method="GET" action="" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Course</label>
                    <select name="course" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Courses</option>
                        <?php foreach($student_courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>" <?php echo $filter_course == $course['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                    <input type="month" name="month" value="<?php echo $filter_month; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="present" <?php echo $filter_status == 'present' ? 'selected' : ''; ?>>Present</option>
                        <option value="absent" <?php echo $filter_status == 'absent' ? 'selected' : ''; ?>>Absent</option>
                        <option value="late" <?php echo $filter_status == 'late' ? 'selected' : ''; ?>>Late</option>
                        <option value="excused" <?php echo $filter_status == 'excused' ? 'selected' : ''; ?>>Excused</option>
                    </select>
                </div>
                
                <div class="flex items-end space-x-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md transition duration-300">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                    <a href="view_attendance.php" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-md text-center transition duration-300">
                        <i class="fas fa-redo mr-2"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl shadow-md p-4 text-center">
            <div class="text-2xl font-bold text-gray-800"><?php echo $summary['total']; ?></div>
            <p class="text-sm text-gray-600">Total</p>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-4 text-center border-t-4 border-green-500">
            <div class="text-2xl font-bold text-green-600"><?php echo $summary['present']; ?></div>
            <p class="text-sm text-gray-600">Present</p>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-4 text-center border-t-4 border-red-500">
            <div class="text-2xl font-bold text-red-600"><?php echo $summary['absent']; ?></div>
            <p class="text-sm text-gray-600">Absent</p>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-4 text-center border-t-4 border-yellow-500">
            <div class="text-2xl font-bold text-yellow-600"><?php echo $summary['late']; ?></div>
            <p class="text-sm text-gray-600">Late</p>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-4 text-center border-t-4 border-blue-500">
            <div class="text-2xl font-bold text-blue-600"><?php echo $summary['excused']; ?></div>
            <p class="text-sm text-gray-600">Excused</p>
        </div>
    </div>
    
    <!-- Attendance Records -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Attendance Records</h2>
            <p class="text-sm text-gray-600">
                Found <?php echo count($attendance_records); ?> record<?php echo count($attendance_records) != 1 ? 's' : ''; ?>
                <?php if ($filter_course || $filter_month || $filter_status): ?>
                    with current filters
                <?php endif; ?>
            </p>
        </div>
        
        <?php if (!empty($attendance_records)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Instructor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach($attendance_records as $record): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo date('M d, Y', strtotime($record['date'])); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo date('l', strtotime($record['date'])); ?>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($record['course_name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($record['course_code']); ?></div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($record['instructor']); ?>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full status-<?php echo $record['status']; ?>">
                                        <?php echo ucfirst($record['status']); ?>
                                    </span>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $record['check_in_time'] ? date('h:i A', strtotime($record['check_in_time'])) : '--'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Summary Footer -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center justify-between">
                    <div class="text-sm text-gray-700 mb-2 md:mb-0">
                        Showing <span class="font-medium">1</span> to <span class="font-medium"><?php echo count($attendance_records); ?></span> of 
                        <span class="font-medium"><?php echo $summary['total']; ?></span> records
                    </div>
                    
                    <div class="text-sm text-gray-700">
                        <?php 
                        $attended = $summary['present'] + $summary['late'] + $summary['excused'];
                        $percentage = $summary['total'] > 0 ? round(($attended / $summary['total']) * 100, 1) : 0;
                        ?>
                        Attendance Rate: 
                        <span class="font-bold <?php echo $percentage >= 75 ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $percentage; ?>%
                        </span>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-12">
                <i class="fas fa-clipboard-check text-gray-300 text-5xl mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-500 mb-2">No Attendance Records Found</h3>
                <p class="text-gray-400 max-w-md mx-auto">
                    <?php if ($filter_course || $filter_month || $filter_status): ?>
                        Try adjusting your filters or there are no records for the selected criteria.
                    <?php else: ?>
                        No attendance records found yet. Attendance will appear here once recorded.
                    <?php endif; ?>
                </p>
                
                <?php if ($filter_course || $filter_month || $filter_status): ?>
                    <a href="view_attendance.php" 
                       class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md transition duration-300">
                        Clear All Filters
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Help Message -->
    <?php if (empty($attendance_records) && empty($student_courses)): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-yellow-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">No Courses Enrolled</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>You are not enrolled in any courses yet. Contact your administrator or instructor to get enrolled in courses.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php 
require_once dirname(__DIR__) . '/includes/footer.php';
?>