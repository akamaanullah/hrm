<?php
header('Content-Type: application/json');
require_once '../../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['emp_ids']) || !is_array($data['emp_ids']) || count($data['emp_ids']) == 0) {
        echo json_encode(['success' => false, 'message' => 'No employees selected!']);
        exit;
    }
    $emp_ids = $data['emp_ids'];
    $selected_month = isset($data['month']) ? intval($data['month']) : intval(date('m'));
    $selected_year = isset($data['year']) ? intval($data['year']) : intval(date('Y'));
    // Agar January select ho toh previous year ka December hoga start
    $start_year = $selected_month == 1 ? $selected_year - 1 : $selected_year;
    $start_month = $selected_month == 1 ? 12 : $selected_month - 1;
    $start_date = sprintf('%04d-%02d-21', $start_year, $start_month);
    $end_date = sprintf('%04d-%02d-20', $selected_year, $selected_month);
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    
    foreach ($emp_ids as $emp_id) {
        try {
                    // Employee ki salary aur info lao (bank fields bhi lao)
        $empQ = $pdo->prepare("SELECT emp_id, first_name, salary, bank_name, account_title, account_number, bank_branch FROM employees WHERE emp_id = ?");
            $empQ->execute([$emp_id]);
            if ($emp = $empQ->fetch()) {
                $salary = floatval($emp['salary']);
                // Allowance breakdown
                $basic_salary = round($salary * 0.50, 2);
                $house_rent_allowance = round($salary * 0.25, 2);
                $fuel_allowance = round($salary * 0.07, 2);
                $utility_allowance = round($salary * 0.13, 2);
                $mobile_allowance = round($salary * 0.05, 2);
            
                $provident_fund = 0;
                $loan = 0;
                
            $month = $selected_month;
            $year = $selected_year;
            
            // Fetch attendance data with proper absent handling
            $attendance_sql = "SELECT a.*, e.first_name, e.middle_name, e.last_name AS emp_name, d.dept_name AS department 
                              FROM attendance a 
                              LEFT JOIN employees e ON a.emp_id = e.emp_id 
                              LEFT JOIN departments d ON e.department_id = d.dept_id 
                              WHERE a.emp_id = ? AND DATE(a.check_in) BETWEEN ? AND ? 
                              AND e.role != 'admin' AND (e.status = 'active' OR e.status IS NULL) 
                              AND (e.is_deleted = 0 OR e.is_deleted IS NULL)";
            
            $attendance_stmt = $pdo->prepare($attendance_sql);
            $attendance_stmt->execute([$emp_id, $start_date, $end_date]);
            
            $attendance_data = [];
            $dates_with_attendance = [];
            
            while ($row = $attendance_stmt->fetch()) {
                $row['date'] = date('Y-m-d', strtotime($row['check_in']));
                $attendance_data[] = $row;
                $dates_with_attendance[$row['date']] = true;
            }
            
            // Add absent records for missing workdays
            $start = new DateTime($start_date);
            $end = new DateTime($end_date);
            $today = date('Y-m-d');
            
            for ($date = $start; $date <= $end; $date->add(new DateInterval('P1D'))) {
                $loop_date_str = $date->format('Y-m-d');
                
                // Check if it's a weekday (Monday = 1, Sunday = 0)
                $dayOfWeek = date('w', strtotime($loop_date_str));
                $isWeekday = ($dayOfWeek >= 1 && $dayOfWeek <= 5); // Monday to Friday
                
                // Only mark past workdays as absent AND only for weekdays
                if (!isset($dates_with_attendance[$loop_date_str]) && 
                    strtotime($loop_date_str) < strtotime($today) && 
                    $isWeekday) {
                    $attendance_data[] = [
                        'attendance_id' => null,
                        'emp_id' => $emp_id,
                        'check_in' => $loop_date_str . ' 00:00:00',
                        'check_out' => null,
                        'working_hrs' => '0h 0m',
                        'status' => 'absent',
                        'emp_name' => null,
                        'department' => 'Not Assigned',
                        'date' => $loop_date_str,
                    ];
                }
            }
            
            $leave_days = 0;
            $late_days = 0;
            $half_day_days = 0;
            
            foreach ($attendance_data as $attendance) {
                $status = strtolower($attendance['status'] ?? '');
                if ($status === 'absent') {
                    $leave_days++;
                } elseif ($status === 'late') {
                    $late_days++;
                } elseif ($status === 'half-day') {
                    $half_day_days++;
                }
            }
            
            // Debug log
            error_log("Employee $emp_id - Leave: $leave_days, Late: $late_days, Half-day: $half_day_days");
                $professional_tax = 0;
                $total_earnings = $basic_salary + $house_rent_allowance + $fuel_allowance + $utility_allowance + $mobile_allowance;
                // Leave/late deduction gross salary se
                $late_to_leave = floor($late_days / 3);
                $total_leave_days = $leave_days + $late_to_leave;
                $per_day_salary = $total_earnings / 30;
                $leave_deduction = $total_leave_days * $per_day_salary;
                
                // Half-day deduction calculation (half-day = 0.5 day salary cut)
                $halfday_deduction = $half_day_days * ($per_day_salary * 0.5);
                
                $total_deductions = $provident_fund + $professional_tax + $loan + $leave_deduction + $halfday_deduction;
                $net_salary = $total_earnings - $total_deductions;
                $bank = $emp['bank_name'] ?? '';
                $payment_date = $end_date;
                // Check if payroll already exists
                $checkQ = $pdo->prepare("SELECT payroll_id FROM payroll WHERE emp_id = ? AND month = ? AND year = ?");
                $checkQ->execute([$emp_id, $month, $year]);
                if ($checkQ->rowCount() > 0) {
                                    // Update - 19 parameters: 16 for SET + 3 for WHERE
                $updateQ = $pdo->prepare("UPDATE payroll SET basic_salary=?, house_rent_allowance=?, fuel_allowance=?, utility_allowance=?, mobile_allowance=?, provident_fund=?, loan=?, leave_days=?, late_days=?, half_day_days=?, professional_tax=?, total_earnings=?, total_deductions=?, net_salary=?, bank=?, payment_date=? WHERE emp_id=? AND month=? AND year=?");
                if (!$updateQ) {
                    throw new Exception("Update prepare failed: " . implode(', ', $pdo->errorInfo()));
                }
                if ($updateQ->execute([$basic_salary, $house_rent_allowance, $fuel_allowance, $utility_allowance, $mobile_allowance, $provident_fund, $loan, $leave_days, $late_days, $half_day_days, $professional_tax, $total_earnings, $total_deductions, $net_salary, $bank, $payment_date, $emp_id, $month, $year])) {
                        $successCount++;
                    } else {
                        throw new Exception("Update execute failed: " . implode(', ', $updateQ->errorInfo()));
                    }
                } else {
                                    // Insert - 19 parameters
                $insertQ = $pdo->prepare("INSERT INTO payroll (emp_id, month, year, basic_salary, house_rent_allowance, fuel_allowance, utility_allowance, mobile_allowance, provident_fund, loan, leave_days, late_days, half_day_days, professional_tax, total_earnings, total_deductions, net_salary, bank, payment_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if (!$insertQ) {
                    throw new Exception("Insert prepare failed: " . implode(', ', $pdo->errorInfo()));
                }
                if ($insertQ->execute([$emp_id, $month, $year, $basic_salary, $house_rent_allowance, $fuel_allowance, $utility_allowance, $mobile_allowance, $provident_fund, $loan, $leave_days, $late_days, $half_day_days, $professional_tax, $total_earnings, $total_deductions, $net_salary, $bank, $payment_date])) {
                        $successCount++;
                    } else {
                        throw new Exception("Insert execute failed: " . implode(', ', $insertQ->errorInfo()));
                    }
                }
            } else {
                $errorCount++;
                $errors[] = "Employee not found or inactive: $emp_id";
            }
        } catch (Exception $e) {
            $errorCount++;
            $errors[] = "Error for emp_id $emp_id: " . $e->getMessage();
        }
    }
    if ($successCount > 0) {
        echo json_encode(['success' => true, 'message' => "Payslips generated for $successCount employees.", 'errors' => $errors, 'debug' => ['total_processed' => count($emp_ids), 'success_count' => $successCount, 'error_count' => $errorCount]]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No payslips generated.', 'errors' => $errors, 'debug' => ['total_processed' => count($emp_ids), 'success_count' => $successCount, 'error_count' => $errorCount]]);
    }
    exit;
}
echo json_encode(['success' => false, 'message' => 'Invalid request!']); 