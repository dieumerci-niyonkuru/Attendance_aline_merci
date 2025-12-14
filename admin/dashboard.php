<?php
$page_title = "Admin Dashboard";
include '../includes/header.php';
include '../includes/auth_check.php';

requireRole('admin');

// Get statistics
try {
    // Total students
    $query = "SELECT COUNT(*) as total FROM users WHERE role = 'student'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total courses
    $query = "SELECT COUNT(*) as total FROM courses";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $total_courses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Today's attendance
    $today = date('Y-m-d');
    $query = "SELECT COUNT(*) as total FROM attendance WHERE date = :today";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':today', $today, PDO::PARAM_STR);
    $stmt->execute();
    $today_attendance = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Recent attendance
    $query = "SELECT a.*, u.full_name, u.student_id, c.course_name 
              FROM attendance a 
              JOIN users u ON a.student_id = u.id 
              JOIN courses c ON a.course_id = c.id 
              ORDER BY a.created_at DESC 
              LIMIT 10";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $recent_attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Attendance by status
    $query = "SELECT status, COUNT(*) as count FROM attendance GROUP BY status";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $attendance_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
                <p class="opacity-90 mt-2">Here's what's happening with your attendance system today.</p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold"><?php echo date('l, F j, Y'); ?></p>
                <p class="opacity-90"><?php echo date('h:i A'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg mr-4">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Total Students</p>
                    <h3 class="text-3xl font-bold"><?php echo $total_students; ?></h3>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg mr-4">
                    <i class="fas fa-book text-green-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Total Courses</p>
                    <h3 class="text-3xl font-bold"><?php echo $total_courses; ?></h3>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-lg mr-4">
                    <i class="fas fa-calendar-check text-yellow-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Today's Attendance</p>
                    <h3 class="text-3xl font-bold"><?php echo $today_attendance; ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-bold mb-4 text-gray-800">Quick Actions</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="take_attendance.php" 
               class="bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg p-4 text-center transition duration-300">
                <i class="fas fa-clipboard-check text-blue-600 text-2xl mb-2"></i>
                <p class="font-semibold text-blue-700">Take Attendance</p>
            </a>
            
            <a href="manage_students.php" 
               class="bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg p-4 text-center transition duration-300">
                <i class="fas fa-user-plus text-green-600 text-2xl mb-2"></i>
                <p class="font-semibold text-green-700">Add Student</p>
            </a>
            
            <a href="manage_courses.php" 
               class="bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg p-4 text-center transition duration-300">
                <i class="fas fa-book-medical text-purple-600 text-2xl mb-2"></i>
                <p class="font-semibold text-purple-700">Add Course</p>
            </a>
            
            <a href="view_reports.php" 
               class="bg-yellow-50 hover:bg-yellow-100 border border-yellow-200 rounded-lg p-4 text-center transition duration-300">
                <i class="fas fa-chart-bar text-yellow-600 text-2xl mb-2"></i>
                <p class="font-semibold text-yellow-700">View Reports</p>
            </a>
        </div>
    </div>
    
    <!-- Recent Attendance -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800">Recent Attendance Records</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach($recent_attendance as $record): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($record['full_name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($record['student_id']); ?></div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($record['course_name']); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900"><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <?php
                                    $status_colors = [
                                        'present' => 'bg-green-100 text-green-800',
                                        'absent' => 'bg-red-100 text-red-800',
                                        'late' => 'bg-yellow-100 text-yellow-800',
                                        'excused' => 'bg-blue-100 text-blue-800'
                                    ];
                                    $color = isset($status_colors[$record['status']]) ? $status_colors[$record['status']] : 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $color; ?>">
                                        <?php echo ucfirst($record['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Attendance Statistics -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800">Attendance Statistics</h2>
            <div class="space-y-4">
                <?php foreach($attendance_stats as $stat): ?>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">
                                <?php echo ucfirst($stat['status']); ?>
                            </span>
                            <span class="text-sm font-medium text-gray-700"><?php echo $stat['count']; ?></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <?php
                            $total = array_sum(array_column($attendance_stats, 'count'));
                            $percentage = $total > 0 ? ($stat['count'] / $total) * 100 : 0;
                            $bar_colors = [
                                'present' => 'bg-green-600',
                                'absent' => 'bg-red-600',
                                'late' => 'bg-yellow-500',
                                'excused' => 'bg-blue-600'
                            ];
                            $bar_color = isset($bar_colors[$stat['status']]) ? $bar_colors[$stat['status']] : 'bg-gray-600';
                            ?>
                            <div class="<?php echo $bar_color; ?> h-2.5 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Upcoming Classes -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold mb-3 text-gray-800">Upcoming Classes Today</h3>
                <?php
                try {
                    $query = "SELECT * FROM courses WHERE schedule_day LIKE :today";
                    $today_name = date('l');
                    $search_term = "%{$today_name}%";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':today', $search_term, PDO::PARAM_STR);
                    $stmt->execute();
                    $today_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($today_classes) > 0) {
                        foreach($today_classes as $class) {
                            echo '<div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg mb-2">';
                            echo '<div>';
                            echo '<h4 class="font-semibold text-gray-900">' . htmlspecialchars($class['course_name']) . '</h4>';
                            echo '<p class="text-sm text-gray-600">' . htmlspecialchars($class['schedule_time']) . '</p>';
                            echo '</div>';
                            echo '<a href="take_attendance.php?course_id=' . $class['id'] . '" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Take Attendance</a>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p class="text-gray-500 text-center py-4">No classes scheduled for today.</p>';
                    }
                } catch(PDOException $e) {
                    echo '<p class="text-red-500">Error loading classes</p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>