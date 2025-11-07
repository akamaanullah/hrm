<?php
require_once '../../../config.php';
header('Content-Type: application/json');
ob_clean();

// SABSE PEHLE POST REQUESTS HANDLE KARO - Redirect to central handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid JSON input: ' . json_last_error_msg()
        ]);
        exit;
    }

    // Forward to central handler using relative path
    $handlerPath = dirname(__FILE__) . '/attendance_handler.php';
    
    // Check if handler file exists
    if (!file_exists($handlerPath)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Attendance handler file not found'
        ]);
        exit;
    }
    
    // Include the handler directly instead of using cURL
    $_POST = $data; // Set POST data for the handler
    ob_start();
    include $handlerPath;
    $response = ob_get_clean();
    
    echo $response;
    exit;
}

// AB GET REQUESTS HANDLE KARO
// Handle get employee info request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_employee_info') {
    $emp_id = intval($_GET['emp_id']);
    $sql = "SELECT emp_id, first_name, joining_date FROM employees WHERE emp_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$emp_id]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($employee) {
        echo json_encode(['success' => true, 'employee' => $employee]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Employee not found']);
    }
    exit;
}

// Handle specific attendance record request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['attendance_id'])) {
    $attendance_id = intval($_GET['attendance_id']);
    $sql = "SELECT a.*, e.first_name, e.middle_name, e.last_name AS emp_name, d.dept_name AS department 
            FROM attendance a 
            LEFT JOIN employees e ON a.emp_id = e.emp_id 
            LEFT JOIN departments d ON e.department_id = d.dept_id 
            WHERE a.attendance_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$attendance_id]);
    $row = $stmt->fetch();
    echo json_encode(['success' => true, 'data' => $row]);
    exit;
}

// Handle monthly attendance request for specific employee
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['emp_id']) && isset($_GET['year']) && isset($_GET['month'])) {
    $emp_id = intval($_GET['emp_id']);
    $year = intval($_GET['year']);
    $month = intval($_GET['month']);
    
    $sql = "SELECT a.*, e.first_name, e.middle_name, e.last_name, d.dept_name AS department, s.shift_name as shift_name, s.start_time as shift_start_time, s.end_time as shift_end_time, e.shift_id as emp_shift_id, e.joining_date FROM attendance a LEFT JOIN employees e ON a.emp_id = e.emp_id LEFT JOIN departments d ON e.department_id = d.dept_id LEFT JOIN shifts s ON a.shift_id = s.id WHERE a.emp_id = ? AND YEAR(a.check_in) = ? AND MONTH(a.check_in) = ? AND e.role != 'admin' AND (e.status = 'active' OR e.status IS NULL) AND (e.is_deleted = 0 OR e.is_deleted IS NULL) AND DATE(a.check_in) >= e.joining_date ORDER BY a.check_in DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$emp_id, $year, $month]);
    
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . implode(', ', $pdo->errorInfo())
        ]);
        exit;
    }
    
    $data = [];
    $datesWithAttendance = [];
    
    while ($row = $stmt->fetch()) {
        $row['date'] = date('Y-m-d', strtotime($row['check_in']));
        $row['department'] = $row['department'] ?? 'Not Assigned';
        // Agar shift_name NULL hai lekin emp_shift_id set hai, toh employee ki shift info nikaalo
        if (empty($row['shift_name']) && !empty($row['emp_shift_id'])) {
            $emp_shift_id = intval($row['emp_shift_id']);
            $shiftQ = $pdo->prepare("SELECT shift_name, start_time, end_time FROM shifts WHERE id = ? LIMIT 1");
            $shiftQ->execute([$emp_shift_id]);
            if ($shiftQ->rowCount() > 0) {
                $shiftRow = $shiftQ->fetch();
                $row['shift_name'] = $shiftRow['shift_name'];
                $row['shift_start_time'] = $shiftRow['start_time'];
                $row['shift_end_time'] = $shiftRow['end_time'];
            }
        }
        $data[] = $row;
        $datesWithAttendance[$row['date']] = true;
    }
    
    // Fill absent for missing workdays (weekdays only)
    $daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
    $today = date('Y-m-d');
    
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $loop_date_str = sprintf('%04d-%02d-%02d', $year, $month, $d);
        
        // Check if it's a weekday (Monday = 1, Sunday = 0)
        $dayOfWeek = date('w', strtotime($loop_date_str));
        $isWeekday = ($dayOfWeek >= 1 && $dayOfWeek <= 5); // Monday to Friday
        
        // Only mark past workdays as absent AND only for weekdays
        if (!isset($datesWithAttendance[$loop_date_str]) && 
            strtotime($loop_date_str) < strtotime($today) && 
            $isWeekday) {
            $data[] = [
                'attendance_id' => null,
                'emp_id' => $emp_id,
                'check_in' => $loop_date_str . ' 00:00:00',
                'check_out' => null,
                'working_hrs' => '0h 0m',
                'status' => 'absent',
                'emp_name' => null, // Will be filled from employee table
                'department' => 'Not Assigned',
                'shift_name' => null,
                'shift_start_time' => null,
                'shift_end_time' => null,
                'date' => $loop_date_str,
            ];
        }
    }
    
    // Sort by date descending (latest first)
    usort($data, function($a, $b) {
        return strcmp($b['date'], $a['date']);
    });
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    exit;
}

