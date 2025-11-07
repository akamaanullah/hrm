<?php
header('Content-Type: application/json');
require_once '../../../config.php';

// 1. Sab active departments lao
$departments = [];
$deptResult = $pdo->query("SELECT dept_name FROM departments WHERE status = 'active' ORDER BY dept_id");
while ($row = $deptResult->fetch()) {
    $departments[] = $row['dept_name'];
}

// 2. Salary ranges define karo
$salary_ranges = ['20-30k','30-40k','40-50k','50-60k','60-70k','70k+'];

// 3. Data array initialize karo (har dept aur range ke liye 0)
$data = [];
foreach ($departments as $dept) {
    foreach ($salary_ranges as $range) {
        $data[$dept][$range] = 0;
    }
}

// 4. Actual data fetch karo
$sql = "
    SELECT 
        d.dept_name AS department,
        CASE
            WHEN e.salary BETWEEN 20000 AND 29999 THEN '20-30k'
            WHEN e.salary BETWEEN 30000 AND 39999 THEN '30-40k'
            WHEN e.salary BETWEEN 40000 AND 49999 THEN '40-50k'
            WHEN e.salary BETWEEN 50000 AND 59999 THEN '50-60k'
            WHEN e.salary BETWEEN 60000 AND 69999 THEN '60-70k'
            ELSE '70k+'
        END AS salary_range,
        COUNT(*) AS emp_count
    FROM employees e
    JOIN departments d ON e.department_id = d.dept_id
    WHERE e.status = 'active'
    AND e.department IS NOT NULL
    AND e.department != ''
    GROUP BY d.dept_name, salary_range
    ORDER BY salary_range, d.dept_name
";

$result = $pdo->query($sql);
while ($row = $result->fetch()) {
    $data[$row['department']][$row['salary_range']] = (int)$row['emp_count'];
}

// 5. Highcharts ke liye series banao
$colors = ['#00bfa5', '#4361ee', '#2ecc71', '#f7b731', '#e74c3c', '#00b4d8'];
$series = [];
$hasData = false;

foreach ($departments as $index => $dept) {
    $dept_data = [];
    foreach ($salary_ranges as $range) {
        $dept_data[] = $data[$dept][$range];
        // Check if any employee exists
        if ($data[$dept][$range] > 0) {
            $hasData = true;
        }
    }
    $series[] = [
        'name' => $dept,
        'data' => $dept_data,
        'color' => $colors[$index % count($colors)]
    ];
}

// Check if there are any active employees at all
$empCountQuery = $pdo->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active' AND department IS NOT NULL AND department != '' AND (role IS NULL OR role != 'admin')");
$empCount = $empCountQuery->fetch()['count'];

if (!$hasData || $empCount == 0) {
    echo json_encode([
        'success' => false,
        'error' => 'No active employees with salary information found',
        'salary_ranges' => [],
        'series' => []
    ]);
} else {
    echo json_encode([
        'success' => true,
        'salary_ranges' => $salary_ranges,
        'series' => $series
    ]);
} 