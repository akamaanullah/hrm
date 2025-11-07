<?php
header('Content-Type: application/json');
require_once '../../../config.php';

// Last 6 months
$months = [];
for ($i = 5; $i >= 0; $i--) {
    $months[] = date('Y-m', strtotime("-$i months"));
}
$monthLabels = array_map(function($m) { return date('M', strtotime($m . '-01')); }, $months);

// Statuses
$statuses = ['approved', 'pending', 'rejected'];
$colors = ['approved' => '#22c55e', 'pending' => '#facc15', 'rejected' => '#ef4444'];
$series = [];

foreach ($statuses as $status) {
    $data = array_fill(0, count($months), 0);
    $sql = "SELECT DATE_FORMAT(start_date, '%Y-%m') as month, COUNT(*) as count
            FROM leave_requests
            WHERE status = ?
              AND DATE_FORMAT(start_date, '%Y-%m') IN ('" . implode("','", $months) . "')
            GROUP BY month";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$status]);
    while ($row = $stmt->fetch()) {
        $monthIdx = array_search($row['month'], $months);
        if ($monthIdx !== false) {
            $data[$monthIdx] = (int)$row['count'];
        }
    }
    $series[] = [
        'name' => ucfirst($status),
        'data' => $data,
        'color' => $colors[$status]
    ];
}

echo json_encode([
    'success' => true,
    'months' => $monthLabels,
    'series' => $series
]); 