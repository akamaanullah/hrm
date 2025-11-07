<?php
header('Content-Type: application/json');
require_once '../../../config.php';
session_start();
$emp_id = $_SESSION['emp_id'] ?? null;
if (!$emp_id) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}
// Handle POST request for updating leave (move this block first)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $leave_id = $_POST['leave_id'] ?? null;
    if (!$leave_id) {
        echo json_encode(['success' => false, 'message' => 'Leave ID is required']);
        exit;
    }
    // Check if leave belongs to current user and is still pending
    $checkSql = "SELECT status FROM leave_requests WHERE leave_id = ? AND emp_id = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$leave_id, $emp_id]);
    if ($checkStmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Leave not found']);
        exit;
    }
    $leaveData = $checkStmt->fetch();
    if ($leaveData['status'] !== 'pending') {
        echo json_encode(['success' => false, 'message' => 'Only pending leaves can be edited']);
        exit;
    }
    // Get current leave data
    $currentSql = "SELECT * FROM leave_requests WHERE leave_id = ? AND emp_id = ?";
    $currentStmt = $pdo->prepare($currentSql);
    $currentStmt->execute([$leave_id, $emp_id]);
    if ($currentStmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Leave not found']);
        exit;
    }
    $currentData = $currentStmt->fetch();   
    // Use provided values or keep current values
    $leave_type_id = $_POST['leave_type_id'] ?? $currentData['leave_type_id'];
    $start_date = $_POST['start_date'] ?? $currentData['start_date'];
    $end_date = $_POST['end_date'] ?? $currentData['end_date'];
    $reason = $_POST['reason'] ?? $currentData['reason'];
    $document_path = null;
    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $targetDir = '../../../uploads/leave_documents/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES['document']['name']);
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES['document']['tmp_name'], $targetFile)) {
            $document_path = 'uploads/leave_documents/' . $fileName;
        }
    }
    // Update leave request
    if ($document_path) {
        $sql = "UPDATE leave_requests SET leave_type_id = ?, start_date = ?, end_date = ?, reason = ?, document_path = ? WHERE leave_id = ? AND emp_id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$leave_type_id, $start_date, $end_date, $reason, $document_path, $leave_id, $emp_id])) {
            echo json_encode(['success' => true, 'message' => 'Leave updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update leave']);
        }
    } else {
        $sql = "UPDATE leave_requests SET leave_type_id = ?, start_date = ?, end_date = ?, reason = ? WHERE leave_id = ? AND emp_id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$leave_type_id, $start_date, $end_date, $reason, $leave_id, $emp_id])) {
            echo json_encode(['success' => true, 'message' => 'Leave updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update leave']);
        }
    }
    exit;
}
// Handle POST request for applying leave (insert)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_type_id = $_POST['leave_type_id'] ?? null;
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $reason = $_POST['reason'] ?? null;
    $document_path = null;
    if (!$leave_type_id || !$start_date || !$end_date || !$reason) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    // Handle document upload
    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $targetDir = '../../../uploads/leave_documents/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES['document']['name']);
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES['document']['tmp_name'], $targetFile)) {
            $document_path = 'uploads/leave_documents/' . $fileName;
        }
    }
    $sql = "INSERT INTO leave_requests (emp_id, leave_type_id, start_date, end_date, reason, document_path, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$emp_id, $leave_type_id, $start_date, $end_date, $reason, $document_path])) {
        echo json_encode(['success' => true, 'message' => 'Leave applied successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to apply leave']);
    }
    exit;
}
// Handle GET request for fetching leave history
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
if ($start_date && $end_date) {
    // Use date range filter
    $sql = "SELECT lr.*, lt.type_name, e.first_name, e.middle_name, e.last_name 
            FROM leave_requests lr 
            LEFT JOIN leave_types lt ON lr.leave_type_id = lt.leave_type_id 
            LEFT JOIN employees e ON lr.emp_id = e.emp_id
            WHERE lr.emp_id = ? AND DATE(lr.start_date) BETWEEN ? AND ? ORDER BY lr.leave_id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$emp_id, $start_date, $end_date]);
} else {
    // Use all time (default)
    $sql = "SELECT lr.*, lt.type_name, e.first_name, e.middle_name, e.last_name 
            FROM leave_requests lr 
            LEFT JOIN leave_types lt ON lr.leave_type_id = lt.leave_type_id 
            LEFT JOIN employees e ON lr.emp_id = e.emp_id
            WHERE lr.emp_id = ? ORDER BY lr.leave_id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$emp_id]);
}
$leaves = [];
while ($row = $stmt->fetch()) {
    $from = new DateTime($row['start_date']);
    $to = new DateTime($row['end_date']);
    $days = $from->diff($to)->days + 1;
    $leaves[] = [
        'leave_id' => $row['leave_id'],
        'emp_id' => $row['emp_id'],
        'first_name' => $row['first_name'],
        'middle_name' => $row['middle_name'],
        'last_name' => $row['last_name'],
        'type_name' => $row['type_name'],
        'leave_type_id' => $row['leave_type_id'],
        'start_date' => $row['start_date'],
        'end_date' => $row['end_date'],
        'days' => $days,
        'reason' => $row['reason'],
        'created_at' => $row['created_at'],
        'admin_comment' => $row['admin_comment'] ?? '',
        'status' => $row['status'],
        'document_path' => $row['document_path'] ?? ''
    ];
}
echo json_encode(['success' => true, 'data' => $leaves]);
?>