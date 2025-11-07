<?php
include '../../../config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $shift_name = $data['shift_name'] ?? '';
    $start_time = $data['start_time'] ?? '';
    $end_time = $data['end_time'] ?? '';
    $grace_time = $data['grace_time'] ?? 0;
    $halfday_hours = $data['halfday_hours'] ?? 0;
    $id = $data['id'] ?? null;

    try {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE shifts SET shift_name = ?, start_time = ?, end_time = ?, grace_time = ?, halfday_hours = ? WHERE id = ?");
            $stmt->execute([$shift_name, $start_time, $end_time, $grace_time, $halfday_hours, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO shifts (shift_name, start_time, end_time, grace_time, halfday_hours) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$shift_name, $start_time, $end_time, $grace_time, $halfday_hours]);
        }
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// DELETE: delete a shift
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $id = $_DELETE['id'] ?? null;

    if ($id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM shifts WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID required']);
    }
    exit;
}

// GET: fetch all shifts
try {
    $stmt = $pdo->query("SELECT id, shift_name as name, DATE_FORMAT(start_time, '%H:%i:%s') as start_time, DATE_FORMAT(end_time, '%H:%i:%s') as end_time, grace_time as grace_period, halfday_hours as half_day_hours FROM shifts ORDER BY id DESC");
    $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $shifts]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}