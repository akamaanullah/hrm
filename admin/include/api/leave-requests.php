<?php
require_once '../../../config.php';
header('Content-Type: application/json');

// Check if it's a count request
if (isset($_GET['action']) && $_GET['action'] === 'count') {
    $sql = "SELECT COUNT(*) as pending_count FROM leave_requests WHERE status = 'pending'";
    $stmt = $pdo->query($sql);
    $count = $stmt->fetch()['pending_count'];
    
    echo json_encode([
        'success' => true,
        'count' => intval($count)
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $leave_id = intval($input['leave_id'] ?? 0);
    $status = $input['status'] ?? '';
    $admin_comment = $input['admin_comment'] ?? ''; // Default to empty string

    if ($leave_id && in_array($status, ['approved', 'rejected'])) {
        // Always update both status and admin_comment
        $stmt = $pdo->prepare("UPDATE leave_requests SET status = ?, admin_comment = ? WHERE leave_id = ?");
        
        $success = $stmt->execute([$status, $admin_comment, $leave_id]);

        if (!$success) {
            // Log error for debugging, but send a generic message to the client
            error_log("Leave request update failed: " . implode(', ', $stmt->errorInfo()));
            echo json_encode(['success' => false, 'error' => 'Database update failed.']);
        } else {
            echo json_encode(['success' => true]);
        }
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }
}

// Fetch all leave requests with employee info and leave type name
$sql = "SELECT l.*, e.first_name, e.middle_name, e.last_name, d.dept_name as department, t.type_name 
FROM leave_requests l 
LEFT JOIN employees e ON l.emp_id = e.emp_id 
LEFT JOIN departments d ON e.department_id = d.dept_id 
LEFT JOIN leave_types t ON l.leave_type_id = t.leave_type_id 
ORDER BY l.created_at DESC";
$stmt = $pdo->query($sql);

$data = [];
while ($row = $stmt->fetch()) {
    $data[] = $row;
}

// Check if there are any leave requests at all
if (empty($data)) {
    echo json_encode([
        'success' => false,
        'error' => 'No leave requests found in the system',
        'data' => []
    ]);
} else {
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
}