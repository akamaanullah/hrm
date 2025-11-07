<?php
header('Content-Type: application/json');
require_once '../../../config.php';

// Last 6 months
$months = [];
for ($i = 5; $i >= 0; $i--) {
    $months[] = date('Y-m', strtotime("-$i months"));
}
$monthLabels = array_map(function($m) { return date('M', strtotime($m . '-01')); }, $months);

// Get all departments
$departments = [];
$stmt = $pdo->query("SELECT dept_id, dept_name FROM departments WHERE status = 'active'");
while ($row = $stmt->fetch()) {
    $departments[$row['dept_id']] = $row['dept_name'];
}

// Prepare data structure
$series = [];
foreach ($departments as $dept_id => $dept_name) {
    $data = array_fill(0, count($months), 0);
    $sql = "SELECT DATE_FORMAT(p.updated_at, '%Y-%m') as month, COUNT(DISTINCT p.emp_id) as count
            FROM payroll p
            JOIN employees e ON p.emp_id = e.emp_id
            WHERE e.department = ?
              AND DATE_FORMAT(p.updated_at, '%Y-%m') IN ('" . implode("','", $months) . "')
            GROUP BY month";
    $stmt2 = $pdo->prepare($sql);
    $stmt2->execute([$dept_id]);
    while ($row2 = $stmt2->fetch()) {
        $idx = array_search($row2['month'], $months);
        if ($idx !== false) $data[$idx] = (int)$row2['count'];
    }
    $series[] = [
        'name' => $dept_name,
        'data' => $data
    ];
}

// Naya endpoint: department wise employees aur salary
if (isset($_GET['details']) && $_GET['details'] == '1') {
    $departments = [];
    $stmt = $pdo->query("SELECT dept_id, dept_name FROM departments WHERE status = 'active'");
    while ($row = $stmt->fetch()) {
        $departments[$row['dept_id']] = [
            'name' => $row['dept_name'],
            'employees' => []
        ];
    }
    $sql = "SELECT e.emp_id, e.first_name, e.middle_name, e.last_name, e.email, e.salary, e.department, d.dept_name 
            FROM employees e 
            LEFT JOIN departments d ON e.department_id = d.dept_id 
            WHERE e.status = 'active' 
            AND (e.is_deleted = 0 OR e.is_deleted IS NULL) 
            AND e.department IS NOT NULL 
            AND e.department != ''
            ORDER BY d.dept_name, e.first_name, e.middle_name, e.last_name";
    $stmt2 = $pdo->query($sql);
    while ($row2 = $stmt2->fetch()) {
        $dept_id = $row2['department'];
        if (isset($departments[$dept_id])) {
            $departments[$dept_id]['employees'][] = [
                'name' => trim($row2['first_name'] . ' ' . $row2['middle_name'] . ' ' . $row2['last_name']),
                'email' => $row2['email'],
                'salary' => $row2['salary']
            ];
        }
    }
    $result = [];
    foreach ($departments as $dept) {
        if (count($dept['employees']) > 0) {
            $result[] = $dept;
        }
    }
    echo json_encode(['success' => true, 'departments' => $result]);
    exit;
}

echo json_encode([
    'success' => true,
    'months' => $monthLabels,
    'series' => $series
]); 