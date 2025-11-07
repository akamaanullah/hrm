<?php
require_once '../../../config.php';
// Don't include attendance_handler.php for GET requests
header('Content-Type: application/json');

date_default_timezone_set('Asia/Karachi');

try {
    // Check database connection
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    // Debug: Check if tables exist
    $tables_check = $pdo->query("SHOW TABLES LIKE 'employees'");
    if (!$tables_check || $tables_check->rowCount() == 0) {
        throw new Exception('Employees table not found');
    }
    
    $attendance_check = $pdo->query("SHOW TABLES LIKE 'attendance'");
    if (!$attendance_check || $attendance_check->rowCount() == 0) {
        throw new Exception('Attendance table not found');
    }
    
    // Check if we have any data (exclude admin role and deleted employees)
    $emp_count = $pdo->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active' AND role != 'admin' AND (is_deleted = 0 OR is_deleted IS NULL)");
    $emp_data = $emp_count->fetch();
    $total_employees = $emp_data['count'];
    
    if ($total_employees == 0) {
        throw new Exception('No active employees found in database');
    }

    // Last 7 days
    $days = [];
    for ($i = 6; $i >= 0; $i--) {
        $days[] = date('Y-m-d', strtotime("-$i days"));
    }
    $dayLabels = array_map(function($d) { return date('D', strtotime($d)); }, $days);

    $statuses = ['present', 'absent', 'late', 'half-day'];
    $colors = ['present' => '#22c55e', 'absent' => '#ef4444', 'late' => '#facc15', 'half-day' => '#3b82f6'];

    $series = [];
    foreach ($statuses as $status) {
        $data = [];
        foreach ($days as $day) {
            if ($status == 'absent') {
                // Absent: employees with status = 'absent' in attendance table + employees not in attendance table
                $sql_absent_records = "SELECT COUNT(DISTINCT emp_id) as count FROM attendance 
                                      WHERE DATE(check_in) = ? AND status = 'absent'";
                $stmt1 = $pdo->prepare($sql_absent_records);
                $stmt1->execute([$day]);
                $absent_records = $stmt1->fetch();
                
                $sql_no_attendance = "SELECT COUNT(*) as count FROM employees e 
                                     WHERE e.status = 'active' AND e.role != 'admin' AND (e.is_deleted = 0 OR e.is_deleted IS NULL)
                                     AND NOT EXISTS (
                                         SELECT 1 FROM attendance a 
                                         WHERE a.emp_id = e.emp_id 
                                         AND DATE(a.check_in) = ?
                                     )";
                $stmt2 = $pdo->prepare($sql_no_attendance);
                $stmt2->execute([$day]);
                $no_attendance = $stmt2->fetch();
                
                $total_absent = (int)$absent_records['count'] + (int)$no_attendance['count'];
                $data[] = $total_absent;
            } else {
                $sql = "SELECT COUNT(DISTINCT emp_id) as count FROM attendance 
                        WHERE DATE(check_in) = ? AND status = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$day, $status]);
                $row = $stmt->fetch();
                $data[] = (int)$row['count'];
            }
        }
        $series[] = [
            'name' => ucfirst($status),
            'data' => $data,
            'color' => $colors[$status]
        ];
    }
    
    // Debug: Log the data for troubleshooting
    error_log("Attendance API Debug - Days: " . json_encode($dayLabels));
    error_log("Attendance API Debug - Series: " . json_encode($series));
    
    // Debug: Log total employees count
    error_log("Attendance API Debug - Total Active Employees: " . $total_employees);
    
    // Debug: Log today's data specifically
    $today = date('Y-m-d');
    $today_absent_sql = "SELECT COUNT(*) as count FROM employees e 
                        WHERE e.status = 'active' AND e.role != 'admin' AND (e.is_deleted = 0 OR e.is_deleted IS NULL)
                        AND NOT EXISTS (
                            SELECT 1 FROM attendance a 
                            WHERE a.emp_id = e.emp_id 
                            AND DATE(a.check_in) = ?
                        )";
    $today_stmt = $pdo->prepare($today_absent_sql);
    $today_stmt->execute([$today]);
    $today_absent = $today_stmt->fetch();
    error_log("Attendance API Debug - Today's Absent (No Record): " . $today_absent['count']);
    
    $today_absent_table_sql = "SELECT COUNT(DISTINCT emp_id) as count FROM attendance 
                              WHERE DATE(check_in) = ? AND status = 'absent'";
    $today_table_stmt = $pdo->prepare($today_absent_table_sql);
    $today_table_stmt->execute([$today]);
    $today_absent_table = $today_table_stmt->fetch();
    error_log("Attendance API Debug - Today's Absent (From Table): " . $today_absent_table['count']);

    echo json_encode([
        'success' => true,
        'days' => $dayLabels,
        'series' => $series
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'days' => [],
        'series' => []
    ]);
} 