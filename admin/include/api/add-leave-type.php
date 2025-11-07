<?php
require_once '../../../config.php';
include '../../session_check.php';

header('Content-Type: application/json');

// Handle GET request to fetch leave types
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT type_name FROM leave_types";
    $stmt = $pdo->query($query);

    if ($stmt) {
        $types = [];
        while ($row = $stmt->fetch()) {
            $types[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $types]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database query failed']);
    }
    exit;
}

// Handle POST request to add a new leave type
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $type_name = trim($input['type_name'] ?? '');

    if ($type_name !== '') {
        // Check if leave type already exists to avoid duplicates
        $check_stmt = $pdo->prepare("SELECT leave_type_id FROM leave_types WHERE type_name = ?");
        $check_stmt->execute([$type_name]);
        
        if ($check_stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'error' => 'Leave type already exists']);
            exit;
        }

        // Insert new leave type
        $stmt = $pdo->prepare('INSERT INTO leave_types (type_name) VALUES (?)');
        if ($stmt->execute([$type_name])) {
            echo json_encode(['success' => true, 'leave_type_id' => $pdo->lastInsertId()]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . implode(', ', $stmt->errorInfo())]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Type name is required']);
    }
    exit;
}

// Handle DELETE request to remove a leave type
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $type_name = trim($input['type_name'] ?? '');

    if ($type_name !== '') {
        $stmt = $pdo->prepare("DELETE FROM leave_types WHERE type_name = ?");
        
        if ($stmt->execute([$type_name])) {
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Leave type not found']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . implode(', ', $stmt->errorInfo())]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Type name is required']);
    }
    exit;
}

// Handle invalid request methods
echo json_encode(['success' => false, 'error' => 'Invalid request method']); 