// Handle daily attendance request for specific employee and date
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['emp_id']) && isset($_GET['date'])) {
    $emp_id = intval($_GET['emp_id']);
    $date = $_GET['date'];
    
    $sql = "SELECT a.*, e.first_name, e.middle_name, e.last_name, d.dept_name AS department, s.shift_name as shift_name, s.start_time as shift_start_time, s.end_time as shift_end_time, e.shift_id as emp_shift_id FROM attendance a LEFT JOIN employees e ON a.emp_id = e.emp_id LEFT JOIN departments d ON e.department_id = d.dept_id LEFT JOIN shifts s ON a.shift_id = s.id WHERE a.emp_id = ? AND DATE(a.check_in) = ? AND e.role != 'admin' AND (e.status = 'active' OR e.status IS NULL) AND (e.is_deleted = 0 OR e.is_deleted IS NULL) ORDER BY a.check_in ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$emp_id, $date]);
    
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . implode(', ', $pdo->errorInfo())
        ]);
        exit;
    }
    
    $row = $stmt->fetch();
    if ($row) {
        $row['date'] = date('Y-m-d', strtotime($row['check_in']));
        $row['department'] = $row['department'] ?? 'Not Assigned';
        // Agar shift_name NULL hai lekin emp_shift_id set hai, toh employee ki shift info nikaalo
        if (empty($row['shift_name']) && !empty($row['emp_shift_id'])) {
            $emp_shift_id = intval($row['emp_shift_id']);
            $shiftQ = $pdo->prepare("SELECT shift_name, start_time, end_time FROM shifts WHERE id = ? LIMIT 1");
            $shiftQ->execute([$emp_shift_id]);
            if ($shiftQ->rowCount() > 0) {
                $shiftRow = $shiftQ->fetch();
                $row['shift_name'] = $shiftRow['shift_name'];
                $row['shift_start_time'] = $shiftRow['start_time'];
                $row['shift_end_time'] = $shiftRow['end_time'];
            }
        }
        
        // Get break details for this attendance record
        $breakSql = "SELECT br.break_id, br.break_start, br.break_end, br.break_duration, br.status 
                     FROM break_records br 
                     WHERE br.attendance_id = ? 
                     ORDER BY br.break_start ASC";
        $breakStmt = $pdo->prepare($breakSql);
        $breakStmt->execute([$row['attendance_id']]);
        $breaks = $breakStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add break details to response
        $row['breaks'] = $breaks;
        $row['total_breaks'] = count($breaks);
        $row['total_break_time'] = calculateTotalBreakTime($breaks);
        
        echo json_encode([
            'success' => true,
            'data' => $row
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No attendance record found for this date'
        ]);
    }
    exit;
}

