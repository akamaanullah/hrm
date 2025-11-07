<?php
include '../../../config.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if user is logged in
    session_start();
    if (!isset($_SESSION['emp_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }
    $emp_id = $_SESSION['emp_id'];
    try {
        // Get all leave requests for the current user
        $sql = "SELECT 
            lr.*,
            lt.type_name as leave_type,
            lr.start_date,
            lr.end_date,
            lr.status,
            lr.reason,
            lr.created_at
        FROM leave_requests lr 
        LEFT JOIN leave_types lt ON lr.leave_type_id = lt.leave_type_id 
        WHERE lr.emp_id = ?
        ORDER BY lr.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$emp_id]);
        $leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response = [
            'success' => true,
            'leaves' => $leaves
        ];
        echo json_encode($response);      
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Only GET method allowed']);
}
?>