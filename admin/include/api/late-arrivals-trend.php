<?php
header('Content-Type: application/json');
require_once '../../../config.php';

// Last 8 months
$months = [];
$data = [];
for ($i = 7; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    $months[] = date('M Y', strtotime("-$i months"));
    $sql = "SELECT COUNT(*) as count FROM attendance WHERE status = 'late' AND DATE_FORMAT(check_in, '%Y-%m') = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$date]);
    $data[] = (int)($stmt->fetch()['count'] ?? 0);
}

echo json_encode([
    'success' => true,
    'months' => $months,
    'late_arrivals' => $data
]); 