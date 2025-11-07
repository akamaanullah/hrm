<?php
require_once '../../../config.php';
session_start();
$emp_id = $_SESSION['emp_id'] ?? null;
if (!$emp_id) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    if ($type === 'payslip') {
        $payment_date = $_POST['payment_date'];
        $sql = "UPDATE payroll SET isread = 1 WHERE emp_id = ? AND payment_date = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$emp_id, $payment_date]);
        echo json_encode(['success' => true, 'type' => 'payslip']);
        exit;
    } elseif ($type === 'announcement') {
        $announcement_id = $_POST['announcement_id'];
        $sql = "INSERT IGNORE INTO announcement_reads (announcement_id, emp_id) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$announcement_id, $emp_id]);
        echo json_encode(['success' => true, 'type' => 'announcement']);
        exit;
    } elseif ($type === 'payslip_all') {
        $sql = "UPDATE payroll SET isread = 1 WHERE emp_id = ? AND isread = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$emp_id]);
        echo json_encode(['success' => true, 'type' => 'payslip_all']);
        exit;
    } elseif ($type === 'announcement_all') {
        // Mark all active announcements as read for this specific user
        $today = date('Y-m-d');
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
        echo json_encode(['success' => true, 'type' => 'announcement_all']);
        exit;
    }
}
echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>