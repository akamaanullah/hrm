<?php
ini_set('session.cookie_path', '/');
session_start();
header('Content-Type: application/json');
require_once '../../../config.php';
// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}
// Get email and password from POST
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}
// Check credentials in employees table
$sql = "SELECT emp_id, password, role FROM employees WHERE email = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);
if ($row = $stmt->fetch()) {
    // Plain text password check (for now)
    if ($password === $row['password']) {
        $_SESSION['emp_id'] = $row['emp_id'];
        $_SESSION['user_id'] = $row['emp_id']; // For compatibility with your other APIs
        $_SESSION['role'] = $row['role'];
        echo json_encode(['success' => true, 'message' => 'Login successful']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
} 