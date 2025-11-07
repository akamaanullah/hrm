<?php
session_start();
require_once dirname(__DIR__, 3) . '/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['emp_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired']);
    exit;
}

$emp_id = $_SESSION['emp_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'start_break':
            startBreak($pdo, $emp_id);
            break;
        case 'end_break':
            endBreak($pdo, $emp_id);
            break;
        case 'get_active_break':
            getActiveBreak($pdo, $emp_id);
            break;
        case 'get_break_history':
            getBreakHistory($pdo, $emp_id);
            break;
        case 'get_attendance_status':
            getAttendanceStatus($pdo, $emp_id);
            break;
        case 'auto_end_break_on_checkout':
            autoEndBreakOnCheckout($pdo, $emp_id);
            break;
        case 'get_break_by_attendance':
            getBreakByAttendance($pdo, $emp_id);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function startBreak($pdo, $emp_id) {
    try {
        // Check if break_records table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'break_records'");
        if ($stmt->rowCount() == 0) {
            echo json_encode(['success' => false, 'message' => 'Break system not initialized. Please contact administrator.']);
            return;
        }
        
        // Check if user already has an active break
        $stmt = $pdo->prepare("SELECT break_id FROM break_records WHERE emp_id = ? AND status = 'active'");
        $stmt->execute([$emp_id]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'You already have an active break. Please end it first.']);
            return;
        }
        
        // Get today's attendance record for this employee
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("SELECT attendance_id, check_in, status FROM attendance WHERE emp_id = ? AND DATE(check_in) = ? ORDER BY check_in DESC LIMIT 1");
        $stmt->execute([$emp_id, $today]);
        $attendance = $stmt->fetch();
        
        $attendance_id = $attendance ? $attendance['attendance_id'] : null;
        
        // If no attendance record found, try to find any recent attendance (within last 7 days)
        if (!$attendance_id) {
            $stmt = $pdo->prepare("SELECT attendance_id, check_in, status FROM attendance WHERE emp_id = ? AND check_in >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY check_in DESC LIMIT 1");
            $stmt->execute([$emp_id]);
            $recent_attendance = $stmt->fetch();
            $attendance_id = $recent_attendance ? $recent_attendance['attendance_id'] : null;
        }
        
        // Start new break with attendance_id
        $stmt = $pdo->prepare("INSERT INTO break_records (emp_id, attendance_id, break_start, status) VALUES (?, ?, NOW(), 'active')");
        $stmt->execute([$emp_id, $attendance_id]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Break started successfully!',
            'break_id' => $pdo->lastInsertId(),
            'break_start' => date('Y-m-d H:i:s'),
            'attendance_id' => $attendance_id,
            'attendance_info' => $attendance ? [
                'check_in' => $attendance['check_in'],
                'status' => $attendance['status']
            ] : null
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage(), 'debug' => $e->getTraceAsString()]);
    }
}

function endBreak($pdo, $emp_id) {
    try {
        // Get active break
        $stmt = $pdo->prepare("SELECT break_id, break_start FROM break_records WHERE emp_id = ? AND status = 'active'");
        $stmt->execute([$emp_id]);
        $break = $stmt->fetch();
        
        if (!$break) {
            echo json_encode(['success' => false, 'message' => 'No active break found.']);
            return;
        }
        
        $break_end = date('Y-m-d H:i:s');
        $break_start = $break['break_start'];
        
        // Calculate duration
        $start_time = new DateTime($break_start);
        $end_time = new DateTime($break_end);
        $duration = $start_time->diff($end_time);
        
        // Format duration nicely
        $hours = $duration->h;
        $minutes = $duration->i;
        $seconds = $duration->s;
        
        if ($hours > 0) {
            $duration_str = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ' . $minutes . ' minute' . ($minutes != 1 ? 's' : '');
        } else if ($minutes > 0) {
            $duration_str = $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ' . $seconds . ' second' . ($seconds != 1 ? 's' : '');
        } else {
            $duration_str = $seconds . ' second' . ($seconds != 1 ? 's' : '');
        }
        
        // Update break record
        $stmt = $pdo->prepare("UPDATE break_records SET break_end = ?, break_duration = ?, status = 'completed' WHERE break_id = ?");
        $stmt->execute([$break_end, $duration_str, $break['break_id']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Break ended successfully!',
            'break_duration' => $duration_str,
            'break_start' => $break_start,
            'break_end' => $break_end
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage(), 'debug' => $e->getTraceAsString()]);
    }
}

