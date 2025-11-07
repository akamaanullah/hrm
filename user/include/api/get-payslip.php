<?php
header('Content-Type: application/json');
require_once '../../../config.php';
$emp_id = $_GET['emp_id'];
$payment_date = $_GET['payment_date'];
$sql = "SELECT p.*, e.first_name, e.middle_name, e.last_name, e.position, e.department, e.joining_date FROM payroll p LEFT JOIN employees e ON p.emp_id = e.emp_id WHERE p.emp_id = ? AND p.payment_date = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$emp_id, $payment_date]);
$data = $stmt->fetch();
echo json_encode(['success' => true, 'data' => $data]); 