// Function to calculate total break time
function calculateTotalBreakTime($breaks) {
    $totalSeconds = 0;
    
    foreach ($breaks as $break) {
        // If break_duration exists, parse it
        if ($break['break_duration'] && $break['break_duration'] !== 'In Progress') {
            $duration = $break['break_duration'];
            
            // Extract hours
            if (preg_match('/(\d+)\s*hour/', $duration, $matches)) {
                $totalSeconds += intval($matches[1]) * 3600; // Convert to seconds
            }
            
            // Extract minutes
            if (preg_match('/(\d+)\s*minute/', $duration, $matches)) {
                $totalSeconds += intval($matches[1]) * 60; // Convert to seconds
            }
            
            // Extract seconds
            if (preg_match('/(\d+)\s*second/', $duration, $matches)) {
                $totalSeconds += intval($matches[1]);
            }
        }
        // If break_duration is not available but we have start and end times, calculate manually
        elseif ($break['break_start'] && $break['break_end']) {
            $start = new DateTime($break['break_start']);
            $end = new DateTime($break['break_end']);
            $diff = $start->diff($end);
            
            $totalSeconds += ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
        }
    }
    
    // Convert back to readable format
    $hours = floor($totalSeconds / 3600);
    $minutes = floor(($totalSeconds % 3600) / 60);
    $seconds = $totalSeconds % 60;
    
    if ($totalSeconds == 0) {
        return '0 minutes';
    } elseif ($hours > 0) {
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ' . $minutes . ' minute' . ($minutes != 1 ? 's' : '');
    } elseif ($minutes > 0) {
        return $minutes . ' minute' . ($minutes != 1 ? 's' : '') . ' ' . $seconds . ' second' . ($seconds != 1 ? 's' : '');
    } else {
        return $seconds . ' second' . ($seconds != 1 ? 's' : '');
    }
}

// Handle today's attendance for all employees
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['today']) && $_GET['today'] == 1) {
    $today = date('Y-m-d');
    $sql = "SELECT a.*, e.first_name, e.middle_name, e.last_name AS emp_name, d.dept_name AS department
            FROM attendance a
            LEFT JOIN employees e ON a.emp_id = e.emp_id
            LEFT JOIN departments d ON e.department_id = d.dept_id
            WHERE DATE(a.check_in) = ? AND e.role != 'admin' AND (e.status = 'active' OR e.status IS NULL) AND (e.is_deleted = 0 OR e.is_deleted IS NULL)
            ORDER BY a.emp_id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$today]);
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . implode(', ', $pdo->errorInfo())
        ]);
        exit;
    }
    
    $data = [];
    while ($row = $stmt->fetch()) {
        $data[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    exit;
}

// Handle date range attendance request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['emp_id']) && isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $emp_id = intval($_GET['emp_id']);
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];

    $sql = "SELECT a.*, e.first_name, e.middle_name, e.last_name, d.dept_name AS department, s.shift_name as shift_name, s.start_time as shift_start_time, s.end_time as shift_end_time, e.shift_id as emp_shift_id, e.joining_date FROM attendance a LEFT JOIN employees e ON a.emp_id = e.emp_id LEFT JOIN departments d ON e.department_id = d.dept_id LEFT JOIN shifts s ON a.shift_id = s.id WHERE a.emp_id = ? AND DATE(a.check_in) BETWEEN ? AND ? AND e.role != 'admin' AND (e.status = 'active' OR e.status IS NULL) AND (e.is_deleted = 0 OR e.is_deleted IS NULL) AND DATE(a.check_in) >= e.joining_date ORDER BY a.check_in DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$emp_id, $start_date, $end_date]);

    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . implode(', ', $pdo->errorInfo())
        ]);
        exit;
    }

    $data = [];
    $datesWithAttendance = [];
    
    while ($row = $stmt->fetch()) {
        $row['date'] = date('Y-m-d', strtotime($row['check_in']));
        $row['department'] = $row['department'] ?? 'Not Assigned';
        // Agar shift_name NULL hai lekin emp_shift_id set hai, toh employee ki shift info nikaalo
        if (empty($row['shift_name']) && !empty($row['emp_shift_id'])) {
            $emp_shift_id = intval($row['emp_shift_id']);
            $shiftQ = $pdo->prepare("SELECT shift_name, start_time, end_time FROM shifts WHERE id = ? LIMIT 1");
            $shiftQ->execute([$emp_shift_id]);
            if ($shiftQ->rowCount() > 0) {
                $shiftRow = $shiftQ->fetch();
                $row['shift_name'] = $shiftRow['shift_name'];
                $row['shift_start_time'] = $shiftRow['start_time'];
                $row['shift_end_time'] = $shiftRow['end_time'];
            }
        }
        $data[] = $row;
        $datesWithAttendance[$row['date']] = true;
    }

    // Fill absent for missing workdays in the date range (weekdays only)
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $today = date('Y-m-d');
    
    for ($date = $start; $date <= $end; $date->add(new DateInterval('P1D'))) {
        $loop_date_str = $date->format('Y-m-d');
        
        // Check if it's a weekday (Monday = 1, Sunday = 0)
        $dayOfWeek = date('w', strtotime($loop_date_str));
        $isWeekday = ($dayOfWeek >= 1 && $dayOfWeek <= 5); // Monday to Friday
        
        // Only mark past workdays as absent AND only for weekdays
        if (!isset($datesWithAttendance[$loop_date_str]) && 
            strtotime($loop_date_str) < strtotime($today) && 
            $isWeekday) {
            $data[] = [
                'attendance_id' => null,
                'emp_id' => $emp_id,
                'check_in' => $loop_date_str . ' 00:00:00',
                'check_out' => null,
                'working_hrs' => '0h 0m',
                'status' => 'Absent',
                'emp_name' => null, // Will be filled from employee table
                'department' => 'Not Assigned',
                'shift_name' => null,
                'shift_start_time' => null,
                'shift_end_time' => null,
                'date' => $loop_date_str,
            ];
        }
    }
    
    // Sort by date descending (latest first)
    usort($data, function($a, $b) {
        return strcmp($b['date'], $a['date']);
    });

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    exit;
}

