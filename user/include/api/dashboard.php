<?php
// Turn off error reporting to prevent HTML errors in JSON output
error_reporting(0);
ini_set('display_errors', 0);
// Start output buffering to catch any unwanted output
ob_start();
session_start();
// Set content type to JSON
header('Content-Type: application/json');
// Check if user is logged in
if (!isset($_SESSION['emp_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$emp_id = $_SESSION['emp_id'] ?? '';
// Database connection with error handling
try {
    require_once '../../../config.php';   
    // Check if database connection is successful
    if (!isset($pdo) || !$pdo) {
        throw new Exception('Database connection failed');
    }
} catch (Exception $e) {
    // Clear any output buffer
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection error: ' . $e->getMessage()]);
    exit;
}
// Get current timestamp
$current_time = time();
// Initialize default values
$user_name = 'User';
$user_designation = 'Employee';
$user_department = 'General';
$user_photo = '../assets/images/default-avatar.jpg';
// Initialize dashboard data
$today_attendance = null;
$attendance_count = 0;
$month_total = 0; // will compute working days (Mon-Fri) for the current month
$leaves_remaining = ['annual' => 0, 'sick' => 0];
$last_salary = ['amount' => '--', 'month' => '--', 'status' => '--'];
$leave_summary = ['taken' => 0, 'upcoming' => 0, 'pending' => 0];
$recent_leaves = [];
$payroll_snapshot = [];
$latest_announcements = [];
$attendance_breakdown = ['present' => 0, 'absent' => 0, 'late' => 0, 'halfDay' => 0];
try {
    // Get user information
    $stmt = $pdo->prepare("
        SELECT e.*, d.dept_name 
        FROM employees e 
        LEFT JOIN departments d ON e.department_id = d.dept_id 
        WHERE e.emp_id = ? AND e.status = 'active'
    ");
    $stmt->execute([$emp_id]);
    $row = $stmt->fetch();
    if ($row) {
        $user_name = trim(($row['first_name'] ?? '') . ' ' . ($row['middle_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: 'User';
        $user_designation = $row['designation'] ?: 'Employee';
        $user_department = $row['dept_name'] ?: 'General';
        // Fix profile image path
        if (!empty($row['profile_img'])) {
            $user_photo = $row['profile_img'];
            if (!preg_match('/^(http|https|\/)/', $user_photo)) {
                $user_photo = '../' . $user_photo;
            }
        } else {
            $user_photo = '../assets/images/default-avatar.jpg';
        }
    }
    $stmt->closeCursor();
    // Get today's attendance (Updated for overnight shifts with 10 AM workday logic)
    $current_time = new DateTime('now', new DateTimeZone('Asia/Karachi'));
    $current_hour = intval($current_time->format('H'));
    
    // Determine workday based on 10 AM cutoff
    if ($current_hour < 10) {
        // Before 10 AM - previous day's workday
        $workday = $current_time->modify('-1 day')->format('Y-m-d');
    } else {
        // 10 AM or after - current day's workday
        $workday = $current_time->format('Y-m-d');
    }
    
    // Query to get attendance for current workday
    // This handles both regular and overnight shifts
    $attendance_stmt = $pdo->prepare("
        SELECT status, check_in, check_out, DATE(check_in) as check_in_date
        FROM attendance 
        WHERE emp_id = ? 
        AND DATE(check_in) = ?
        ORDER BY check_in DESC
        LIMIT 1
    ");
    $attendance_stmt->execute([$emp_id, $workday]);
    $attendance_row = $attendance_stmt->fetch();
    
    if ($attendance_row) {
        $check_in_time = $attendance_row['check_in'] ? date('h:i A', strtotime($attendance_row['check_in'])) : '--:--';
        $check_out_time = $attendance_row['check_out'] ? date('h:i A', strtotime($attendance_row['check_out'])) : '--:--';
        
        $status = $attendance_row['status'];
        $is_overnight = false;
        
        // Check if this is an overnight shift (no checkout yet and current time is next calendar day)
        if (!$attendance_row['check_out']) {
            $check_in_calendar_date = date('Y-m-d', strtotime($attendance_row['check_in']));
            $today_calendar_date = date('Y-m-d');
            
            if ($check_in_calendar_date != $today_calendar_date) {
                $status = 'Overnight Shift';
                $is_overnight = true;
            }
        }
        
        $today_attendance = [
            'status' => $status,
            'check_in' => $check_in_time,
            'check_out' => $check_out_time,
            'is_overnight' => $is_overnight
        ];
    }
    $attendance_stmt->closeCursor();
    // Working days calculation for current month or date range (Mon-Fri)
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
    if ($start_date && $end_date) {
        // Calculate working days for date range
        $firstDay = new DateTime($start_date);
        $lastDay = new DateTime($end_date);
        $workingDays = 0;
        for ($d = clone $firstDay; $d <= $lastDay; $d->modify('+1 day')) {
            $w = (int)$d->format('N'); // 1=Mon ... 7=Sun
            if ($w <= 5) { // Mon-Fri
                $workingDays++;
            }
        }
        $month_total = $workingDays;
    } else {
        // Use current month (default)
        $current_month = date('Y-m');
        $firstDay = new DateTime($current_month . '-01');
        $lastDay = (clone $firstDay)->modify('last day of this month');
        $workingDays = 0;
        for ($d = clone $firstDay; $d <= $lastDay; $d->modify('+1 day')) {
            $w = (int)$d->format('N'); // 1=Mon ... 7=Sun
            if ($w <= 5) { // Mon-Fri
                $workingDays++;
            }
        }
        $month_total = $workingDays;
    }
    // Get attendance count for current month or date range
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
    if ($start_date && $end_date) {
        // Use date range for attendance count
        $month_attendance_stmt = $pdo->prepare("SELECT COUNT(*) as attendance_count 
                                                FROM attendance 
                                                WHERE emp_id = ? AND DATE(check_in) BETWEEN ? AND ? 
                                                AND check_in IS NOT NULL 
                                                AND status IN ('Present', 'Late', 'Half-day')"); 
        $month_attendance_stmt->execute([$emp_id, $start_date, $end_date]); 
    } else {
        // Use current month (default)
        $month_attendance_stmt = $pdo->prepare("SELECT COUNT(*) as attendance_count 
                                                FROM attendance 
                                                WHERE emp_id = ? AND DATE_FORMAT(check_in, '%Y-%m') = ? 
                                                AND check_in IS NOT NULL 
                                                AND status IN ('Present', 'Late', 'Half-day')"); 
        $month_attendance_stmt->execute([$emp_id, $current_month]); 
    }
    $month_row = $month_attendance_stmt->fetch(); 
    if ($month_row) { 
        $attendance_count = $month_row['attendance_count']; 
    } 
    $month_attendance_stmt->closeCursor(); 
    // Get attendance breakdown for chart (separate absent handling)
    $breakdown_stmt = $pdo->prepare("SELECT status, COUNT(*) as count 
                                     FROM attendance 
                                     WHERE emp_id = ? AND DATE_FORMAT(check_in, '%Y-%m') = ? 
                                     AND check_in IS NOT NULL 
                                     AND status IN ('Present', 'Late', 'Half-day', 'Absent')
                                     GROUP BY status");
    $breakdown_stmt->execute([$emp_id, $current_month]);
    $breakdown_rows = $breakdown_stmt->fetchAll();
    // Reset breakdown array
    $attendance_breakdown = ['present' => 0, 'absent' => 0, 'late' => 0, 'halfDay' => 0];
    foreach ($breakdown_rows as $breakdown_row) {
        $status = strtolower($breakdown_row['status']);
        if (isset($attendance_breakdown[$status])) {
            $attendance_breakdown[$status] = $breakdown_row['count'];
        }
    }
    $breakdown_stmt->closeCursor();
    // Get approved leaves count by leave types (Total Leave) - with date range support
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
    if ($start_date && $end_date) {
        // Use date range for leave count with days calculation
        $approved_leaves_stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_approved,
                lt.type_name,
                COUNT(lt.type_name) as type_count,
                SUM(DATEDIFF(lr.end_date, lr.start_date) + 1) as total_days
            FROM leave_requests lr 
            LEFT JOIN leave_types lt ON lr.leave_type_id = lt.leave_type_id 
            WHERE lr.emp_id = ? AND lr.status = 'approved' 
            AND DATE(lr.start_date) BETWEEN ? AND ?
            GROUP BY lt.type_name
            ORDER BY total_days DESC
        ");
        $approved_leaves_stmt->execute([$emp_id, $start_date, $end_date]);
    } else {
        // Use all time (default) with days calculation
        $approved_leaves_stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_approved,
                lt.type_name,
                COUNT(lt.type_name) as type_count,
                SUM(DATEDIFF(lr.end_date, lr.start_date) + 1) as total_days
            FROM leave_requests lr 
            LEFT JOIN leave_types lt ON lr.leave_type_id = lt.leave_type_id 
            WHERE lr.emp_id = ? AND lr.status = 'approved'
            GROUP BY lt.type_name
            ORDER BY total_days DESC
        ");
        $approved_leaves_stmt->execute([$emp_id]);
    }
    $approved_leaves_rows = $approved_leaves_stmt->fetchAll();
    $total_approved = 0;
    $total_days = 0;
    $leave_types_breakdown = [];
    foreach ($approved_leaves_rows as $leave_row) {
        $total_approved += $leave_row['type_count'];
        $total_days += $leave_row['total_days'];
        $leave_types_breakdown[] = [
            'type_name' => $leave_row['type_name'],
            'count' => $leave_row['type_count'],
            'days' => $leave_row['total_days']
        ];
    }
    $leaves_remaining = [
        'total' => $total_approved,
        'total_days' => $total_days,
        'breakdown' => $leave_types_breakdown
    ];
    $approved_leaves_stmt->closeCursor();
    // Get last salary
    $salary_stmt = $pdo->prepare("
        SELECT net_salary, payment_date, status 
        FROM payroll 
        WHERE emp_id = ? 
        ORDER BY payment_date DESC 
        LIMIT 1
    ");
    $salary_stmt->execute([$emp_id]);
    $salary_row = $salary_stmt->fetch();
    if ($salary_row) {
        $last_salary = [
            'amount' => number_format($salary_row['net_salary']),
            'month' => date('F Y', strtotime($salary_row['payment_date'])),
            'status' => $salary_row['status'] ?: 'Paid'
        ];
    }
    $salary_stmt->closeCursor();
    // Get leave summary
    $leave_summary_stmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
            COUNT(CASE WHEN status = 'approved' AND start_date > CURDATE() THEN 1 END) as upcoming_count,
            COUNT(CASE WHEN status = 'approved' AND start_date <= CURDATE() AND end_date >= CURDATE() THEN 1 END) as taken_count
        FROM leave_requests 
        WHERE emp_id = ?
    ");
    $leave_summary_stmt->execute([$emp_id]);
    $leave_summary_row = $leave_summary_stmt->fetch();
    if ($leave_summary_row) {
        $leave_summary = [
            'pending' => $leave_summary_row['pending_count'],
            'upcoming' => $leave_summary_row['upcoming_count'],
            'taken' => $leave_summary_row['taken_count']
        ];
    }
    $leave_summary_stmt->closeCursor();
    // Get recent leaves
    $recent_leaves_stmt = $pdo->prepare("
        SELECT lr.*, lt.type_name 
        FROM leave_requests lr 
        LEFT JOIN leave_types lt ON lr.leave_type_id = lt.leave_type_id 
        WHERE lr.emp_id = ? 
        ORDER BY lr.created_at DESC 
        LIMIT 5
    ");
    $recent_leaves_stmt->execute([$emp_id]);
    $recent_leaves_rows = $recent_leaves_stmt->fetchAll();
    foreach ($recent_leaves_rows as $recent_leave_row) {
        $recent_leaves[] = [
            'id' => $recent_leave_row['leave_id'],
            'type' => $recent_leave_row['type_name'],
            'start_date' => $recent_leave_row['start_date'],
            'end_date' => $recent_leave_row['end_date'],
            'status' => $recent_leave_row['status'],
            'reason' => $recent_leave_row['reason']
        ];
    }
    $recent_leaves_stmt->closeCursor();
    // Get payroll snapshot
    $payroll_stmt = $pdo->prepare("
        SELECT net_salary, payment_date, status 
        FROM payroll 
        WHERE emp_id = ? 
        ORDER BY payment_date DESC 
        LIMIT 6
    ");
    $payroll_stmt->execute([$emp_id]);
    $payroll_rows = $payroll_stmt->fetchAll();
    foreach ($payroll_rows as $payroll_row) {
        $payroll_snapshot[] = [
            'month' => date('F Y', strtotime($payroll_row['payment_date'])),
            'amount' => number_format($payroll_row['net_salary']),
            'status' => $payroll_row['status'] ?: 'Paid'
        ];
    }
    $payroll_stmt->closeCursor();
    // Get announcements
    $announcements_stmt = $pdo->prepare("SELECT id, title, created_at, expires_at, is_urgent 
                                         FROM announcements 
                                         WHERE expires_at > NOW() 
                                         ORDER BY created_at DESC 
                                         LIMIT 5");
    $announcements_stmt->execute();
    $announcements_rows = $announcements_stmt->fetchAll();
    foreach ($announcements_rows as $announcement_row) {
        $latest_announcements[] = [
            'id' => $announcement_row['id'],
            'title' => $announcement_row['title'],
            'created_at' => $announcement_row['created_at'],
            'expires_at' => $announcement_row['expires_at'],
            'is_urgent' => (bool)$announcement_row['is_urgent']
        ];
    }
    $announcements_stmt->closeCursor();   
} catch (Exception $e) {
    error_log("Dashboard API Error: " . $e->getMessage());
}
// Get motivational quote for today
$quotes = [
    "Success is not final, failure is not fatal: it is the courage to continue that counts.",
    "The only way to do great work is to love what you do.",
    "Don't watch the clock; do what it does. Keep going.",
    "The future depends on what you do today.",
    "Believe you can and you're halfway there.",
    "It always seems impossible until it's done.",
    "The only limit to our realization of tomorrow will be our doubts of today.",
    "Success usually comes to those who are too busy to be looking for it."
];
$today_quote = $quotes[array_rand($quotes)];
$response = [
    'user_info' => [
        'name' => $user_name,
        'emp_id' => $emp_id,
        'designation' => $user_designation,
        'department' => $user_department,
        'photo' => $user_photo
    ],
    'motivational_quote' => $today_quote,
    'today_attendance' => $today_attendance,
    'attendance_count' => $attendance_count,
    'month_total' => $month_total,
    'leaves_remaining' => $leaves_remaining,
    'last_salary' => $last_salary,
    'leave_summary' => $leave_summary,
    'recent_leaves' => $recent_leaves,
    'payroll_snapshot' => $payroll_snapshot,
    'latest_announcements' => $latest_announcements,
    'attendance_breakdown' => $attendance_breakdown,  
    // Debug data
    'debug' => [
        'user_name' => $user_name,
        'user_designation' => $user_designation,
        'user_department' => $user_department,
        'emp_id' => $emp_id
    ]
];
// Clear any unwanted output and send clean JSON response
ob_clean();
echo json_encode(['success' => true, 'data' => $response]);
?>