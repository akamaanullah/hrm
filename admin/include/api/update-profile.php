<?php
session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

$emp_id = $_SESSION['emp_id'] ?? null;
if (!$emp_id) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Handle profile image upload FIRST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_img'])) {
    $targetDir = '../../../assets/images/profile/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    $fileName = 'emp_' . $emp_id . '_' . time() . '_' . basename($_FILES['profile_img']['name']);
    $targetFile = $targetDir . $fileName;

    // File type check
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png'];
    if (!in_array($imageFileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Only JPG, JPEG, PNG images allowed!']);
        exit;
    }

    if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $targetFile)) {
        $relativePath = 'assets/images/profile/' . $fileName;
        $sql = "UPDATE employees SET profile_img=? WHERE emp_id=?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$relativePath, $emp_id])) {
            $_SESSION['profile_img'] = $relativePath;
            echo json_encode(['success' => true, 'profile_img' => $relativePath]);
        } else {
            echo json_encode(['success' => false, 'message' => 'DB update failed']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Image upload failed']);
    }
    exit;
}

// Handle profile info update (JSON)
$data = json_decode(file_get_contents('php://input'), true);

$first_name = $data['first_name'] ?? $_SESSION['first_name'] ?? '';
$middle_name = $data['middle_name'] ?? $_SESSION['middle_name'] ?? '';
$last_name = $data['last_name'] ?? $_SESSION['last_name'] ?? '';
$phone = $data['phone'] ?? '';
$address = $data['address'] ?? '';

// Phone validation
if (!preg_match('/^0\d{10,11}$/', $phone)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid phone number']);
    exit;
}

if (!$first_name || !$last_name || !$phone || !$address) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

$sql = "UPDATE employees SET first_name=?, middle_name=?, last_name=?, phone=?, address=? WHERE emp_id=?";
$stmt = $pdo->prepare($sql);

if ($stmt->execute([$first_name, $middle_name, $last_name, $phone, $address, $emp_id])) {
    // Update session values so reload par nayi info turant show ho
    $_SESSION['first_name'] = $first_name;
    $_SESSION['middle_name'] = $middle_name;
    $_SESSION['last_name'] = $last_name;
    $_SESSION['phone'] = $phone;
    $_SESSION['address'] = $address;
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . implode(', ', $stmt->errorInfo())]);
}