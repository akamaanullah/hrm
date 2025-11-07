<?php
require_once '../../../config.php';
header('Content-Type: application/json');
$sql = "SELECT leave_type_id, type_name FROM leave_types ORDER BY type_name ASC";
$stmt = $pdo->query($sql);
$types = [];
if ($stmt && $stmt->rowCount() > 0) {
    while ($row = $stmt->fetch()) {
        $types[] = $row;
    }
}
echo json_encode(['success' => true, 'data' => $types]); 