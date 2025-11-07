<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();
// Check config.php existence
if (!file_exists('../../../config.php')) {
    error_log('Config file missing');
    echo json_encode(['success' => false, 'message' => 'Server configuration error.']);
    exit;
}
require_once '../../../config.php';
$emp_id = $_SESSION['emp_id'] ?? null;
if (!$emp_id) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}
$first_name = $_POST['first_name'] ?? $_SESSION['first_name'] ?? '';
$middle_name = $_POST['middle_name'] ?? $_SESSION['middle_name'] ?? '';
$last_name = $_POST['last_name'] ?? $_SESSION['last_name'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
if (!$first_name || !$last_name || !$phone || !$address) {
    echo json_encode(['success' => false, 'message' => 'Name, phone, and address are required']);
    exit;
}
// Handle image if uploaded
if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['profile_img'];
    $targetDir = '../../../assets/images/profile/';
    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0777, true)) {
            error_log('Failed to create directory: ' . $targetDir);
            echo json_encode(['success' => false, 'message' => 'Server error: cannot create image directory.']);
            exit;
        }
    }
    if (!is_writable($targetDir)) {
        error_log('Directory not writable: ' . $targetDir);
        echo json_encode(['success' => false, 'message' => 'Server error: image directory not writable.']);
        exit;
    }
    $fileName = 'emp_' . $emp_id . '_' . time() . '_' . basename($file['name']);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png'];
    if (!in_array($imageFileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Only JPG, JPEG, PNG allowed']);
        exit;
    }
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $relativePath = 'assets/images/profile/' . $fileName;
        $sql = "UPDATE employees SET first_name=?, middle_name=?, last_name=?, phone=?, address=?, profile_img=? WHERE emp_id=?";
        $stmt = $pdo->prepare($sql);
        if (!$stmt) {
            error_log('DB prepare error: ' . implode(', ', $pdo->errorInfo()));
            echo json_encode(['success' => false, 'message' => 'Database error.']);
            exit;
        }
        $params = [$first_name, $middle_name, $last_name, $phone, $address, $relativePath, $emp_id];
    } else {
        error_log('File upload failed: ' . $file['name']);
        echo json_encode(['success' => false, 'message' => 'File upload failed']);
        exit;
    }
} else {
    $sql = "UPDATE employees SET first_name=?, middle_name=?, last_name=?, phone=?, address=? WHERE emp_id=?";
    $stmt = $pdo->prepare($sql);
    if (!$stmt) {
        error_log('DB prepare error: ' . implode(', ', $pdo->errorInfo()));
        echo json_encode(['success' => false, 'message' => 'Database error.']);
        exit;
    }
    $params = [$first_name, $middle_name, $last_name, $phone, $address, $emp_id];
}
if ($stmt->execute($params)) {
    $_SESSION['first_name'] = $first_name;
    $_SESSION['middle_name'] = $middle_name;
    $_SESSION['last_name'] = $last_name;
    $_SESSION['phone'] = $phone;
    $_SESSION['address'] = $address;
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
} else {
    error_log('DB execute error: ' . implode(', ', $stmt->errorInfo()));
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . implode(', ', $stmt->errorInfo())]);
}