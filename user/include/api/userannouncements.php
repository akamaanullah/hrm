<?php
header('Content-Type: application/json');
require_once '../../../config.php';
session_start();
$emp_id = $_SESSION['emp_id'] ?? null;
if (!$emp_id) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}
$today = date('Y-m-d');
// Mark as read for specific user
if (isset($_POST['action']) && $_POST['action'] === 'mark_read') {
    $announcement_id = $_POST['announcement_id'];
    // Insert into announcement_reads table
    $sql = "INSERT IGNORE INTO announcement_reads (announcement_id, emp_id) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$announcement_id, $emp_id]);    
    echo json_encode(['success' => true]);
    exit;
}
// Count unread for specific user
if (isset($_GET['action']) && $_GET['action'] === 'count_unread') {
    $sql = "SELECT COUNT(*) as count FROM announcements a 
            WHERE a.start_date <= ? AND a.end_date >= ? AND a.status = 'active'
            AND a.announcement_id NOT IN (
                SELECT ar.announcement_id FROM announcement_reads ar WHERE ar.emp_id = ?
            )";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$today, $today, $emp_id]);
    $count = $stmt->fetch()['count'];
    echo json_encode(['success' => true, 'count' => intval($count)]);
    exit;
}
// Mark all announcements as read for specific user
if (isset($_POST['action']) && $_POST['action'] === 'mark_all_read') {
    // Get all active announcements
    $sql = "SELECT announcement_id FROM announcements 
            WHERE start_date <= ? AND end_date >= ? AND status = 'active'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$today, $today]);
    $announcements = $stmt->fetchAll();
    // Mark each as read for this user
    foreach ($announcements as $announcement) {
        $sql = "INSERT IGNORE INTO announcement_reads (announcement_id, emp_id) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$announcement['announcement_id'], $emp_id]);
    }   
    echo json_encode(['success' => true]);
    exit;
}
// Default: List all active announcements
$sql = "SELECT a.*, 
        CASE WHEN ar.announcement_id IS NOT NULL THEN 1 ELSE 0 END as is_read
        FROM announcements a 
        LEFT JOIN announcement_reads ar ON a.announcement_id = ar.announcement_id AND ar.emp_id = ?
        WHERE a.start_date <= ? AND a.end_date >= ? AND a.status = 'active' 
        ORDER BY a.start_date DESC, a.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$emp_id, $today, $today]);
$announcements = [];
while ($row = $stmt->fetch()) {
    $announcements[] = $row;
}
echo json_encode(['success' => true, 'data' => $announcements]);
?>