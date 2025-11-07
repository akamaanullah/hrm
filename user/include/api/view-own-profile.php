<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json');
require_once '../../../config.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['emp_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$user_id = $_SESSION['emp_id'];

try {
    // Get user's own profile data - using PDO
    $sql = "SELECT 
                e.emp_id,
                e.first_name, e.middle_name, e.last_name,
                e.middle_name,
                e.gender,
                e.date_of_birth,
                e.phone,
                e.email,
                e.address,
                e.cnic,
                e.emergency_contact,
                e.emergency_relation,
                e.designation as position,
                e.line_manager,
                e.department as department_id,
                d.dept_name as department,
                e.sub_department,
                e.bank_name,
                e.account_title,
                e.account_number,
                e.account_type,
                e.bank_branch,
                e.salary,
                e.joining_date,
                e.qualification_institution,
                e.marital_status,
                e.education_percentage,
                e.specialization,
                e.last_organization,
                e.last_designation,
                e.experience_from_date,
                e.experience_to_date,
                e.job_type,
                e.profile_img,
                e.cv_attachment,
                e.id_card_attachment,
                e.other_documents,
                s.shift_name,
                s.start_time as shift_start_time,
                s.end_time as shift_end_time
            FROM employees e
            LEFT JOIN shifts s ON e.shift_id = s.id
            LEFT JOIN departments d ON e.department_id = d.dept_id
            WHERE e.emp_id = :user_id AND e.status = 'active'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $employee = $stmt->fetch();
    
    if ($employee) {
        // Debug: Log employee data
        error_log("Employee data: " . json_encode($employee));
        
        // Format the response data
        $profileData = [
            'first_name' => $employee['first_name'],
            'position' => $employee['position'],
            'profile_img' => $employee['profile_img'] ? $employee['profile_img'] : null,
            'middle_name' => $employee['middle_name'],
            'gender' => $employee['gender'],
            'date_of_birth' => $employee['date_of_birth'],
            'cnic' => $employee['cnic'],
            'phone' => $employee['phone'],
            'email' => $employee['email'],
            'address' => $employee['address'],
            'emergency_contact' => $employee['emergency_contact'],
            'emergency_relation' => $employee['emergency_relation'],
            'department' => $employee['department'],
            'sub_department' => $employee['sub_department'],
            'line_manager' => $employee['line_manager'],
            'joining_date' => $employee['joining_date'],
            'timing' => $employee['shift_name'] ? 
                $employee['shift_name'] . ' (' . date('g:i A', strtotime($employee['shift_start_time'])) . ' - ' . date('g:i A', strtotime($employee['shift_end_time'])) . ')' 
                : 'N/A',
            'job_type' => $employee['job_type'],
            'salary' => $employee['salary'],
            'bank_name' => $employee['bank_name'],
            'account_type' => $employee['account_type'],
            'account_title' => $employee['account_title'],
            'bank_branch' => $employee['bank_branch'],
            'account_number' => $employee['account_number'],
            'qualification_institution' => $employee['qualification_institution'],
            'marital_status' => $employee['marital_status'],
            'specialization' => $employee['specialization'],
            'education_percentage' => $employee['education_percentage'],
            'last_organization' => $employee['last_organization'],
            'last_designation' => $employee['last_designation'],
            'experience_from_date' => $employee['experience_from_date'],
            'experience_to_date' => $employee['experience_to_date'],
            'cv_attachment' => $employee['cv_attachment'],
            'id_card_attachment' => $employee['id_card_attachment'],
            'other_documents' => $employee['other_documents']
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $profileData,
            'debug' => [
                'cv_attachment' => $employee['cv_attachment'],
                'id_card_attachment' => $employee['id_card_attachment'],
                'other_documents' => $employee['other_documents']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Employee profile not found'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading profile: ' . $e->getMessage()
    ]);
}
?>
