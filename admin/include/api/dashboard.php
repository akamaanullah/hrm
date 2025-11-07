<?php
header('Content-Type: application/json');
require_once '../../../config.php';

// Get chart type if specified
$chart = isset($_GET['chart']) ? $_GET['chart'] : null;

// Handle modal data requests
if (isset($_GET['modal'])) {
    $modal = $_GET['modal'];
    
    switch ($modal) {
        case 'total_employees':
            // Get all employees with department info
            $sql = "SELECT e.first_name, e.middle_name, e.last_name, e.email, d.dept_name as department
                    FROM employees e
                    LEFT JOIN departments d ON e.department_id = d.dept_id
                    WHERE e.status = 'active' 
                    AND (e.is_deleted = 0 OR e.is_deleted IS NULL) 
                    AND (e.role IS NULL OR e.role != 'admin')
                    ORDER BY e.first_name, e.middle_name, e.last_name ASC";
            $stmt = $pdo->query($sql);
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $employees]);
            exit;
            
        case 'present_today':
            // Get employees present today
            $today = date('Y-m-d');
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            
            $sql = "SELECT DISTINCT e.first_name, e.middle_name, e.last_name, e.email, d.dept_name as department, 
                           TIME(a.check_in) as check_in_time
                    FROM employees e
                    LEFT JOIN departments d ON e.department_id = d.dept_id
                    INNER JOIN attendance a ON e.emp_id = a.emp_id
                    WHERE e.status = 'active' 
                    AND (e.is_deleted = 0 OR e.is_deleted IS NULL) 
                    AND (e.role IS NULL OR e.role != 'admin')
                    AND (
                        (DATE(a.check_in) = ? AND a.status IN ('present', 'late', 'half-day')) 
                        OR 
                        (DATE(a.check_in) = ? AND a.check_out IS NULL AND a.status IN ('present', 'late', 'half-day') 
                         AND a.check_in >= DATE_SUB(NOW(), INTERVAL 15 HOUR)
                         AND e.emp_id NOT IN (
                             SELECT DISTINCT emp_id FROM attendance 
                             WHERE DATE(check_in) = ?
                         ))
                    )
                    ORDER BY e.first_name, e.middle_name, e.last_name ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$today, $yesterday, $today]);
            $present_employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $present_employees]);
            exit;
            
        case 'on_leave':
            // Get employees on leave today
            $today = date('Y-m-d');
            
            $sql = "SELECT e.first_name, e.middle_name, e.last_name, e.email, d.dept_name as department,
                           CONCAT(DATE_FORMAT(lr.start_date, '%d %b %Y'), ' - ', DATE_FORMAT(lr.end_date, '%d %b %Y')) as leave_period,
                           DATEDIFF(lr.end_date, lr.start_date) + 1 as days
                    FROM employees e
                    LEFT JOIN departments d ON e.department_id = d.dept_id
                    INNER JOIN leave_requests lr ON e.emp_id = lr.emp_id
                    WHERE lr.status = 'approved'
                    AND ? BETWEEN lr.start_date AND lr.end_date
                    AND e.status = 'active'
                    AND (e.is_deleted = 0 OR e.is_deleted IS NULL)
                    ORDER BY e.first_name, e.middle_name, e.last_name ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$today]);
            $on_leave_employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $on_leave_employees]);
            exit;
            
        case 'departments':
            // Get all departments with employee count, manager, and department head
            $sql = "SELECT d.dept_name, d.manager, d.dep_head, d.status,
                           COUNT(e.emp_id) as employee_count,
                           emp.first_name, emp.middle_name, emp.last_name,
                           m.first_name as manager_first_name, m.middle_name as manager_middle_name, m.last_name as manager_last_name
                    FROM departments d
                    LEFT JOIN employees e ON d.dept_id = e.department 
                        AND e.status = 'active' 
                        AND (e.is_deleted = 0 OR e.is_deleted IS NULL)
                        AND (e.role IS NULL OR e.role != 'admin')
                    LEFT JOIN employees emp ON d.dep_head = emp.emp_id AND emp.status = 'active' AND (emp.is_deleted = 0 OR emp.is_deleted IS NULL)
                    LEFT JOIN employees m ON d.manager = m.emp_id AND m.status = 'active' AND (m.is_deleted = 0 OR m.is_deleted IS NULL)
                    WHERE d.status = 'active'
                    GROUP BY d.dept_id, d.dept_name, d.manager, d.dep_head, d.status, emp.first_name, emp.middle_name, emp.last_name, m.first_name, m.middle_name, m.last_name
                    ORDER BY d.dept_name ASC";
            $stmt = $pdo->query($sql);
            $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $departments]);
            exit;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid modal type']);
            exit;
    }
}

