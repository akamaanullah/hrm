<?php
header('Content-Type: application/json');
require_once '../../../config.php';

// Add database columns if they don't exist
try {
    // Add bank column if it doesn't exist
    $checkBankColumn = "SHOW COLUMNS FROM payroll LIKE 'bank'";
    $result2 = $pdo->query($checkBankColumn);
    if ($result2->rowCount() == 0) {
        $addBankColumn = "ALTER TABLE payroll ADD COLUMN bank VARCHAR(100) AFTER net_salary";
        $pdo->query($addBankColumn);
    }
    
    // Add payment_date column if it doesn't exist
    $checkPaymentDateColumn = "SHOW COLUMNS FROM payroll LIKE 'payment_date'";
    $result3 = $pdo->query($checkPaymentDateColumn);
    if ($result3->rowCount() == 0) {
        $addPaymentDateColumn = "ALTER TABLE payroll ADD COLUMN payment_date DATE AFTER bank";
        $pdo->query($addPaymentDateColumn);
    }
    
    // Add position column to employees table if it doesn't exist
    $checkPositionColumn = "SHOW COLUMNS FROM employees LIKE 'position'";
    $result4 = $pdo->query($checkPositionColumn);
    if ($result4->rowCount() == 0) {
        $addPositionColumn = "ALTER TABLE employees ADD COLUMN position VARCHAR(100) AFTER designation";
        $pdo->query($addPositionColumn);
    }
    
    // Check if designation column exists in employees table
    $checkDesignationColumn = "SHOW COLUMNS FROM employees LIKE 'designation'";
    $result5 = $pdo->query($checkDesignationColumn);
    if ($result5->rowCount() == 0) {
        $addDesignationColumn = "ALTER TABLE employees ADD COLUMN designation VARCHAR(100) AFTER first_name";
        $pdo->query($addDesignationColumn);
    }
} catch (Exception $e) {
    // Column already exists or other error, continue
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    

    
    if (isset($data['action']) && $data['action'] === 'check_halfday') {
        // Check half-day records functionality
        $response = [];
        
        // Check total half-day records
        $sql = "SELECT COUNT(*) as total FROM attendance WHERE status = 'half-day'";
        $result = $pdo->query($sql);
        $total = $result->fetch()['total'];
        $response['total_halfday_records'] = $total;
        
        // Check current month half-day records
        $sql = "SELECT emp_id, COUNT(*) as count FROM attendance 
                WHERE status = 'half-day' 
                AND MONTH(check_in) = MONTH(CURRENT_DATE()) 
                AND YEAR(check_in) = YEAR(CURRENT_DATE()) 
                GROUP BY emp_id";
        $result = $pdo->query($sql);
        
        $current_month_data = [];
        while ($row = $result->fetch()) {
            $current_month_data[] = $row;
        }
        $response['current_month_halfday'] = $current_month_data;
        
        // Check if payroll table has half_day_days column
        $sql = "SHOW COLUMNS FROM payroll LIKE 'half_day_days'";
        $result = $pdo->query($sql);
        $response['has_half_day_column'] = ($result->rowCount() > 0);
        
        // Check payroll records with half_day_days
        $sql = "SELECT emp_id, half_day_days FROM payroll WHERE half_day_days > 0";
        $result = $pdo->query($sql);
        
        $payroll_halfday_data = [];
        while ($row = $result->fetch()) {
            $payroll_halfday_data[] = $row;
        }
        $response['payroll_halfday_records'] = $payroll_halfday_data;
        
        echo json_encode(['success' => true, 'data' => $response]);
        exit;
    }
    
    if (isset($data['action']) && $data['action'] === 'update') {
        // Update payroll record
        $emp_id = $data['emp_id'];
        $month = (int)$data['month'];
        $year = isset($data['year']) ? (int)$data['year'] : date('Y');
        $basic_salary = $data['basic_salary'];
        $fuel_allowance = $data['fuel_allowance'];
        $house_rent_allowance = $data['house_rent_allowance'];
        $utility_allowance = $data['utility_allowance'];
        $mobile_allowance = $data['mobile_allowance'];
    
        $provident_fund = $data['provident_fund'];
        $loan = $data['loan'];
        $leave_days = $data['leave_days'];
        $late_days = $data['late_days'];
        $half_day_days = isset($data['half_day_days']) ? $data['half_day_days'] : 0;
        $bank = $data['bank'];
        $payment_date = $data['payment_date'];
        $professional_tax = $data['professional_tax'];
        $total_earnings = $data['total_earnings'];
        $net_salary = $data['net_salary'];

        // Total deductions calculate karo
        // 3 late = 1 leave
        $late_to_leave = floor($late_days / 3);
        $total_leave_days = $leave_days + $late_to_leave;
        $per_day_salary = $total_earnings / 30;
        $leave_deduction = $total_leave_days * $per_day_salary;
        
        // Half-day deduction calculation (half-day = 0.5 day salary cut)
        $halfday_deduction = $half_day_days * ($per_day_salary * 0.5);
        
        $total_deductions = $provident_fund + $professional_tax + $loan + $leave_deduction + $halfday_deduction;
        $net_salary = $total_earnings - $total_deductions;

        $oldEmpId = isset($data['oldEmpId']) ? $data['oldEmpId'] : $emp_id;
        $oldMonth = isset($data['oldMonth']) ? (int)$data['oldMonth'] : $month;
        $oldYear = isset($data['oldYear']) ? (int)$data['oldYear'] : $year;
        $sql = "UPDATE payroll SET 
            basic_salary=?, fuel_allowance=?, house_rent_allowance=?, utility_allowance=?, mobile_allowance=?, provident_fund=?, loan=?, leave_days=?, late_days=?, half_day_days=?, bank=?, payment_date=?, professional_tax=?, total_earnings=?, net_salary=?, total_deductions=?, emp_id=?, month=?, year=? 
            WHERE emp_id=? AND month=? AND year=?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([
            $basic_salary, $fuel_allowance, $house_rent_allowance, $utility_allowance, $mobile_allowance,
            $provident_fund, $loan, $leave_days, $late_days, $half_day_days,
            $bank, $payment_date,
            $professional_tax, $total_earnings, $net_salary, $total_deductions,
            $emp_id, $month, $year, // SET nayi values
            $oldEmpId, $oldMonth, $oldYear // WHERE purani values
        ])) {
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Payroll updated successfully', 'affected_rows' => $stmt->rowCount(), 'half_day_days' => $half_day_days]);
            } else {
                // Agar update nahi hua toh insert karo
                $insert_sql = "INSERT INTO payroll (emp_id, month, year, basic_salary, fuel_allowance, house_rent_allowance, utility_allowance, mobile_allowance, provident_fund, loan, leave_days, late_days, half_day_days, bank, payment_date, professional_tax, total_earnings, net_salary, total_deductions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = $pdo->prepare($insert_sql);
                if ($insert_stmt->execute([
                    $emp_id, $month, $year, $basic_salary, $fuel_allowance, $house_rent_allowance, $utility_allowance, $mobile_allowance,
                    $provident_fund, $loan, $leave_days, $late_days, $half_day_days, $bank, $payment_date, $professional_tax, $total_earnings, $net_salary, $total_deductions
                ])) {
                    echo json_encode(['success' => true, 'message' => 'Payroll inserted successfully', 'insert_id' => $pdo->lastInsertId(), 'half_day_days' => $half_day_days]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Payroll insert failed!', 'error' => implode(', ', $insert_stmt->errorInfo())]);
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Payroll update failed', 'error' => implode(', ', $stmt->errorInfo())]);
        }
        exit;
    }
}

// Helper function: auto leave_days calculation
function getAutoLeaveDays($pdo, $emp_id, $year, $month) {
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $weekdays = [];
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $date = sprintf('%04d-%02d-%02d', $year, $month, $d);
        $dayOfWeek = date('N', strtotime($date)); // 1=Mon, 7=Sun
        if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
            $weekdays[] = $date;
        }
    }
    $sql = "SELECT DATE(check_in) as att_date FROM attendance WHERE emp_id = ? AND YEAR(check_in) = ? AND MONTH(check_in) = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$emp_id, $year, $month]);
    $attendedDays = [];
    while ($row = $stmt->fetch()) {
        $attendedDays[] = $row['att_date'];
    }
    $leaveDays = 0;
    foreach ($weekdays as $date) {
        if (!in_array($date, $attendedDays)) {
            $leaveDays++;
        }
    }
    return $leaveDays;
}




$sql = "SELECT p.emp_id, p.month, p.year, e.first_name, e.middle_name, e.last_name, COALESCE(e.designation, e.position, 'N/A') as position, COALESCE(d.dept_name, e.department, 'Not Assigned') as department, e.joining_date, p.basic_salary, p.fuel_allowance, p.house_rent_allowance, p.utility_allowance, p.mobile_allowance, p.provident_fund, p.professional_tax, p.total_deductions, p.loan, p.leave_days, p.late_days, p.half_day_days, p.total_earnings, p.net_salary, p.bank, p.payment_date 
FROM payroll p 
JOIN employees e ON p.emp_id = e.emp_id 
LEFT JOIN departments d ON e.department_id = d.dept_id 
ORDER BY p.payroll_id DESC";
$result = $pdo->query($sql);

$data = [];
while ($row = $result->fetch()) {
    // Payroll table ki leave_days/late_days use karo
    $data[] = $row;
}

echo json_encode(['success' => true, 'data' => $data]); 