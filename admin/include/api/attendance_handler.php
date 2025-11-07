<?php
require_once '../../../config.php';
header('Content-Type: application/json');
ob_clean(); // Clear any output buffers

define('WORKDAY_STARTS_AT_HOUR', 10);
date_default_timezone_set('Asia/Karachi');

// Function to determine workday based on 10 AM to 10 AM logic
function getWorkdayBasedOn10AM($current_time = null) {
    if (!$current_time) {
        $current_time = new DateTime('now', new DateTimeZone('Asia/Karachi'));
    }
    
    $current_hour = intval($current_time->format('H'));
    
    // If current time is before 10 AM, consider it as previous day's workday
    if ($current_hour < 10) {
        $workday_date = (clone $current_time)->modify('-1 day')->format('Y-m-d');
    } else {
        // If current time is 10 AM or after, consider it as current day's workday
        $workday_date = $current_time->format('Y-m-d');
    }
    
    return $workday_date;
}

// Check if user is logged in (for user attendance)
session_start();
$is_user_request = isset($_SESSION['emp_id']);

function mark_or_update_attendance($emp_id, $date, $check_in, $check_out, $status = null, $shift_id = null, $reason = '') {
    global $pdo;
    
    // Shift info lao
    if (!$shift_id) {
        $shiftQ = $pdo->prepare("SELECT shift_id FROM employees WHERE emp_id = ?");
        $shiftQ->execute([$emp_id]);
        if ($shiftQ->rowCount() > 0) {
            $shift_id = $shiftQ->fetch()['shift_id'];
        }
    }
    
    // Get shift info from database - NO HARDCODED VALUES
    $shift = null;
    if ($shift_id) {
        $shiftQ = $pdo->prepare("SELECT * FROM shifts WHERE id = ?");
        $shiftQ->execute([$shift_id]);
        if ($shiftQ->rowCount() > 0) {
            $shift = $shiftQ->fetch();
        }
    }
    
    // If no shift_id provided, get employee's assigned shift
    if (!$shift) {
        $emp_shiftQ = $pdo->prepare("SELECT s.* FROM employees e LEFT JOIN shifts s ON e.shift_id = s.id WHERE e.emp_id = ?");
        $emp_shiftQ->execute([$emp_id]);
        if ($emp_shiftQ->rowCount() > 0) {
            $shift = $emp_shiftQ->fetch();
        }
    }
    
    // Default values only if no shift found in database
    if (!$shift) {
        $shift = [
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'grace_time' => 15,
            'halfday_hours' => 4
        ];
    }
    
    // Check if record exists
    $check_sql = "SELECT * FROM attendance WHERE emp_id = ? AND DATE(check_in) = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$emp_id, $date]);
    
    if ($check_stmt->rowCount() > 0) {
        // Update existing record
        $row = $check_stmt->fetch();
        $update_sql = "UPDATE attendance SET ";
        $updates = [];
        $params = [];
        
        if ($check_in) {
            $updates[] = "check_in = ?";
            $params[] = "$date $check_in:00";
        }
        if ($check_out) {
            $updates[] = "check_out = ?";
            $params[] = "$date $check_out:00";
        }
        if ($status) {
            $updates[] = "status = ?";
            $params[] = $status;
        }
        if ($reason !== '') {
            $updates[] = "reason = ?";
            $params[] = $reason;
        }
        if ($shift_id) {
            $updates[] = "shift_id = ?";
            $params[] = $shift_id;
        }
        
        // Calculate working hours if both times are provided
        if ($check_in && $check_out) {
            $check_in_dt = strtotime("$date $check_in:00");
            $check_out_dt = strtotime("$date $check_out:00");
            if ($check_out_dt <= $check_in_dt) {
                $check_out_dt = strtotime("$date $check_out:00 +1 day");
            }
            $diff = $check_out_dt - $check_in_dt;
            if ($diff > 0) {
                $hours = floor($diff / 3600);
                $minutes = floor(($diff % 3600) / 60);
                $working_hrs = "{$hours}h {$minutes}m";
                $updates[] = "working_hrs = ?";
                $params[] = $working_hrs;
            }
        }
        
        $update_sql .= implode(', ', $updates);
        $update_sql .= " WHERE emp_id = ? AND DATE(check_in) = ?";
        $params[] = $emp_id;
        $params[] = $date;
        
        $update_stmt = $pdo->prepare($update_sql);
        if ($update_stmt->execute($params)) {
            return ['success' => true, 'message' => 'Attendance updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Update failed: ' . implode(', ', $update_stmt->errorInfo())];
        }
    } else {
        // Insert new record
        $insert_sql = "INSERT INTO attendance (emp_id, check_in, check_out, status, shift_id, reason) VALUES (?, ?, ?, ?, ?, ?)";
        $insert_stmt = $pdo->prepare($insert_sql);
        
        if ($insert_stmt->execute([$emp_id, "$date $check_in:00", "$date $check_out:00", $status, $shift_id, $reason])) {
            return ['success' => true, 'message' => 'Attendance inserted successfully'];
        } else {
            return ['success' => false, 'message' => 'Insert failed: ' . implode(', ', $insert_stmt->errorInfo())];
        }
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $emp_id = intval($_POST['emp_id'] ?? ($is_user_request ? $_SESSION['emp_id'] : 0));
    
    if (!$emp_id) {
        echo json_encode(['success' => false, 'message' => 'Employee ID required']);
        exit;
    }
    
    switch ($action) {
        case 'check_in':
            // User check-in logic
            $reason = $_POST['reason'] ?? '';
            $current_time = new DateTime('now', new DateTimeZone('Asia/Karachi'));
            $check_in_time = $current_time->format('H:i:s');
            
            // Get workday based on 10 AM to 10 AM logic
            $workday_date = getWorkdayBasedOn10AM($current_time);
            
            // Check if already checked in for this workday
            $check_sql = "SELECT attendance_id FROM attendance WHERE emp_id = ? AND DATE(check_in) = ?";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([$emp_id, $workday_date]);
            if ($check_stmt->rowCount() > 0) {
                echo json_encode(['success' => false, 'message' => 'Already checked in for this workday']);
                exit;
            }

            // --- NEW LOGIC: Insert absents for missing days ---
            // 1. Get employee shift_id for absent records
            $emp_shift_sql = "SELECT shift_id FROM employees WHERE emp_id = ?";
            $emp_shift_stmt = $pdo->prepare($emp_shift_sql);
            $emp_shift_stmt->execute([$emp_id]);
            $emp_shift_data = $emp_shift_stmt->fetch();
            $emp_shift_id = $emp_shift_data['shift_id'] ?? null;
            
            // 2. Last check-in date nikaalo
            $last_attendance_sql = "SELECT check_in FROM attendance WHERE emp_id = ? AND check_in IS NOT NULL ORDER BY check_in DESC LIMIT 1";
            $last_attendance_stmt = $pdo->prepare($last_attendance_sql);
            $last_attendance_stmt->execute([$emp_id]);
            $last_checkin_date = null;
            if ($row = $last_attendance_stmt->fetch()) {
                $last_checkin_date = date('Y-m-d', strtotime($row['check_in']));
            }
            // 3. Agar last check-in hai, to uske agle din se workday se ek din pehle tak loop chalao
            if ($last_checkin_date) {
                $start = (new DateTime($last_checkin_date))->modify('+1 day');
                $end = (new DateTime($workday_date))->modify('-1 day'); // workday se ek din pehle tak
                for ($d = clone $start; $d <= $end; $d->modify('+1 day')) {
                    $loop_date = $d->format('Y-m-d');
                    $dayOfWeek = $d->format('w');
                    $isWeekday = ($dayOfWeek >= 1 && $dayOfWeek <= 5); // Monday to Friday
                    if ($isWeekday) {
                        // Check if koi bhi attendance record nahi hai (kisi bhi status ke sath)
                        $check = $pdo->prepare("SELECT attendance_id FROM attendance WHERE emp_id = ? AND DATE(check_in) = ?");
                        $check->execute([$emp_id, $loop_date]);
                        if ($check->rowCount() == 0) {
                            $insert_absent = $pdo->prepare("INSERT INTO attendance (emp_id, check_in, check_out, status, shift_id) VALUES (?, ?, NULL, 'absent', ?)");
                            $insert_absent->execute([$emp_id, "$loop_date 00:00:00", $emp_shift_id]);
                        }
                    }
                }
            }
            // --- END NEW LOGIC ---
            
            // Calculate status based on shift from database - FIXED for overnight shifts
            $shift_sql = "SELECT e.shift_id, s.* FROM employees e LEFT JOIN shifts s ON e.shift_id = s.id WHERE e.emp_id = ?";
            $shift_stmt = $pdo->prepare($shift_sql);
            $shift_stmt->execute([$emp_id]);
            $shift_data = $shift_stmt->fetch();
            
            // Get shift_id from employee record
            $employee_shift_id = $shift_data['shift_id'] ?? null;
            
            $status = 'present';
            if ($shift_data && $shift_data['start_time']) {
                $shift_start_time = $shift_data['start_time'];
                $grace_minutes = $shift_data['grace_time'] ? intval($shift_data['grace_time']) : 15;
                
                // For overnight shifts, compare with previous day's shift start
                $shift_start_date = $workday_date;
                if ($shift_start_time >= '12:00:00' && $check_in_time < '12:00:00') {
                    // Overnight shift: check-in next day, shift started previous day
                    $shift_start_date = date('Y-m-d', strtotime($workday_date) - 86400); // Previous day
                }
                
                $shift_start_dt = strtotime($shift_start_date . " " . $shift_start_time);
                $check_in_dt = strtotime($workday_date . " " . $check_in_time);
                
                if ($check_in_dt > $shift_start_dt + ($grace_minutes * 60)) {
                    $status = 'late';
                }
            }
            
            // Insert attendance with check_out as NULL and msg_time if reason provided
            $msg_time = !empty($reason) ? date('Y-m-d H:i:s') : null;
            $insert_sql = "INSERT INTO attendance (emp_id, check_in, check_out, status, shift_id, reason, msg_time) VALUES (?, ?, NULL, ?, ?, ?, ?)";
            $insert_stmt = $pdo->prepare($insert_sql);
            if ($insert_stmt->execute([$emp_id, "$workday_date $check_in_time", $status, $employee_shift_id, $reason, $msg_time])) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Checked in successfully',
                    'trigger_update' => true,
                    'emp_id' => $emp_id,
                    'action' => 'check_in'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error checking in']);
            }
            break;
            
        case 'check_out':
            // User check-out logic
            $current_time = new DateTime('now', new DateTimeZone('Asia/Karachi'));
            $check_out_time = $current_time->format('H:i:s');
            $check_out_date = $current_time->format('Y-m-d');
            
            // Find latest open check-in record (regardless of date)
            $find_sql = "SELECT * FROM attendance WHERE emp_id = ? AND check_out IS NULL ORDER BY check_in DESC LIMIT 1";
            $find_stmt = $pdo->prepare($find_sql);
            $find_stmt->execute([$emp_id]);
            
            if ($find_stmt->rowCount() == 0) {
                echo json_encode(['success' => false, 'message' => 'No open check-in found for today']);
                exit;
            }
            
            $attendance_row = $find_stmt->fetch();
            $attendance_id = $attendance_row['attendance_id'];
            $check_in_time = date('H:i:s', strtotime($attendance_row['check_in']));
            
            // Shift info
            $shift_sql = "SELECT s.* FROM employees e LEFT JOIN shifts s ON e.shift_id = s.id WHERE e.emp_id = ?";
            $shift_stmt = $pdo->prepare($shift_sql);
            $shift_stmt->execute([$emp_id]);
            $shift_data = $shift_stmt->fetch();
            $shift_start = $shift_data && $shift_data['start_time'] ? $shift_data['start_time'] : '09:00:00';
            $grace_minutes = $shift_data && isset($shift_data['grace_time']) ? intval($shift_data['grace_time']) : 15;
            $halfday_hours = $shift_data && isset($shift_data['halfday_hours']) ? floatval($shift_data['halfday_hours']) : 4;
            
            // Calculate working hours and status
            $check_in_dt = strtotime($attendance_row['check_in']);
            $check_out_dt = strtotime("$check_out_date $check_out_time");
            
            // Get check-in date from database record
            $check_in_date = date('Y-m-d', $check_in_dt);
            
            // Handle overnight shifts properly
            if ($check_out_dt <= $check_in_dt) {
                // Check if this is an overnight shift (check-out next day)
                $check_out_date_actual = date('Y-m-d', $check_out_dt);
                
                if ($check_in_date != $check_out_date_actual) {
                    // This is an overnight shift, add 24 hours
                    $check_out_dt = strtotime("$check_out_date $check_out_time +1 day");
                } else {
                    // Same day but check-out time is before check-in (invalid)
                    $check_out_dt = strtotime("$check_out_date $check_out_time +1 day");
                }
            }
            
            // Universal working hours calculation logic
            // Always use check-in date for calculation to get correct working hours
            $check_out_dt = strtotime("$check_in_date $check_out_time");
            
            // If check-out time is before check-in time, add 24 hours
            if ($check_out_dt <= $check_in_dt) {
                $check_out_dt = strtotime("$check_in_date $check_out_time +1 day");
            }
            $diff = $check_out_dt - $check_in_dt;
            $hours = floor($diff / 3600);
            $minutes = floor(($diff % 3600) / 60);
            $working_hrs = "{$hours}h {$minutes}m";
            $working_hours = $diff / 3600;
            
            // Status calculation
            $status = 'present';
            if ($working_hours > 0 && $working_hours < $halfday_hours) {
                $status = 'half-day';
            } else {
                // Late check-in check karo - FIXED for overnight shifts
                $check_in_date = date('Y-m-d', strtotime($attendance_row['check_in']));
                $shift_start_time = $shift_data['start_time'];
                
                // For overnight shifts, if check-in is next day, use previous day for shift start
                $shift_start_date = $check_in_date;
                if ($shift_start_time >= '12:00:00' && date('H:i:s', strtotime($attendance_row['check_in'])) < '12:00:00') {
                    // Overnight shift: check-in next day, shift started previous day
                    $shift_start_date = date('Y-m-d', strtotime($attendance_row['check_in']) - 86400); // Previous day
                }
                
                $shift_start_dt = strtotime($shift_start_date . " " . $shift_start_time);
                if ($check_in_dt > $shift_start_dt + ($grace_minutes * 60)) {
                    $status = 'late';
                }
            }
            
            // Update by attendance_id
            $update_sql = "UPDATE attendance SET check_out = ?, working_hrs = ?, status = ? WHERE attendance_id = ? AND emp_id = ? AND check_out IS NULL";
            $update_stmt = $pdo->prepare($update_sql);
            
            if ($update_stmt->execute([$check_out_date . ' ' . $check_out_time, $working_hrs, $status, $attendance_id, $emp_id])) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Checked out successfully!',
                    'trigger_update' => true,
                    'emp_id' => $emp_id,
                    'action' => 'check_out'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Check-out failed: ' . implode(', ', $update_stmt->errorInfo())]);
            }
            break;
            
        case 'update_reason':
            // Update reason for specific attendance record
            $attendance_id = intval($_POST['attendance_id'] ?? 0);
            $reason = $_POST['reason'] ?? '';
            $msg_time = date('Y-m-d H:i:s');
            
            if (!$attendance_id) {
                echo json_encode(['success' => false, 'message' => 'Attendance ID required']);
                exit;
            }
            
            $update_sql = "UPDATE attendance SET reason = ?, msg_time = ? WHERE attendance_id = ? AND emp_id = ?";
            $update_stmt = $pdo->prepare($update_sql);
            
            if ($update_stmt->execute([$reason, $msg_time, $attendance_id, $emp_id])) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Reason updated successfully',
                    'trigger_update' => true,
                    'emp_id' => $emp_id,
                    'action' => 'update_reason'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Update failed: ' . implode(', ', $update_stmt->errorInfo())]);
            }
            break;
            
        case 'bulk_mark_attendance':
            $emp_ids = $_POST['emp_ids'] ?? [];
            $date = $_POST['date'] ?? date('Y-m-d');
            $status = strtolower($_POST['status'] ?? 'present');
            $success = 0; $fail = 0;

            foreach ($emp_ids as $emp_id) {
                // Shift info
                $shift_sql = "SELECT shift_id, s.start_time, s.end_time FROM employees e LEFT JOIN shifts s ON e.shift_id = s.id WHERE e.emp_id = ?";
                $shift_stmt = $pdo->prepare($shift_sql);
                $shift_stmt->execute([$emp_id]);
                $shift_id = null;
                $shift_start_time = '09:00:00';
                $shift_end_time = null;
                if ($shift_row = $shift_stmt->fetch()) {
                    $shift_id = $shift_row['shift_id'];
                    $shift_start_time = $shift_row['start_time'] ?: '09:00:00';
                    $shift_end_time = $shift_row['end_time'];
                }

                $check = $pdo->prepare("SELECT attendance_id FROM attendance WHERE emp_id = ? AND DATE(check_in) = ?");
                $check->execute([$emp_id, $date]);
                $check_in = $date . ' ' . $shift_start_time;
                $check_out = $shift_end_time ? ($date . ' ' . $shift_end_time) : null;

                if ($check->rowCount() == 0) {
                    // Insert present
                    $sql = "INSERT INTO attendance (emp_id, check_in, check_out, status, shift_id) VALUES (?, ?, ?, ?, ?)";
                    $insert_stmt = $pdo->prepare($sql);
                    if ($insert_stmt->execute([$emp_id, $check_in, $check_out, $status, $shift_id])) $success++; else $fail++;
                } else {
                    // Update to present
                    $row = $check->fetch();
                    $update_sql = "UPDATE attendance SET status = ?, check_in = ?, check_out = ?, shift_id = ?, working_hrs = NULL WHERE attendance_id = ?";
                    $update_stmt = $pdo->prepare($update_sql);
                    if ($update_stmt->execute([$status, $check_in, $check_out, $shift_id, $row['attendance_id']])) $success++; else $fail++;
                }
            }
            echo json_encode([
                'success' => true, 
                'marked' => $success, 
                'skipped' => $fail,
                'trigger_update' => true,
                'action' => 'bulk_mark_attendance'
            ]);
            break;
            
        case 'admin_mark_attendance':
            // Admin individual attendance marking
            $emp_id = intval($_POST['emp_id'] ?? 0);
            $date = $_POST['date'] ?? date('Y-m-d');
            $check_in = $_POST['check_in'] ?? null;
            $check_out = $_POST['check_out'] ?? null;
            $status = $_POST['status'] ?? 'Present';
            
            if (!$emp_id) {
                echo json_encode(['success' => false, 'message' => 'Employee ID required']);
                exit;
            }
            
            $result = mark_or_update_attendance($emp_id, $date, $check_in, $check_out, $status, null, '');
            echo json_encode($result);
            break;
            
        case 'admin_edit_attendance':
            // Admin attendance edit
            $emp_id = intval($_POST['emp_id'] ?? 0);
            $date = $_POST['date'] ?? date('Y-m-d');
            $check_in = $_POST['check_in'] ?? null;
            $check_out = $_POST['check_out'] ?? null;
            
            if (!$emp_id) {
                echo json_encode(['success' => false, 'message' => 'Employee ID required']);
                exit;
            }
            
            // Calculate status based on shift
            $shift_sql = "SELECT s.start_time, s.end_time, s.grace_time, s.halfday_hours FROM employees e LEFT JOIN shifts s ON e.shift_id = s.id WHERE e.emp_id = ?";
            $shift_stmt = $pdo->prepare($shift_sql);
            $shift_stmt->execute([$emp_id]);
            $shift = $shift_stmt->rowCount() > 0 ? $shift_stmt->fetch() : null;

            $status = 'present'; // default

            if (!$shift || empty($check_in) || empty($check_out) || $check_in === "00:00" || $check_out === "00:00") {
                $status = 'absent';
            } else {
                $shift_start = $shift['start_time'];
                $shift_end = $shift['end_time'];
                $grace = isset($shift['grace_time']) ? intval($shift['grace_time']) : 15; // minutes
                $halfday = isset($shift['halfday_hours']) ? floatval($shift['halfday_hours']) : 4; // hours

                $check_in_dt = strtotime("$date $check_in:00");
                $check_out_dt = strtotime("$date $check_out:00");
                
                // FIXED for overnight shifts - admin edit logic
                $shift_start_date = $date;
                if ($shift_start >= '12:00:00' && $check_in < '12:00:00') {
                    // Overnight shift: check-in next day, shift started previous day
                    $shift_start_date = date('Y-m-d', strtotime($date) - 86400); // Previous day
                }
                
                $shift_start_dt = strtotime("$shift_start_date $shift_start");
                $shift_end_dt = strtotime("$date $shift_end");

                if ($check_in_dt > $shift_start_dt + ($grace * 60)) {
                    $status = 'late';
                } else {
                    $status = 'present';
                }

                $working_seconds = $check_out_dt - $check_in_dt;
                $working_hours = $working_seconds / 3600;

                if ($working_hours > 0 && $working_hours < $halfday) {
                    $status = 'half-day';
                }

                if ($check_in === "00:00" || $check_out === "00:00") {
                    $status = 'absent';
                }
            }

            // Working hours calculate karo (agar dono time sahi hain)
            $working_hrs = '0h 0m';
            if (!empty($check_in) && !empty($check_out) && $check_in !== "00:00" && $check_out !== "00:00") {
                $check_in_dt = strtotime("$date $check_in:00");
                $check_out_dt = strtotime("$date $check_out:00");
                // Cross-midnight shift: agar check_out, check_in se pehle ya barabar hai, to agle din ka samjho
                if ($check_out_dt <= $check_in_dt) {
                    $check_out_dt = strtotime("$date $check_out:00 +1 day");
                }
                $diff = $check_out_dt - $check_in_dt;
                if ($diff > 0) {
                    $hours = floor($diff / 3600);
                    $minutes = floor(($diff % 3600) / 60);
                    $working_hrs = "{$hours}h {$minutes}m";
                }
            }

            // Pehle check karo record hai ya nahi
            $sql = "SELECT * FROM attendance WHERE emp_id = ? AND DATE(check_in) = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$emp_id, $date]);

            if ($stmt->rowCount() > 0) {
                // Record hai, update karo (reason/message preserve karo)
                $row = $stmt->fetch();
                $reason = isset($row['reason']) ? $row['reason'] : '';
                $msg_time = isset($row['msg_time']) ? $row['msg_time'] : null;
                $update = "UPDATE attendance SET check_in = ?, check_out = ?, status = ?, working_hrs = ?, reason = ?, msg_time = ? WHERE emp_id = ? AND DATE(check_in) = ?";
                $update_stmt = $pdo->prepare($update);
                if ($update_stmt->execute([$date . ' ' . $check_in . ':00', $date . ' ' . $check_out . ':00', $status, $working_hrs, $reason, $msg_time, $emp_id, $date])) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Attendance updated',
                        'trigger_update' => true,
                        'emp_id' => $emp_id,
                        'action' => 'admin_edit_attendance'
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Update failed: ' . implode(', ', $update_stmt->errorInfo())]);
                }
            } else {
                // Record nahi hai, naya insert karo
                $insert = "INSERT INTO attendance (emp_id, check_in, check_out, status, working_hrs, reason) VALUES (?, ?, ?, ?, ?, ?)";
                $insert_stmt = $pdo->prepare($insert);
                if ($insert_stmt->execute([$emp_id, $date . ' ' . $check_in . ':00', $date . ' ' . $check_out . ':00', $status, $working_hrs, ''])) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Attendance inserted',
                        'trigger_update' => true,
                        'emp_id' => $emp_id,
                        'action' => 'admin_edit_attendance'
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Insert failed: ' . implode(', ', $insert_stmt->errorInfo())]);
                }
            }
            break;
            
        default:
            // Admin attendance mark/edit (existing logic)
            $emp_id = intval($_POST['emp_id'] ?? 0);
            $date = $_POST['date'] ?? date('Y-m-d');
            $check_in = $_POST['check_in'] ?? null;
            $check_out = $_POST['check_out'] ?? null;
            $status = $_POST['status'] ?? null;
            $shift_id = $_POST['shift_id'] ?? null;
            $reason = $_POST['reason'] ?? '';
            
            if (!$emp_id) {
                echo json_encode(['success' => false, 'message' => 'Employee ID required']);
                exit;
            }
            
            $result = mark_or_update_attendance($emp_id, $date, $check_in, $check_out, $status, $shift_id, $reason);
            echo json_encode($result);
            break;
    }
} else {
    // Handle other methods
    echo json_encode(['success' => false, 'message' => 'Method not supported']);
}
exit;