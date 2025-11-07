<?php
session_start();
require_once '../../../config.php';
$emp_id = $_SESSION['emp_id'] ?? null;
if (!$emp_id) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}
$sql = "SELECT 
    p.emp_id, 
    e.first_name, e.middle_name, e.last_name, 
    COALESCE(e.designation, e.position, 'N/A') as designation, 
    COALESCE(d.dept_name, 'N/A') as department, 
    e.joining_date,
    p.basic_salary, 
    p.fuel_allowance,
    p.house_rent_allowance,
    p.utility_allowance,
    p.mobile_allowance,
    p.provident_fund,
    p.professional_tax,
    p.loan,
    p.leave_days,
    p.late_days,
    p.half_day_days,
    p.total_earnings,
    p.total_deductions, 
    p.net_salary, 
    p.payment_date, 
    p.bank
FROM payroll p 
LEFT JOIN employees e ON p.emp_id = e.emp_id 
LEFT JOIN departments d ON e.department_id = d.dept_id 
WHERE p.emp_id = ? ORDER BY p.payment_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$emp_id]);
$payrollData = [];
while ($row = $stmt->fetch()) {
    $payrollData[] = $row;
}
// JSON format mein data return karo
echo json_encode($payrollData);
?>