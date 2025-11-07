<?php
header('Content-Type: application/json');
require_once '../../../config.php';

// Last 12 months
$months = [];
for ($i = 11; $i >= 0; $i--) {
    $months[] = date('Y-m', strtotime("-$i months"));
}
$monthLabels = array_map(function($m) { return date('M Y', strtotime($m . '-01')); }, $months);

$joined = [];
$deleted = [];
$hasData = false;

foreach ($months as $month) {
    // Joined
    $sql = "SELECT COUNT(*) as count FROM employees WHERE DATE_FORMAT(joining_date, '%Y-%m') = ? AND is_deleted = 0";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$month]);
    $joinedCount = (int)($stmt->fetch()['count'] ?? 0);
    $joined[] = $joinedCount;

    // Deleted - using updated_at to get exit date
    $sql2 = "SELECT COUNT(*) as count FROM employees WHERE DATE_FORMAT(updated_at, '%Y-%m') = ? AND is_deleted = 1";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([$month]);
    $deletedCount = (int)($stmt2->fetch()['count'] ?? 0);
    $deleted[] = $deletedCount;

    // Check if any activity exists
    if ($joinedCount > 0 || $deletedCount > 0) {
        $hasData = true;
    }
}

// Check if there are any employees at all in the system
$totalEmpQuery = $pdo->query("SELECT COUNT(*) as count FROM employees WHERE is_deleted = 0 OR is_deleted IS NULL");
$totalEmployees = $totalEmpQuery->fetch()['count'];

if (!$hasData || $totalEmployees == 0) {
    echo json_encode([
        'success' => false,
        'error' => 'No employee joining or exit activity found',
        'months' => [],
        'joined' => [],
        'deleted' => []
    ]);
} else {
    echo json_encode([
        'success' => true,
        'months' => $monthLabels,
        'joined' => $joined,
        'deleted' => $deleted
    ]);
}