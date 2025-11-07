<?php
header('Content-Type: application/json');
require_once '../../../config.php';


$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get announcements
        if (isset($_GET['announcement_id'])) {
            // Get specific announcement
            $announcement_id = $_GET['announcement_id'];
            $sql = "SELECT * FROM announcements WHERE announcement_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$announcement_id]);
        } else {
            // Get all announcements
            $sql = "SELECT * FROM announcements WHERE status != 'inactive' ORDER BY created_at DESC";
            $stmt = $pdo->query($sql);
        }
        $announcements = [];
        while ($row = $stmt->fetch()) {
            $announcements[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $announcements]);
        break;

    case 'POST':
        // Agar action delete hai toh delete karo
        if (isset($_POST['action']) && $_POST['action'] == 'delete') {
            $announcement_id = $_POST['announcement_id'];
            if ($announcement_id) {
                $sql = "UPDATE announcements SET status = 'inactive' WHERE announcement_id = ?";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$announcement_id])) {
                    echo json_encode(['success' => true, 'message' => 'Announcement deleted successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error deleting announcement']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Missing announcement_id']);
            }
            exit;
        }

        // Add new announcement
        $data = json_decode(file_get_contents('php://input'), true);
        $title = $data['title'];
        $content = $data['content'];
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        $status = 'active';

        $sql = "INSERT INTO announcements (title, content, start_date, end_date, status) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$title, $content, $start_date, $end_date, $status])) {
            echo json_encode(['success' => true, 'message' => 'Announcement added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding announcement']);
        }
        break;

    case 'PUT':
        // Update announcement
        $data = json_decode(file_get_contents('php://input'), true);
        $announcement_id = $data['announcement_id'];
        $title = $data['title'];
        $content = $data['content'];
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];

        $sql = "UPDATE announcements 
                SET title = ?, content = ?, start_date = ?, end_date = ? 
                WHERE announcement_id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$title, $content, $start_date, $end_date, $announcement_id])) {
            echo json_encode(['success' => true, 'message' => 'Announcement updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating announcement']);
        }
        break;

    case 'DELETE':
        // announcement_id ko reliable tarike se lo
        parse_str(file_get_contents("php://input"), $delete_vars);
        $announcement_id = $_GET['announcement_id'] ?? $delete_vars['announcement_id'] ?? null;

        if ($announcement_id) {
            $sql = "UPDATE announcements SET status = 'inactive' WHERE announcement_id = ?";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$announcement_id])) {
                echo json_encode(['success' => true, 'message' => 'Announcement deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting announcement']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing announcement_id']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        break;
}