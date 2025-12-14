<?php
$page_title = "My Attendance";
include '../includes/header.php';
include '../includes/auth_check.php';

requireRole('student');

$student_id = $_SESSION['user_id'];

// Filter parameters
$filter_course = isset($_GET['course']) ? $_GET['course'] : '';
$filter_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

try {
    // Get student's courses for filter dropdown
    $query = "SELECT c.id, c.course_code, c.course_name FROM courses c 
              JOIN enrollments e ON c.id = e.course_id 
              WHERE e.student_id = :student_id 
              ORDER BY c.course_name";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $student_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Build attendance query with filters
    $query = "SELECT a.*, c.course_code, c.course_name, c.instructor 
              FROM attendance a 
              JOIN enrollments e ON a.enrollment_id = e.id 
              JOIN courses c ON e.course_id = c.id 
              WHERE e.student_id = :student_id";

    $params = [':student_id' => $student_id];

    if ($filter_course) {
        $query .= " AND c.id = :course_id";
        $params[':course_id'] = $filter_course;
    }

    if ($filter_month) {
        $query .= " AND DATE_FORMAT(a.date, '%Y-%m') = :month";
        $params[':month'] = $filter_month;
    }

    if ($filter_status) {
        $query .= " AND a.status = :status";
        $params[':status'] = $filter_status;
    }

    $query .= " ORDER BY a.date DESC";

    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get attendance summary safely
    $summary_query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present,
                        SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent,
                        SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late,
                        SUM(CASE WHEN a.status = 'excused' THEN 1 ELSE 0 END) as excused
                      FROM attendance a 
                      JOIN enrollments e ON a.enrollment_id = e.id 
                      JOIN courses c ON e.course_id = c.id 
                      WHERE e.student_id = :student_id";

    if ($filter_course) $summary_query .= " AND c.id = :course_id";
    if ($filter_month) $summary_query .= " AND DATE_FORMAT(a.date, '%Y-%m') = :month";
    if ($filter_status) $summary_query .= " AND a.status = :status";

    $stmt = $db->prepare($summary_query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ensure summary array exists
    if (!$summary) {
        $summary = [
            'total' => 0,
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'excused' => 0
        ];
    }

} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">My Attendance Records</h1>
        <p class="text-gray-600">View and filter your attendance history across all courses.</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <form method="GET" action="view_attendance.php" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Course Filter -->
                <div>
                    <label for="course" class="block text-sm font-medium text-gray-700 mb-1">Course</label>
                    <select id="course" name="course" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Courses</option>
                        <?php foreach($student_courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>" <?php echo ($filter_course == $course['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Month Filter -->
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                    <input type="month" id="month" name="month" value="<?php echo $filter_month; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="present" <?php echo ($filter_status == 'present') ? 'selected' : ''; ?>>Present</option>
                        <option value="absent" <?php echo ($filter_status == 'absent') ? 'selected' : ''; ?>>Absent</option>
                        <option value="late" <?php echo ($filter_status == 'late') ? 'selected' : ''; ?>>Late</option>
                        <option value="excused" <?php echo ($filter_status == 'excused') ? 'selected' : ''; ?>>Excused</option>
                    </select>
                </div>

                <!-- Filter & Reset Buttons -->
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

    <!-- Attendance Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mt-6">
        <?php if (!empty($attendance_records)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th>Date</th>
                            <th>Course</th>
                            <th>Instructor</th>
                            <th>Status</th>
                            <th>Check-in Time</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach($attendance_records as $record): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                                <td><?php echo htmlspecialchars($record['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($record['instructor']); ?></td>
                                <td>
                                    <?php
                                    $status_colors = [
                                        'present' => 'bg-green-100 text-green-800',
                                        'absent' => 'bg-red-100 text-red-800',
                                        'late' => 'bg-yellow-100 text-yellow-800',
                                        'excused' => 'bg-blue-100 text-blue-800'
                                    ];
                                    $color = $status_colors[$record['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-2 py-1 rounded <?php echo $color; ?>"><?php echo ucfirst($record['status']); ?></span>
                                </td>
                                <td><?php echo $record['check_in_time'] ? date('h:i A', strtotime($record['check_in_time'])) : '--'; ?></td>
                                <td><?php echo $record['notes'] ? htmlspecialchars(substr($record['notes'],0,50)) . '...' : '--'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <p class="text-gray-500">No attendance records found for the selected filters.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
