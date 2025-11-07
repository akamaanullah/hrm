<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json');
require_once '../../../config.php';
// Check if user is logged in
session_start();
if (!isset($_SESSION['emp_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}
$method = $_SERVER['REQUEST_METHOD'];
$emp_id = $_SESSION['emp_id'];
// Only handle GET requests for attendance display
if ($method === 'GET') {
    // Handle get by status request
    if (isset($_GET['action']) && $_GET['action'] === 'get_by_status') {
        $status = $_GET['status'] ?? '';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        if (!$status || !$start_date || !$end_date) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }
        // Map status names to database values
        $statusMap = [
            'present' => 'Present',
            'absent' => 'Absent', 
            'late' => 'Late',
            'half-day' => 'Half-day'
        ];
        $dbStatus = $statusMap[$status] ?? $status;
        $sql = "SELECT * FROM attendance 
                WHERE emp_id = ? 
                AND status = ? 
                AND DATE(check_in) BETWEEN ? AND ?
                ORDER BY check_in DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$emp_id, $dbStatus, $start_date, $end_date]);
        $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Format time fields for better display
        foreach ($attendance as &$record) {
            // Agar absent hai to check_in_formatted aur check_out_formatted dono '-' kar do
            if ($record['status'] === 'Absent' || $record['status'] === 'absent') {
                $record['check_in_formatted'] = '-';
                $record['check_out_formatted'] = '-';
            } else {
                if ($record['check_in']) {
                    $record['check_in_formatted'] = date('h:i A', strtotime($record['check_in']));
                } else {
                    $record['check_in_formatted'] = '-';
                }
                if ($record['check_out']) {
                    $record['check_out_formatted'] = date('h:i A', strtotime($record['check_out']));
                } else {
                    $record['check_out_formatted'] = '-';
                }
            }
            // Add workday field
            if ($record['check_in']) {
                $record['workday'] = date('Y-m-d', strtotime($record['check_in']));
            }
        }   
        echo json_encode(['success' => true, 'data' => $attendance]);
        exit;
    }
    // Handle current status request
    if (isset($_GET['action']) && $_GET['action'] === 'current_status') {
        // Check if user is checked in (regardless of date - for overnight shifts)
        $sql = "SELECT * FROM attendance WHERE emp_id = ? AND check_out IS NULL ORDER BY check_in DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$emp_id]);
        $currentAttendance = $stmt->fetch();
        $isCheckedIn = false;
        $checkInTime = null;
        if ($currentAttendance && $currentAttendance['status'] !== 'absent') {
            $isCheckedIn = true;
            $checkInTime = $currentAttendance['check_in'];
        }   
        echo json_encode([
            'success' => true,
            'data' => [
                'is_checked_in' => $isCheckedIn,
                'check_in_time' => $checkInTime
            ]
        ]);
        exit;
    }
    // Handle get employee info request (for joining date)
    if (isset($_GET['action']) && $_GET['action'] === 'get_employee_info') {
        $sql = "SELECT emp_id, first_name, middle_name, last_name, joining_date FROM employees WHERE emp_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$emp_id]);
        $employee = $stmt->fetch();   
        echo json_encode([
            'success' => true,
            'employee' => $employee
        ]);
        exit;
    }
        // Check if date range parameters are provided
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
        if ($start_date && $end_date) {
            // Use date range filter with joining date restriction
            $sql = "SELECT a.*, e.joining_date FROM attendance a 
                    LEFT JOIN employees e ON a.emp_id = e.emp_id 
                    WHERE a.emp_id = ? AND DATE(a.check_in) BETWEEN ? AND ? 
                    AND DATE(a.check_in) >= e.joining_date 
                    ORDER BY a.check_in DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$emp_id, $start_date, $end_date]);
        } else {
            // Use month/year filter (default behavior) with joining date restriction
            $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
            $month = isset($_GET['month']) ? intval($_GET['month']) : date('n');   
            $sql = "SELECT a.*, e.joining_date FROM attendance a 
                    LEFT JOIN employees e ON a.emp_id = e.emp_id 
                    WHERE a.emp_id = ? AND YEAR(a.check_in) = ? AND MONTH(a.check_in) = ? 
                    AND DATE(a.check_in) >= e.joining_date 
                    ORDER BY a.check_in DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$emp_id, $year, $month]);
        }
        $attendance = [];
        $datesWithAttendance = [];
        while ($row = $stmt->fetch()) {
            // Agar absent hai to check_in_formatted aur check_out_formatted dono '-' kar do
            if ($row['status'] === 'absent') {
                $row['check_in_formatted'] = '-';
                $row['check_out_formatted'] = '-';
            } else {
            $row['check_in_formatted'] = $row['check_in'] ? date('h:i A', strtotime($row['check_in'])) : null;
            $row['check_out_formatted'] = $row['check_out'] ? date('h:i A', strtotime($row['check_out'])) : null;
            }   
            $checkInTime = $row['check_in'] ? new DateTime($row['check_in']) : null;
            $row['workday'] = $checkInTime ? $checkInTime->format('Y-m-d') : null;
            // reason bhi bhejo
            $row['reason'] = isset($row['reason']) ? $row['reason'] : '';
            $row['msg_time'] = isset($row['msg_time']) ? $row['msg_time'] : null;
            $attendance[] = $row;
            if ($row['workday']) {
                $datesWithAttendance[$row['workday']] = true;
            }
        }
        usort($attendance, function($a, $b) {
            return strcmp($b['workday'] ?? '', $a['workday'] ?? '');
        });
        echo json_encode(['success' => true, 'data' => $attendance]);
        exit;
    }
// For POST and PUT requests, redirect to central handler
if ($method === 'POST' || $method === 'PUT') {
    // Forward the request to central handler
    $input_data = $_POST;
    if ($method === 'PUT') {
        $input_data = json_decode(file_get_contents('php://input'), true);
    }
    // Add emp_id from session
    $input_data['emp_id'] = $emp_id;
    // Forward to central handler
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, '/admin/include/api/attendance_handler.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($input_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    $response = curl_exec($ch);
    curl_close($ch);   
    echo $response;
    exit;
}
// If method not supported
echo json_encode(['success' => false, 'message' => 'Method not supported']);
exit;
?>