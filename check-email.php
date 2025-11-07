<?php
require_once 'config.php';
header('Content-Type: application/json');

// Check if email parameter is provided
if (!isset($_GET['email']) || empty($_GET['email'])) {
    echo json_encode(['exists' => false, 'error' => 'Email parameter required']);
    exit;
}

$email = trim($_GET['email']);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['exists' => false, 'error' => 'Invalid email format']);
    exit;
}

// Get exclude_emp_id if provided (for edit cases)
$exclude_emp_id = isset($_GET['exclude_emp_id']) ? intval($_GET['exclude_emp_id']) : null;

try {
    // Check if email exists in database (excluding current employee if provided)
    if ($exclude_emp_id) {
        $sql = "SELECT emp_id FROM employees WHERE email = ? AND emp_id != ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $exclude_emp_id]);
    } else {
        $sql = "SELECT emp_id FROM employees WHERE email = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
    }
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['exists' => true, 'message' => 'Email already registered']);
    } else {
        echo json_encode(['exists' => false, 'message' => 'Email is available']);
    }
} catch (PDOException $e) {
    echo json_encode(['exists' => false, 'error' => 'Database error']);
}
?>