// Handle date-specific attendance request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['date'])) {
    $selected_date = $_GET['date'];
    
    // Debug logging
    error_log("📅 Date filter request received: " . $selected_date);
    
    $sql = "SELECT e.emp_id, e.first_name, e.middle_name, e.last_name, d.dept_name as dept_name, 
            a.check_in, a.check_out, a.status, a.working_hrs, a.attendance_id,
            s.shift_name, s.start_time as shift_start_time, s.end_time as shift_end_time
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.dept_id
            LEFT JOIN attendance a ON e.emp_id = a.emp_id AND DATE(a.check_in) = ?
            LEFT JOIN shifts s ON e.shift_id = s.id
            WHERE e.role != 'admin' AND (e.status = 'active' OR e.status IS NULL) AND (e.is_deleted = 0 OR e.is_deleted IS NULL)
            ORDER BY e.emp_id ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$selected_date]);
    
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . implode(', ', $pdo->errorInfo())
        ]);
        exit;
    }
    
    $data = [];
    while ($row = $stmt->fetch()) {
        // Ensure department name is properly set
        $row['dept_name'] = $row['dept_name'] ?? 'Not Assigned';
        
        // If no attendance record, mark as absent
        if (empty($row['check_in']) || $row['check_in'] === null) {
            $row['check_in'] = null;
            $row['check_out'] = null;
            $row['status'] = 'Absent';
            $row['working_hrs'] = '0h 0m';
            $row['attendance_id'] = null;
        }
        
        $data[] = $row;
        
        // Debug logging for each record
        error_log("📋 Date filter record: " . $row['emp_id'] . " - " . $row['first_name'] . " - " . $row['check_in'] . " - " . $row['status']);
    }
    
    error_log("📊 Date filter returning " . count($data) . " records for date: " . $selected_date);
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    exit;
}

// Default GET request - Get all employees with their latest attendance
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT e.emp_id, e.first_name, e.middle_name, e.last_name, d.dept_name as dept_name, 
            a.check_in, a.check_out, a.status, a.working_hrs, a.attendance_id,
            s.shift_name, s.start_time as shift_start_time, s.end_time as shift_end_time
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.dept_id
            LEFT JOIN (
                SELECT a1.* FROM attendance a1 
                INNER JOIN (
                    SELECT emp_id, MAX(check_in) as max_check_in
                    FROM attendance
                    GROUP BY emp_id
                ) a2 ON a1.emp_id = a2.emp_id AND a1.check_in = a2.max_check_in
            ) a ON e.emp_id = a.emp_id
            LEFT JOIN shifts s ON e.shift_id = s.id
            WHERE e.role != 'admin' AND (e.status = 'active' OR e.status IS NULL) AND (e.is_deleted = 0 OR e.is_deleted IS NULL)
            ORDER BY e.emp_id ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . implode(', ', $pdo->errorInfo())
        ]);
        exit;
    }
    
    $data = [];
    while ($row = $stmt->fetch()) {
        // Ensure department name is properly set
        $row['dept_name'] = $row['dept_name'] ?? 'Not Assigned';
        $data[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    exit;
}

// If method not supported
echo json_encode(['success' => false, 'message' => 'Method not supported']);
    exit;
?>