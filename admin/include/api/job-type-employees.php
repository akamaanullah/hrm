<?php
header('Content-Type: application/json');
require_once '../../../config.php';

try {
    // Get job type from request
    $job_type = isset($_GET['job_type']) ? $_GET['job_type'] : '';
    
    if (empty($job_type)) {
        echo json_encode(['success' => false, 'message' => 'Job type parameter is required']);
        exit;
    }
    
    // Validate job type
    $valid_job_types = ['Internship', 'Probation', 'Permanent'];
    if (!in_array($job_type, $valid_job_types)) {
        echo json_encode(['success' => false, 'message' => 'Invalid job type']);
        exit;
    }
    
    // Fetch employees by job type with complete data
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
        e.religion,
        e.marital_status,
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
        e.education_from_date,
        e.education_to_date,
        e.education_percentage,
        e.specialization,
        e.last_organization,
        e.last_designation,
        e.experience_from_date,
        e.experience_to_date,
        e.job_type,
        e.created_at,
        e.updated_at,
        e.profile_img,
        e.cv_attachment,
        e.id_card_attachment,
        e.other_documents,
        e.shift_id,
        s.shift_name,
        s.start_time as shift_start_time,
        s.end_time as shift_end_time
    FROM employees e
    LEFT JOIN shifts s ON e.shift_id = s.id
    LEFT JOIN departments d ON e.department_id = d.dept_id
    WHERE e.job_type = :job_type 
    AND e.status = 'active' 
    AND (e.is_deleted = 0 OR e.is_deleted IS NULL)
    AND (e.role IS NULL OR e.role != 'admin')
    ORDER BY e.first_name, e.middle_name, e.last_name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':job_type' => $job_type]);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get count for this job type
    $count_sql = "SELECT COUNT(*) as count 
                  FROM employees 
                  WHERE job_type = :job_type 
                  AND status = 'active' 
                  AND (is_deleted = 0 OR is_deleted IS NULL)
                  AND (role IS NULL OR role != 'admin')";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute([':job_type' => $job_type]);
    $count = $count_stmt->fetch()['count'];
    
    echo json_encode([
        'success' => true,
        'job_type' => $job_type,
        'count' => $count,
        'employees' => $employees
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