function getActiveBreak($pdo, $emp_id) {
    $stmt = $pdo->prepare("
        SELECT br.break_id, br.break_start, br.attendance_id,
               a.check_in, a.status as attendance_status
        FROM break_records br
        LEFT JOIN attendance a ON br.attendance_id = a.attendance_id
        WHERE br.emp_id = ? AND br.status = 'active'
    ");
    $stmt->execute([$emp_id]);
    $break = $stmt->fetch();
    
    if ($break) {
        $start_time = new DateTime($break['break_start']);
        $current_time = new DateTime();
        $duration = $start_time->diff($current_time);
        
        // Format duration nicely
        $hours = $duration->h;
        $minutes = $duration->i;
        $seconds = $duration->s;
        
        if ($hours > 0) {
            $duration_str = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ' . $minutes . ' minute' . ($minutes != 1 ? 's' : '');
        } else if ($minutes > 0) {
            $duration_str = $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ' . $seconds . ' second' . ($seconds != 1 ? 's' : '');
        } else {
            $duration_str = $seconds . ' second' . ($seconds != 1 ? 's' : '');
        }
        
        // Prepare attendance info
        $attendance_info = null;
        if ($break['check_in']) {
            $attendance_info = [
                'check_in' => $break['check_in'],
                'status' => $break['attendance_status']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'has_active_break' => true,
            'break_start' => $break['break_start'],
            'current_duration' => $duration_str,
            'break_id' => $break['break_id'],
            'attendance_id' => $break['attendance_id'],
            'attendance_info' => $attendance_info,
            'duration_minutes' => ($hours * 60) + $minutes,
            'duration_seconds' => ($hours * 3600) + ($minutes * 60) + $seconds
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'has_active_break' => false
        ]);
    }
}

function getBreakHistory($pdo, $emp_id) {
    // Check for date range parameters
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;
    
    $sql = "
        SELECT br.break_id, br.break_start, br.break_end, br.break_duration, br.status, br.created_at, br.attendance_id,
               a.check_in, a.check_out, a.status as attendance_status
        FROM break_records br
        LEFT JOIN attendance a ON br.attendance_id = a.attendance_id
        WHERE br.emp_id = ? 
    ";
    
    $params = [$emp_id];
    
    // Add date range filter if provided
    if ($start_date && $end_date) {
        $sql .= " AND DATE(a.check_in) BETWEEN ? AND ?";
        $params[] = $start_date;
        $params[] = $end_date;
    }
    
    $sql .= " ORDER BY br.created_at DESC LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $breaks = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'breaks' => $breaks
    ]);
}