if (!$chart) {
    // Get dashboard statistics
    $stats = [];
    
    // Total Employees
    $sql = "SELECT COUNT(*) as count FROM employees WHERE status = 'active' AND (is_deleted = 0 OR is_deleted IS NULL) AND (role IS NULL OR role != 'admin')";
    $stmt = $pdo->query($sql);
    $stats['total_employees'] = $stmt->fetch()['count'];
    
    // Present Today (Updated for overnight shifts and forgotten check-outs)
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    // Query to handle overnight shifts and forgotten check-outs
    // Count employees who either:
    // 1. Checked in today, OR
    // 2. Have an active overnight shift (checked in yesterday, no check-out, within 15 hours)
    // 3. But exclude those who have already checked in today (new shift started)
    $sql = "SELECT COUNT(DISTINCT emp_id) as count 
            FROM attendance 
            WHERE (
                (DATE(check_in) = ? AND status IN ('present', 'late', 'half-day')) 
                OR 
                (DATE(check_in) = ? AND check_out IS NULL AND status IN ('present', 'late', 'half-day') 
                 AND check_in >= DATE_SUB(NOW(), INTERVAL 15 HOUR)
                 AND emp_id NOT IN (
                     SELECT DISTINCT emp_id FROM attendance 
                     WHERE DATE(check_in) = ?
                 ))
            )";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$today, $yesterday, $today]);
    $stats['present_today'] = $stmt->fetch()['count'];
    
    // On Leave
    $sql = "SELECT COUNT(DISTINCT lr.emp_id) as count
            FROM leave_requests lr
            JOIN employees e ON lr.emp_id = e.emp_id
            WHERE lr.status = 'approved'
              AND ? BETWEEN lr.start_date AND lr.end_date
              AND e.status = 'active'
              AND (e.is_deleted = 0 OR e.is_deleted IS NULL)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$today]);
    $stats['on_leave'] = $stmt->fetch()['count'];
    
    // Total Departments
    $sql = "SELECT COUNT(*) as count FROM departments WHERE status = 'active'";
    $stmt = $pdo->query($sql);
    $stats['total_departments'] = $stmt->fetch()['count'];
    
    // Calculate real trends
    // Employee growth this month
    $currentMonth = date('Y-m');
    $lastMonth = date('Y-m', strtotime('-1 month'));
    
    $sql = "SELECT COUNT(*) as count FROM employees 
            WHERE status = 'active' 
            AND (is_deleted = 0 OR is_deleted IS NULL) 
            AND (role IS NULL OR role != 'admin')
            AND DATE_FORMAT(created_at, '%Y-%m') = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$currentMonth]);
    $currentMonthEmployees = $stmt->fetch()['count'];
    
    $stmt->execute([$lastMonth]);
    $lastMonthEmployees = $stmt->fetch()['count'];
    
    if ($lastMonthEmployees > 0) {
        $employeeChange = round((($currentMonthEmployees - $lastMonthEmployees) / $lastMonthEmployees) * 100);
        $stats['employee_change'] = $employeeChange;
    } else {
        $stats['employee_change'] = 0;
    }
    
    // Department growth this year
    $currentYear = date('Y');
    $lastYear = date('Y', strtotime('-1 year'));
    
    $sql = "SELECT COUNT(*) as count FROM departments 
            WHERE status = 'active' 
            AND YEAR(created_at) = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$currentYear]);
    $currentYearDepts = $stmt->fetch()['count'];
    
    $stmt->execute([$lastYear]);
    $lastYearDepts = $stmt->fetch()['count'];
    
    $stats['department_change'] = $currentYearDepts - $lastYearDepts;
    
    echo json_encode(array_merge(['success' => true], $stats));
    exit;
}

