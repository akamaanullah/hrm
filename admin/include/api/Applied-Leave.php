<?php
require_once '../../../config.php';
header('Content-Type: application/json');

$sql = "SELECT l.*, e.first_name, e.middle_name, e.last_name, d.dept_name as department, t.type_name FROM leave_requests l LEFT JOIN employees e ON l.emp_id = e.emp_id LEFT JOIN departments d ON e.department_id = d.dept_id LEFT JOIN leave_types t ON l.leave_type_id = t.leave_type_id ORDER BY l.created_at DESC";
$stmt = $pdo->query($sql);

$data = [];
while ($row = $stmt->fetch()) {
    $data[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $data
]);