function getAttendanceStatus($pdo, $emp_id) {
    try {
        $today = date('Y-m-d');
        
        // Get today's attendance record first
        $stmt = $pdo->prepare("
            SELECT attendance_id, check_in, check_out, status 
            FROM attendance 
            WHERE emp_id = ? AND DATE(check_in) = ? 
            ORDER BY check_in DESC 
            LIMIT 1
        ");
        $stmt->execute([$emp_id, $today]);
        $attendance = $stmt->fetch();
        
        // If no attendance for today, get recent attendance (within last 7 days)
        if (!$attendance) {
            $stmt = $pdo->prepare("
                SELECT attendance_id, check_in, check_out, status 
                FROM attendance 
                WHERE emp_id = ? AND check_in >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
                ORDER BY check_in DESC 
                LIMIT 1
            ");
            $stmt->execute([$emp_id]);
            $attendance = $stmt->fetch();
        }
        
        $canTakeBreak = false;
        $message = '';
        
        if (!$attendance) {
            // No attendance record found
            $message = 'Please check-in first before taking a break.';
        } elseif (!$attendance['check_in']) {
            // No check-in time
            $message = 'Please check-in first before taking a break.';
        } elseif ($attendance['check_out']) {
            // Already checked out
            $message = 'Cannot take break after check-out.';
        } else {
            // Can take break (checked in but not checked out)
            $canTakeBreak = true;
            $message = 'You can take a break now.';
        }
        
        echo json_encode([
            'success' => true,
            'can_take_break' => $canTakeBreak,
            'message' => $message,
            'attendance' => $attendance ? [
                'check_in' => $attendance['check_in'],
                'check_out' => $attendance['check_out'],
                'status' => $attendance['status']
            ] : null
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Error checking attendance status: ' . $e->getMessage()
        ]);
    }
}

function autoEndBreakOnCheckout($pdo, $emp_id) {
    try {
        $today = date('Y-m-d');
        
        // Get today's attendance record
        $stmt = $pdo->prepare("
            SELECT attendance_id, check_in, check_out, status 
            FROM attendance 
            WHERE emp_id = ? AND DATE(check_in) = ? 
            ORDER BY check_in DESC 
            LIMIT 1
        ");
        $stmt->execute([$emp_id, $today]);
        $attendance = $stmt->fetch();
        
        // If no attendance for today, get recent attendance
        if (!$attendance) {
            $stmt = $pdo->prepare("
                SELECT attendance_id, check_in, check_out, status 
                FROM attendance 
                WHERE emp_id = ? AND check_in >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
                ORDER BY check_in DESC 
                LIMIT 1
            ");
            $stmt->execute([$emp_id]);
            $attendance = $stmt->fetch();
        }
        
        // Check if user has checked out
        if ($attendance && $attendance['check_out']) {
            // User has checked out, check if there's an active break
            $stmt = $pdo->prepare("SELECT break_id, break_start FROM break_records WHERE emp_id = ? AND status = 'active'");
            $stmt->execute([$emp_id]);
            $activeBreak = $stmt->fetch();
            
            if ($activeBreak) {
                // Auto-end the active break
                $break_end = date('Y-m-d H:i:s');
                $break_start = $activeBreak['break_start'];
                
                // Calculate duration
                $start_time = new DateTime($break_start);
                $end_time = new DateTime($break_end);
                $duration = $start_time->diff($end_time);
                
                // Format duration nicely
                $hours = $duration->h;
                $minutes = $duration->i;
                $seconds = $duration->s;
                
                if ($hours > 0) {
                    $duration_str = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ' . $minutes . ' minute' . ($minutes != 1 ? 's' : '');
                } else if ($minutes > 0) {
                    $duration_str = $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ' . $seconds . ' second' . ($seconds != 1 ? 's' : '');
                } else {
                    $duration_str = $seconds . ' second' . ($seconds != 1 ? 's' : '');
                }
                
                // Update break record
                $stmt = $pdo->prepare("UPDATE break_records SET break_end = ?, break_duration = ?, status = 'completed' WHERE break_id = ?");
                $stmt->execute([$break_end, $duration_str, $activeBreak['break_id']]);
                
                echo json_encode([
                    'success' => true,
                    'auto_ended' => true,
                    'message' => 'Break automatically ended due to check-out.',
                    'break_duration' => $duration_str,
                    'break_start' => $break_start,
                    'break_end' => $break_end
                ]);
                return;
            }
        }
        
        // No active break to end or user hasn't checked out
        echo json_encode([
            'success' => true,
            'auto_ended' => false,
            'message' => 'No active break to end.'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Error auto-ending break: ' . $e->getMessage()
        ]);
    }
}

function getBreakByAttendance($pdo, $emp_id) {
    try {
        // Get attendance_id from request
        $attendance_id = $_GET['attendance_id'] ?? null;
        
        if (!$attendance_id) {
            echo json_encode(['success' => false, 'message' => 'Attendance ID is required']);
            return;
        }
        
        // Verify that the attendance belongs to this employee
        $stmt = $pdo->prepare("SELECT emp_id FROM attendance WHERE attendance_id = ?");
        $stmt->execute([$attendance_id]);
        $attendance = $stmt->fetch();
        
        if (!$attendance || $attendance['emp_id'] != $emp_id) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access to attendance record']);
            return;
        }
        
        // Fetch break records for this attendance
        $sql = "
            SELECT break_id, break_start, break_end, break_duration, status, created_at
            FROM break_records
            WHERE emp_id = ? AND attendance_id = ?
            ORDER BY break_start DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$emp_id, $attendance_id]);
        $breaks = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'breaks' => $breaks
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Error fetching break records: ' . $e->getMessage()
        ]);
    }
}
?>