// Handle different chart types
switch ($chart) {
    case 'salary':
        // Salary Distribution (latest payroll per employee, only from payroll table)
        $sql = "
            SELECT
              CASE
                WHEN p.basic_salary BETWEEN 20000 AND 29999 THEN '20-30k'
                WHEN p.basic_salary BETWEEN 30000 AND 39999 THEN '30-40k'
                WHEN p.basic_salary BETWEEN 40000 AND 49999 THEN '40-50k'
                WHEN p.basic_salary BETWEEN 50000 AND 59999 THEN '50-60k'
                WHEN p.basic_salary BETWEEN 60000 AND 69999 THEN '60-70k'
                ELSE '70k+'
              END as `range`,
              COUNT(*) as count
            FROM (
              SELECT emp_id, MAX(payroll_id) as latest_payroll_id
              FROM payroll
              GROUP BY emp_id
            ) latest
            JOIN payroll p ON p.payroll_id = latest.latest_payroll_id
            GROUP BY `range`
            ORDER BY MIN(p.basic_salary)
        ";
        $stmt = $pdo->query($sql);
        $data = [];
        $categories = [];
        while ($row = $stmt->fetch()) {
            $categories[] = $row['range'];
            $data[] = (int)$row['count'];
        }
        echo json_encode(['success' => true, 'categories' => $categories, 'data' => $data]);
        break;

    case 'gender':
        // Gender Diversity
        $sql = "SELECT gender, COUNT(*) as count 
                FROM employees 
                WHERE status = 'active'
                GROUP BY gender";
        $stmt = $pdo->query($sql);
        $data = [];
        while ($row = $stmt->fetch()) {
            $data[] = [
                'name' => ucfirst($row['gender']),
                'y' => (int)$row['count']
            ];
        }
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'join_exit':
        // Monthly Joining Trend (Last 6 months, exit logic removed)
        $months = [];
        $joining = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i months"));
            $months[] = date('M Y', strtotime("-$i months"));
            
            // Joining count
            $sql = "SELECT COUNT(*) as count FROM employees 
                    WHERE DATE_FORMAT(joining_date, '%Y-%m') = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$date]);
            $joining[] = (int)$stmt->fetch()['count'];
        }
        
        echo json_encode([
            'success' => true,
            'categories' => $months,
            'joining' => $joining
        ]);
        break;

    case 'employee_count':
        // Employee Count Trend (Last 6 months)
        $months = [];
        $data = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i months"));
            $months[] = date('M Y', strtotime("-$i months"));
            
            $sql = "SELECT COUNT(*) as count FROM employees 
                    WHERE status = 'active' 
                    AND (is_deleted = 0 OR is_deleted IS NULL)
                    AND (role IS NULL OR role != 'admin')
                    AND DATE_FORMAT(joining_date, '%Y-%m') <= ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$date]);
            $data[] = (int)$stmt->fetch()['count'];
        }
        
        echo json_encode([
            'success' => true,
            'categories' => $months,
            'data' => $data
        ]);
        break;

    case 'attendance_summary':
        // Attendance Summary (Last 7 days)
        $dates = [];
        $present = [];
        $absent = [];
        $late = [];
        $half_day = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dates[] = date('d M', strtotime("-$i days"));
            
            // Present count
            $sql = "SELECT COUNT(DISTINCT emp_id) as count FROM attendance 
                    WHERE DATE(check_in) = ? AND status = 'present'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$date]);
            $present[] = (int)$stmt->fetch()['count'];
            
            // Absent count
            $sql = "SELECT COUNT(*) as count FROM employees e 
                    WHERE e.status = 'active' 
                    AND NOT EXISTS (
                        SELECT 1 FROM attendance a 
                        WHERE a.emp_id = e.emp_id 
                        AND DATE(a.check_in) = ?
                    )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$date]);
            $absent[] = (int)$stmt->fetch()['count'];
            
            // Late count
            $sql = "SELECT COUNT(DISTINCT emp_id) as count FROM attendance 
                    WHERE DATE(check_in) = ? AND status = 'late'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$date]);
            $late[] = (int)$stmt->fetch()['count'];
            
            // Half day count
            $sql = "SELECT COUNT(DISTINCT emp_id) as count FROM attendance 
                    WHERE DATE(check_in) = ? AND status = 'half-day'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$date]);
            $half_day[] = (int)$stmt->fetch()['count'];
        }
        
        echo json_encode([
            'success' => true,
            'categories' => $dates,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'half_day' => $half_day
        ]);
        break;

    case 'leave_requests_typewise':
        $leave_types = ['Casual', 'Sick', 'Annual', 'Personal', 'Maternity', 'Paternity'];
        $approved = array_fill(0, count($leave_types), 0);
        $pending = array_fill(0, count($leave_types), 0);
        $rejected = array_fill(0, count($leave_types), 0);

        $sql = "SELECT l.type_name, lr.status, COUNT(*) as count 
                FROM leave_requests lr
                JOIN leave_types l ON lr.leave_type_id = l.leave_type_id
                GROUP BY l.type_name, lr.status";
        $stmt = $pdo->query($sql);

        while ($row = $stmt->fetch()) {
            $type = strtolower(trim($row['type_name']));
            $status = strtolower(trim($row['status']));
            // Map type_name to index (case-insensitive)
            $idx = array_search(ucfirst($type), $leave_types);
            if ($idx !== false) {
                if ($status == 'approved') $approved[$idx] = (int)$row['count'];
                if ($status == 'pending') $pending[$idx] = (int)$row['count'];
                if ($status == 'rejected') $rejected[$idx] = (int)$row['count'];
            }
        }

        echo json_encode([
            'success' => true,
            'categories' => $leave_types,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid chart type']);
        break;
}
?>