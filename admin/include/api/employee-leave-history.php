<?php
include '../../../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $emp_id = isset($_GET['emp_id']) ? intval($_GET['emp_id']) : 0;
    
    if (!$emp_id) {
        echo json_encode(['success' => false, 'message' => 'Employee ID is required']);
        exit;
    }
    
    try {
        // Get all leave requests for the employee
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
