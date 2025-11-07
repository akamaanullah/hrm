<?php
header('Content-Type: application/json');
require_once '../../../config.php';

$sql = "SELECT gender, COUNT(*) as count FROM employees WHERE status = 'active' AND (is_deleted = 0 OR is_deleted IS NULL) AND (role IS NULL OR role != 'admin') AND gender IS NOT NULL AND gender != '' GROUP BY gender";
$stmt = $pdo->query($sql);

$total = 0;
$data = [];
while ($row = $stmt->fetch()) {
    $total += $row['count'];
    $data[strtolower($row['gender'])] = (int)$row['count'];
}

$male = isset($data['male']) ? $data['male'] : 0;
$female = isset($data['female']) ? $data['female'] : 0;

// Check if there are any employees at all
if ($total == 0) {
    echo json_encode([
        'success' => false,
        'error' => 'No active employees found for gender distribution',
        'male' => 0,
        'female' => 0,
        'total' => 0
    ]);
} else {
    echo json_encode([
        'success' => true,
        'male' => $male,
        'female' => $female,
        'total' => $total
    ]);
} 