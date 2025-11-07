<?php
// Turn off error reporting to prevent HTML errors in JSON output
error_reporting(0);
ini_set('display_errors', 0);
// Start output buffering to catch any unwanted output
ob_start();
session_start();
// Set content type to JSON
header('Content-Type: application/json');
// Check if user is logged in
if (!isset($_SESSION['emp_id'])) {
    ob_clean();
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}
try {
    // Include database configuration
    require_once '../../../config.php';
    $emp_id = $_SESSION['emp_id'];
    // Get user's department and department head info
    $stmt = $pdo->prepare("
        SELECT e.department_id, d.dept_name, d.dep_head 
        FROM employees e 
        LEFT JOIN departments d ON e.department_id = d.dept_id 
        WHERE e.emp_id = ?
    ");
    $stmt->execute([$emp_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || empty($user['department_id'])) {
        ob_clean();
        echo json_encode([
            'success' => false, 
            'message' => 'User department not found',
            'emp_id' => $emp_id
        ]);
        exit;
    }
    $user_department_id = $user['department_id'];
    $user_department_name = $user['dept_name'];
    $department_head_id = $user['dep_head'];
    // Get all employees in department (including current user to show department head)
    $stmt = $pdo->prepare("
        SELECT 
            e.emp_id,
            e.first_name,
            e.middle_name,
            e.last_name,
            e.email,
            e.phone,
            e.designation,
            d.dept_name as department,
            e.joining_date,
            e.status
        FROM employees e
        LEFT JOIN departments d ON e.department_id = d.dept_id
        WHERE e.department_id = ?
        And(e.is_deleted = 0 OR e.is_deleted IS NULL)
        ORDER BY e.first_name ASC
    ");
    $stmt->execute([$user_department_id]);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Format the response
    $formatted_employees = [];
    foreach ($employees as $employee) {
        // Combine first, middle, and last name for full name display
        $fullName = $employee['first_name'] ?: '';
        if ($employee['middle_name']) $fullName .= ' ' . $employee['middle_name'];
        if ($employee['last_name']) $fullName .= ' ' . $employee['last_name'];   
        $formatted_employees[] = [
            'id' => $employee['emp_id'],
            'name' => $fullName ?: 'Unknown',
            'email' => $employee['email'] ?: 'Not provided',
            'phone' => $employee['phone'] ?: 'Not provided',
            'designation' => $employee['designation'] ?: 'Not specified',
            'department' => $employee['department'] ?: 'Unknown',
            'status' => $employee['status'] ?: 'inactive',
            'joined_date' => $employee['joining_date'] ? date('M d, Y', strtotime($employee['joining_date'])) : 'Not specified',
            'is_department_head' => ($employee['emp_id'] == $department_head_id)
        ];
    }
    // Clean output buffer
    ob_clean();
    echo json_encode([
        'success' => true,
        'data' => $formatted_employees,
        'department' => $user_department_name,
        'total_employees' => count($formatted_employees)
    ]);
} catch (PDOException $e) {
    // Clean output buffer
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
} catch (Exception $e) {
    // Clean output buffer
    ob_clean();   
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'General error: ' . $e->getMessage()
    ]);
}
// End output buffering
ob_end_flush();
?>