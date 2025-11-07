<?php
header('Content-Type: application/json');
require_once '../../../config.php';
// Check if user is logged in as admin
session_start();
if (!isset($_SESSION['emp_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}
// Get action parameter
$action = $_GET['action'] ?? 'list';
switch ($action) {
    case 'list':
        // Get all attendance messages with employee details
        $sql = "SELECT 
                    a.attendance_id,
                    a.emp_id,
                    a.reason,
                    a.msg_time,
                    a.check_in,
                    a.check_out,
                    a.status,
                    e.first_name, e.middle_name, e.last_name,
                    e.designation,
                    d.dept_name as department
                FROM attendance a
                LEFT JOIN employees e ON a.emp_id = e.emp_id
                LEFT JOIN departments d ON e.department_id = d.dept_id
                WHERE a.reason IS NOT NULL 
                AND a.reason != '' 
                AND a.msg_time IS NOT NULL
                ORDER BY a.msg_time DESC
                LIMIT 50";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $messages]);
        break;
    case 'count':
        // Get count of unread messages for current admin
        $admin_emp_id = $_SESSION['emp_id'];
        // Debug: Check if admin_emp_id is set
        if (!$admin_emp_id) {
            echo json_encode(['success' => false, 'message' => 'Admin ID not found in session', 'count' => 0]);
            break;
        }
        // Debug: Check if admin_emp_id exists in employees table
        $check_admin_sql = "SELECT emp_id FROM employees WHERE emp_id = ? AND role = 'admin'";
        $check_admin_stmt = $pdo->prepare($check_admin_sql);
        $check_admin_stmt->execute([$admin_emp_id]);
        
        if ($check_admin_stmt->rowCount() == 0) {
            echo json_encode(['success' => false, 'message' => 'Admin ID ' . $admin_emp_id . ' not found in employees table or not an admin', 'count' => 0]);
            break;
        }
        $sql = "SELECT COUNT(*) as count 
                FROM attendance a
                LEFT JOIN admin_read_messages arm ON a.attendance_id = arm.attendance_id AND arm.admin_emp_id = ?
                WHERE a.reason IS NOT NULL 
                AND a.reason != '' 
                AND a.msg_time IS NOT NULL
                AND a.msg_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                AND arm.attendance_id IS NULL";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$admin_emp_id]);
        $count = $stmt->fetch()['count'];
        // Debug: Log the count result
        error_log("Unread messages count for admin " . $admin_emp_id . ": " . $count);
        echo json_encode(['success' => true, 'count' => $count, 'admin_emp_id' => $admin_emp_id]);
        break;
    case 'statistics':
        // Get detailed statistics
        $today = date('Y-m-d');
        // Today's messages
        $sql = "SELECT COUNT(*) as count 
                FROM attendance 
                WHERE reason IS NOT NULL 
                AND reason != '' 
                AND msg_time IS NOT NULL
                AND DATE(msg_time) = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$today]);
        $today_count = $stmt->fetch()['count'];
        // Late arrivals with messages
        $sql = "SELECT COUNT(*) as count 
                FROM attendance 
                WHERE reason IS NOT NULL 
                AND reason != '' 
                AND msg_time IS NOT NULL
                AND status = 'late'
                AND msg_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $late_count = $stmt->fetch()['count'];
        // Absent messages
        $sql = "SELECT COUNT(*) as count 
                FROM attendance 
                WHERE reason IS NOT NULL 
                AND reason != '' 
                AND msg_time IS NOT NULL
                AND status = 'absent'
                AND msg_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $absent_count = $stmt->fetch()['count'];
        echo json_encode([
            'success' => true, 
            'today_count' => $today_count,
            'late_count' => $late_count,
            'absent_count' => $absent_count
        ]);
        break;
    case 'mark_read':
        // Mark specific message as read
        $attendance_id = intval($_POST['attendance_id'] ?? 0);
        $admin_emp_id = $_SESSION['emp_id'];
        if ($attendance_id) {
            // Debug: Check if admin_emp_id exists in employees table
            $check_admin_sql = "SELECT emp_id FROM employees WHERE emp_id = ? AND role = 'admin'";
            $check_admin_stmt = $pdo->prepare($check_admin_sql);
            $check_admin_stmt->execute([$admin_emp_id]);
            
            if ($check_admin_stmt->rowCount() == 0) {
                echo json_encode(['success' => false, 'message' => 'Admin ID ' . $admin_emp_id . ' not found in employees table or not an admin']);
                exit;
            }
            try {
                // First, ensure admin_read_messages table exists
                $createTableSQL = "CREATE TABLE IF NOT EXISTS `admin_read_messages` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `attendance_id` INT(11) NOT NULL,
                    `admin_emp_id` INT(11) NOT NULL,
                    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
                    `read_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `unique_admin_read` (`attendance_id`, `admin_emp_id`),
                    KEY `idx_attendance_id` (`attendance_id`),
                    KEY `idx_admin_emp_id` (`admin_emp_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
                $pdo->exec($createTableSQL);
                // Check if record already exists
                $check_sql = "SELECT id FROM admin_read_messages WHERE attendance_id = ? AND admin_emp_id = ?";
                $check_stmt = $pdo->prepare($check_sql);
                $check_stmt->execute([$attendance_id, $admin_emp_id]);
                if ($check_stmt->rowCount() == 0) {
                    // Record doesn't exist, insert it
                    $sql = "INSERT INTO admin_read_messages (attendance_id, admin_emp_id, is_read) VALUES (?, ?, 1)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$attendance_id, $admin_emp_id]);
                } else {
                    // Record already exists, update it
                    $update_sql = "UPDATE admin_read_messages SET is_read = 1 WHERE attendance_id = ? AND admin_emp_id = ?";
                    $update_stmt = $pdo->prepare($update_sql);
                    $update_stmt->execute([$attendance_id, $admin_emp_id]);
                }
                echo json_encode(['success' => true, 'message' => 'Marked as read']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error marking as read: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid attendance ID']);
        }
        break;
    case 'mark_all_read':
        // Mark all messages as read for current admin
        $admin_emp_id = $_SESSION['emp_id'];
        // Debug: Check if admin_emp_id is set
        if (!$admin_emp_id) {
            echo json_encode(['success' => false, 'message' => 'Admin ID not found in session']);
            break;
        }
        // Debug: Check if admin_emp_id exists in employees table
        $check_admin_sql = "SELECT emp_id FROM employees WHERE emp_id = ? AND role = 'admin'";
        $check_admin_stmt = $pdo->prepare($check_admin_sql);
        $check_admin_stmt->execute([$admin_emp_id]);
        
        if ($check_admin_stmt->rowCount() == 0) {
            echo json_encode(['success' => false, 'message' => 'Admin ID ' . $admin_emp_id . ' not found in employees table or not an admin']);
            break;
        }
        try {
            // First, ensure admin_read_messages table exists
            $createTableSQL = "CREATE TABLE IF NOT EXISTS `admin_read_messages` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `attendance_id` INT(11) NOT NULL,
                `admin_emp_id` INT(11) NOT NULL,
                `is_read` TINYINT(1) NOT NULL DEFAULT 0,
                `read_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_admin_read` (`attendance_id`, `admin_emp_id`),
                KEY `idx_attendance_id` (`attendance_id`),
                KEY `idx_admin_emp_id` (`admin_emp_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
            $pdo->exec($createTableSQL);
            // Get all attendance messages that have reason and msg_time
            $sql = "SELECT attendance_id FROM attendance 
                    WHERE reason IS NOT NULL 
                    AND reason != '' 
                    AND msg_time IS NOT NULL
                    AND msg_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Debug: Log the messages found
            error_log("Found " . count($messages) . " messages to mark as read for admin: " . $admin_emp_id);
            // If no messages found, return success anyway
            if (count($messages) == 0) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'No messages to mark as read',
                    'marked_count' => 0,
                    'total_messages' => 0,
                    'admin_emp_id' => $admin_emp_id
                ]);
                break;
            }
            // Mark each message as read
            $inserted_count = 0;
            foreach ($messages as $message) {
                // First check if record already exists
                $check_sql = "SELECT id FROM admin_read_messages WHERE attendance_id = ? AND admin_emp_id = ?";
                $check_stmt = $pdo->prepare($check_sql);
                $check_stmt->execute([$message['attendance_id'], $admin_emp_id]);
                if ($check_stmt->rowCount() == 0) {
                    // Record doesn't exist, insert it
                    $sql = "INSERT INTO admin_read_messages (attendance_id, admin_emp_id, is_read) VALUES (?, ?, 1)";
                    $stmt = $pdo->prepare($sql);
                    if ($stmt->execute([$message['attendance_id'], $admin_emp_id])) {
                        $inserted_count++;
                        error_log("Inserted record for attendance_id: " . $message['attendance_id'] . ", admin_emp_id: " . $admin_emp_id);
                    } else {
                        error_log("Failed to insert record for attendance_id: " . $message['attendance_id'] . ", admin_emp_id: " . $admin_emp_id);
                    }
                } else {
                    // Record already exists, update it
                    $update_sql = "UPDATE admin_read_messages SET is_read = 1 WHERE attendance_id = ? AND admin_emp_id = ?";
                    $update_stmt = $pdo->prepare($update_sql);
                    if ($update_stmt->execute([$message['attendance_id'], $admin_emp_id])) {
                        $inserted_count++;
                        error_log("Updated record for attendance_id: " . $message['attendance_id'] . ", admin_emp_id: " . $admin_emp_id);
                    }
                }
            }
            // Debug: Log the insertion result
            error_log("Inserted " . $inserted_count . " records into admin_read_messages");
            echo json_encode([
                'success' => true, 
                'message' => 'All messages marked as read',
                'marked_count' => $inserted_count,
                'total_messages' => count($messages),
                'admin_emp_id' => $admin_emp_id
            ]);
        } catch (Exception $e) {
            error_log("Error in mark_all_read: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error marking all as read: ' . $e->getMessage()]);
        }
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>